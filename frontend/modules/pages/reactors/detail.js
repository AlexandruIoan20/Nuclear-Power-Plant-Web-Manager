import { getQueryParam } from '../../utils/urlHelper.js';
import { API_BASE, BACKEND_BASE } from '../../config/api.config.js';

const reactorId = getQueryParam('reactorId');
const plantId = getQueryParam('plantId') || '';

let sensorCache = new Map();
let evtSource = null;

function el(id) { return document.getElementById(id); }

function hideLoader() {
    const ld = el('shell-loader');
    if (ld) ld.style.display = 'none';
}

function showError(msg, detail) {
    hideLoader();
    for (const id of ['stats-grid', 'stats-grid-2', 'sensors-section', 'topbar']) {
        const e = el(id);
        if (e) e.style.display = 'none';
    }
    let area = el('error-area');
    if (!area) {
        area = document.createElement('div');
        area.id = 'error-area';
        area.style.cssText = 'text-align:center;padding:60px 20px;border:1px solid rgba(255,255,255,0.08);border-radius:6px;margin-top:24px;';
        document.querySelector('.page-shell').appendChild(area);
    }
    area.style.display = '';
    area.innerHTML =
        '<h2 style="margin:0 0 8px;">' + msg + '</h2>' +
        '<p style="color:var(--muted);margin:0 0 20px;max-width:480px;margin-inline:auto;">' + detail + '</p>' +
        '<button class="button" onclick="location.reload()">Încearcă din nou</button>';
}

function hideError() {
    const area = el('error-area');
    if (area) area.style.display = 'none';
}

function showContent() {
    hideLoader();
    for (const id of ['stats-grid', 'stats-grid-2', 'sensors-section', 'topbar']) {
        const e = el(id);
        if (e) e.style.display = '';
    }
}

function setOnline(online) {
    const ind = el('connection-indicator');
    ind.className = online ? 'online' : 'offline';
    el('connection-text').textContent = online ? 'Live' : 'Deconectat';
}

function setLastUpdate(ts) {
    el('last-update').textContent = ts ? 'Actualizat: ' + ts : '';
}

function formatVal(v) {
    if (v === null || v === undefined) return '<span class="value-na">—</span>';
    if (typeof v === 'number') {
        if (Number.isInteger(v)) return v.toLocaleString('ro-RO');
        return v.toLocaleString('ro-RO', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
    }
    return v;
}

function statusLabel(s) {
    const labels = { GOOD: 'Bună', SUSPECT: 'Suspectă', BAD: 'Rea', MAINTENANCE: 'Mentenanță', SIMULATED: 'Simulată' };
    return labels[s] || s;
}

function barPct(value, min, max) {
    if (value === null || value === undefined || min === null || max === null || max <= min) return null;
    return Math.min(100, Math.max(0, ((value - min) / (max - min)) * 100));
}

function createSensorCard(s) {
    const card = document.createElement('div');
    card.className = 'sensor-card border-' + s.status;
    card.dataset.sensorId = s.id;

    const pct = barPct(s.value, s.normalMin, s.normalMax);
    const barWidth = pct !== null ? pct + '%' : '0%';

    card.innerHTML =
        '<div class="sensor-header">' +
            '<div>' +
                '<div class="sensor-code">' + s.code + '</div>' +
                '<div class="sensor-type">' + s.type + (s.location ? ' · ' + s.location : '') + '</div>' +
            '</div>' +
            '<span class="sensor-status-badge ' + s.status + '">' + statusLabel(s.status) + '</span>' +
        '</div>' +
        '<div class="sensor-value" id="sv-' + s.id + '">' + formatVal(s.value) + '<span class="sensor-unit">' + (s.unit || '') + '</span></div>' +
        (s.description ? '<div class="sensor-location">' + s.description + '</div>' : '') +
        '<div class="sensor-bar-wrap"><div class="sensor-bar-fill" id="sb-' + s.id + '" style="width:' + barWidth + '"></div></div>' +
        '<div class="sensor-thresholds">' +
            (s.scramLow !== null ? '<span>Scram ' + s.scramLow + ' / ' + s.scramHigh + '</span>' : '') +
            (s.alertLow !== null ? '<span>Alert ' + s.alertLow + ' / ' + s.alertHigh + '</span>' : '') +
            (s.alarmLow !== null ? '<span>Alarm ' + s.alarmLow + ' / ' + s.alarmHigh + '</span>' : '') +
        '</div>';

    return card;
}

function updateSensorCard(s) {
    const valEl = document.getElementById('sv-' + s.id);
    if (!valEl) return;

    const newHtml = formatVal(s.value) + '<span class="sensor-unit">' + (s.unit || '') + '</span>';
    if (valEl.innerHTML !== newHtml) {
        valEl.innerHTML = newHtml;
        valEl.classList.remove('flash');
        void valEl.offsetWidth;
        valEl.classList.add('flash');
    }

    const barEl = document.getElementById('sb-' + s.id);
    if (barEl) {
        const pct = barPct(s.value, s.normalMin, s.normalMax);
        barEl.style.width = pct !== null ? pct + '%' : '0%';
    }

    const card = valEl.closest('.sensor-card');
    if (card) {
        card.className = 'sensor-card border-' + s.status;
        const badge = card.querySelector('.sensor-status-badge');
        if (badge) {
            badge.className = 'sensor-status-badge ' + s.status;
            badge.textContent = statusLabel(s.status);
        }
    }
}

function buildSensorGrid(sensors) {
    const grid = el('sensors-grid');
    grid.innerHTML = '';
    el('sensors-count').textContent = 'Total senzori: ' + sensors.length;
    for (const s of sensors) {
        grid.appendChild(createSensorCard(s));
        sensorCache.set(s.id, s);
    }
}

function handleStreamData(data) {
    setLastUpdate(data.timestamp || '');
    hideError();
    showContent();

    if (!data.sensors || data.sensors.length === 0) {
        if (sensorCache.size === 0) {
            el('sensors-grid').innerHTML =
                '<div style="grid-column:1/-1;text-align:center;padding:32px 0;color:var(--muted);">Nu există senzori pentru acest reactor.</div>';
        }
        return;
    }

    if (sensorCache.size === 0) {
        buildSensorGrid(data.sensors);
        return;
    }

    for (const s of data.sensors) {
        sensorCache.set(s.id, s);
        updateSensorCard(s);
    }
}

function connectSSE() {
    if (evtSource) evtSource.close();

    const url = BACKEND_BASE + '/api/reactors/' + reactorId + '/stream';
    evtSource = new EventSource(url, { withCredentials: true });

    evtSource.onopen = () => setOnline(true);

    evtSource.onmessage = (e) => {
        try {
            const data = JSON.parse(e.data);
            if (data.error) {
                console.error('[SSE] Eroare:', data.error);
                return;
            }
            handleStreamData(data);
        } catch (err) {
            console.error('[SSE] Parse error:', err);
        }
    };

    let sseFailed = false;
    evtSource.onerror = () => {
        setOnline(false);
        if (!sseFailed) {
            sseFailed = true;
            setTimeout(() => { sseFailed = false; }, 15000);
            const grid = el('sensors-grid');
            if (grid && sensorCache.size === 0 && !grid.querySelector('.sensor-card')) {
                grid.innerHTML =
                    '<div style="grid-column:1/-1;text-align:center;padding:32px 0;color:var(--muted);">' +
                    'Conexiunea cu serverul a fost pierdută. Se încearcă reconectarea...</div>';
            }
        }
    };
}

async function loadReactor() {
    try {
        const resp = await fetch(API_BASE + '/reactors/' + reactorId, { credentials: 'include' });
        const json = await resp.json();
        if (!resp.ok || json.status !== 'success') throw new Error(json.message || 'Eroare la încărcare');

        const r = json.data;
        el('reactor-name').textContent = r.reactorCode + ' (' + r.reactorType + ')';
        el('reactor-meta').textContent = 'Răcire: ' + r.coolingType + ' · Status: ' + r.operationalStatus;

        el('stat-thermal').textContent    = r.thermalPowerMw !== null    ? r.thermalPowerMw.toLocaleString('ro-RO') : '—';
        el('stat-electrical').textContent = r.electricalPowerMw !== null ? r.electricalPowerMw.toLocaleString('ro-RO') : '—';
        el('stat-cycle').textContent      = r.fuelCycleDays !== null     ? r.fuelCycleDays : '—';
        el('stat-wear').textContent       = r.wearIndex !== null         ? r.wearIndex.toFixed(4) : '—';
        el('stat-status').textContent     = r.operationalStatus || '—';
        el('stat-cycle-day').textContent  = r.currentCycleDay !== null   ? r.currentCycleDay : '—';
        if (r.fuelCycleDays !== null) el('stat-cycle-total').textContent = r.fuelCycleDays;

        connectSSE();
    } catch (err) {
        console.error(err);
        const isNetwork = err instanceof TypeError || err.message === 'Failed to fetch';
        showError(
            isNetwork ? 'Sistem indisponibil' : 'Eroare la încărcare',
            isNetwork
                ? 'Serverul de backend nu răspunde. Verificați dacă serviciul docker este pornit.'
                : (err.message || 'A apărut o eroare neașteptată.')
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!reactorId) {
        showError('ID reactor lipsă', 'Adăugați parametrul ?reactorId=... în URL pentru a vizualiza un reactor.');
        return;
    }

    el('btn-back').addEventListener('click', () => {
        window.location.href = plantId
            ? '/pages/reactors/list.html?plantId=' + plantId
            : '/pages/power-plants/list.html';
    });

    el('btn-edit').addEventListener('click', () => {
        window.location.href = plantId
            ? '/pages/reactors/edit.html?reactorId=' + reactorId + '&plantId=' + plantId
            : '/pages/reactors/edit.html?reactorId=' + reactorId;
    });

    loadReactor();
});

window.addEventListener('beforeunload', () => {
    if (evtSource) evtSource.close();
});