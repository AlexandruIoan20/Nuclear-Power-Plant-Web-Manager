import { getQueryParam } from '../../utils/urlHelper.js';
import { API_BASE, BACKEND_BASE } from '../../config/api.config.js';
import {
    createSensorCard,
    updateSensorCard,
    buildSensorGrid as buildSensorGridUI,
    renderReactorStats
} from '../../ui/reactors/reactorDetail.js';

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
    el('connection-text').textContent = online ? 'Activ' : 'Deconectat';
}

function setLastUpdate(ts) {
    el('last-update').textContent = ts ? 'Actualizat: ' + ts : '';
}



function buildSensorGrid(sensors) {
    buildSensorGridUI(sensors, el('sensors-grid'), el('sensors-count'));
    sensors.forEach(s => sensorCache.set(s.id, s));
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

        renderReactorStats(json.data);
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