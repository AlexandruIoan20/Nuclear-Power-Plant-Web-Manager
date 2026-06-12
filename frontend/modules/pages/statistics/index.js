import { statsService } from '../../services/statsService.js';
import { d3charts } from '../../ui/d3charts.js';

const { drawBar, drawDonut, drawGroupedBar } = d3charts();

let statsData = null;
let measurementsData = [];
let renderedTabs = new Set();

const STATS_INTERVAL_MS = 30000;
const MEASUREMENTS_INTERVAL_MS = 15000;

// --- Tabs ---

function activateTab(name) {
    document.querySelectorAll('.tab-bar button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

    const btn = document.querySelector(`.tab-bar button[data-tab="${name}"]`);
    const panel = document.getElementById('tab-' + name);
    if (btn) btn.classList.add('active');
    if (panel) panel.classList.add('active');

    if (statsData && !renderedTabs.has(name)) {
        renderedTabs.add(name);
        switch (name) {
            case 'plants': renderPlants(statsData.plants); break;
            case 'reactors': renderReactors(statsData.reactors); break;
            case 'sensors': renderSensors(statsData.sensors); break;
            case 'alerts': renderAlerts(statsData.alerts); break;
        }
    }
}

function initTabs() {
    document.querySelectorAll('.tab-bar button').forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn.dataset.tab));
    });
}

// --- KPI ---

function renderKpi(containerId, cards) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = cards.map(c => `
        <div class="kpi-card">
            <div class="kpi-value">${c.value}</div>
            <div class="kpi-label">${c.label}</div>
        </div>
    `).join('');
}

// --- Plants tab ---

function renderPlants(data) {
    renderKpi('plants-kpi', [
        { value: data.total, label: 'Total centrale' },
        { value: data.avgEfficiency ?? '—', label: 'Eficiență medie %' },
        { value: data.avgRisk ?? '—', label: 'Risc oper. mediu' },
    ]);

    const statusEntries = Object.entries(data.byStatus).map(([k, v]) => ({ label: k, value: v }));
    drawDonut('#chart-plants-status', statusEntries, { labelKey: 'label', valueKey: 'value' });

    drawBar('#chart-plants-country', data.byCountry.map(d => ({ label: d.country, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });

    drawBar('#chart-plants-monthly', data.createdByMonth.map(d => ({ label: d.month, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
}

// --- Reactors tab ---

function renderReactors(data) {
    renderKpi('reactors-kpi', [
        { value: data.total, label: 'Total reactoare' },
        { value: data.avgWear ?? '—', label: 'Uzură medie' },
        { value: data.avgThermalMw ?? '—', label: 'Putere term. medie MW' },
        { value: data.avgElectricalMw ?? '—', label: 'Putere electr. medie MW' },
    ]);

    drawBar('#chart-reactors-type', data.byType.map(d => ({ label: d.reactor_type, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
    drawBar('#chart-reactors-cooling', data.byCooling.map(d => ({ label: d.cooling_type, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
    drawBar('#chart-reactors-status', data.byStatus.map(d => ({ label: d.operational_status, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });

    populateReactorSelect();
    renderReactorHourly();
}

function populateReactorSelect() {
    const sel = document.getElementById('reactor-select');
    if (!sel) return;
    while (sel.options.length > 1) sel.remove(1);

    fetch(API_BASE + '/reactors', { credentials: 'include' })
        .then(r => r.json())
        .then(resp => {
            const reactors = Array.isArray(resp) ? resp : (resp.data || []);
            if (!reactors.length) return;
            reactors.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.reactorCode + (r.reactorType ? ` (${r.reactorType})` : '');
                sel.appendChild(opt);
            });
        })
        .catch(() => {});
}

// --- Measurements / hourly chart ---

function renderReactorHourly() {
    const reactorId = document.getElementById('reactor-select')?.value || '';
    const hours = parseInt(document.getElementById('hours-select')?.value || '24');

    statsService.getMeasurements(reactorId || null, hours).then(data => {
        measurementsData = Array.isArray(data) ? data : [];
        drawHourlyChart(measurementsData);
    }).catch(() => {});
}

function drawHourlyChart(data) {
    if (!data || data.length === 0) {
        drawBar('#chart-reactors-hourly', [], { labelKey: 'label', valueKey: 'value' });
        return;
    }

    const byHour = {};
    data.forEach(d => {
        const hour = d.hour ? d.hour.substring(0, 16).replace('T', ' ') : '';
        const rid = d.reactor_id || 'all';
        if (!byHour[hour]) byHour[hour] = {};
        byHour[hour][rid] = d;
    });

    const hours = Object.keys(byHour).sort();
    const reactorIds = [...new Set(data.map(d => d.reactor_id))];
    const isAggregate = document.getElementById('reactor-select')?.value === '';

    if (isAggregate) {
        const agg = hours.map(h => {
            const entries = Object.values(byHour[h]);
            const avgPower = entries.reduce((s, e) => s + parseFloat(e.power_percent_avg || 0), 0) / (entries.length || 1);
            return { label: h, value: Math.round(avgPower * 10) / 10 };
        });
        drawBar('#chart-reactors-hourly', agg, { labelKey: 'label', valueKey: 'value' });
    } else {
        const bars = [];
        hours.forEach(h => {
            reactorIds.forEach(rid => {
                const entry = byHour[h] && byHour[h][rid];
                if (entry) {
                    bars.push({
                        group: reactorIds.length > 1 ? rid.substring(0, 8) : 'putere',
                        label: h,
                        value: Math.round(parseFloat(entry.power_percent_avg || 0) * 10) / 10,
                    });
                }
            });
        });
        if (reactorIds.length <= 1) {
            drawBar('#chart-reactors-hourly', bars.map(b => ({ label: b.label, value: b.value })), { labelKey: 'label', valueKey: 'value' });
        } else {
            drawGroupedBar('#chart-reactors-hourly', bars, { groupKey: 'group', labelKey: 'label', valueKey: 'value' });
        }
    }
}

// --- Sensors tab ---

function renderSensors(data) {
    renderKpi('sensors-kpi', [
        { value: data.total, label: 'Total senzori' },
        { value: data.activeCount, label: 'Senzori activi' },
        { value: data.total - data.activeCount, label: 'Senzori inactivi' },
    ]);

    drawBar('#chart-sensors-type', data.byType.map(d => ({ label: d.sensor_type, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
    drawDonut('#chart-sensors-status', data.byStatus.map(d => ({ label: d.status, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
}

// --- Alerts tab ---

function renderAlerts(data) {
    const emg = data.bySeverity.find(s => s.severity === 'EMERGENCY');
    const warn = data.bySeverity.find(s => s.severity === 'WARNING');

    renderKpi('alerts-kpi', [
        { value: data.total, label: 'Total alerte' },
        { value: emg ? emg.cnt : 0, label: 'Urgențe' },
        { value: warn ? warn.cnt : 0, label: 'Avertismente' },
    ]);

    drawBar('#chart-alerts-severity', data.bySeverity.map(d => ({ label: d.severity, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
    drawBar('#chart-alerts-type', data.byType.map(d => ({ label: d.type, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });
    drawBar('#chart-alerts-daily', data.last30days.map(d => ({ label: d.day, value: parseInt(d.cnt) })), { labelKey: 'label', valueKey: 'value' });

    renderRecentAlerts(data.last30days);
}

function renderRecentAlerts(daily) {
    const el = document.getElementById('alerts-recent-table');
    if (!el) return;
    if (!daily || daily.length === 0) {
        el.innerHTML = '<p style="color:#666;padding:12px;">Nicio alertă în ultimele 30 zile.</p>';
        return;
    }
    const recent = daily.slice(-15).reverse();
    el.innerHTML = `
        <table>
            <thead><tr><th>Data</th><th>Alerte</th></tr></thead>
            <tbody>
                ${recent.map(d => `
                    <tr>
                        <td>${d.day}</td>
                        <td><span class="severity-badge" style="background:#dc3545;">${d.cnt}</span></td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

// --- Error display ---

function showError(msg) {
    const el = document.getElementById('stats-error');
    if (!el) return;
    el.innerHTML = msg + ' <button onclick="location.reload()" style="background:#dc3545;color:#fff;border:none;padding:4px 12px;border-radius:4px;cursor:pointer;margin-left:8px;">Reîncarcă</button>';
    el.style.display = 'block';
    document.getElementById('stats-loading')?.remove();
}

function hideError() {
    const el = document.getElementById('stats-error');
    if (el) el.style.display = 'none';
}

// --- Main fetch & update ---

let lastUpdate = null;
let statsTimer = null;
let measurementsTimer = null;

async function fetchStats() {
    try {
        console.log('[Stats] Fetching stats data...');
        const data = await statsService.getAll();
        console.log('[Stats] Data received:', data ? Object.keys(data) : 'null', 'plants:', data?.plants?.total, 'reactors:', data?.reactors?.total);
        hideError();
        document.getElementById('stats-loading')?.remove();
        statsData = data;
        lastUpdate = new Date();
        document.getElementById('stats-last-update').textContent = 'Ultima actualizare: ' + lastUpdate.toLocaleTimeString();

        const activeTab = document.querySelector('.tab-bar button.active')?.dataset?.tab || 'plants';
        if (!renderedTabs.has(activeTab)) {
            renderedTabs.add(activeTab);
            switch (activeTab) {
                case 'plants': renderPlants(data.plants); break;
                case 'reactors': renderReactors(data.reactors); break;
                case 'sensors': renderSensors(data.sensors); break;
                case 'alerts': renderAlerts(data.alerts); break;
            }
        } else {
            if (renderedTabs.has('plants')) renderPlants(data.plants);
            if (renderedTabs.has('reactors')) renderReactors(data.reactors);
            if (renderedTabs.has('sensors')) renderSensors(data.sensors);
            if (renderedTabs.has('alerts')) renderAlerts(data.alerts);
        }
    } catch (err) {
        const msg = err?.message || err?.status ? `HTTP ${err.status}: ${err.message}` : String(err);
        showError('Eroare la încărcarea datelor: ' + msg + '. Verificați conexiunea și autentificarea.');
        console.error('Stats fetch failed:', err);
    }
}

// --- Init ---

function init() {
    console.log('[Stats] Initializing statistics page');
    if (typeof d3 === 'undefined') {
        showError('Biblioteca D3.js nu a fost încărcată. Verificați conexiunea la internet.');
        return;
    }
    console.log('[Stats] D3 loaded:', !!d3);
    initTabs();

    document.getElementById('reactor-select')?.addEventListener('change', renderReactorHourly);
    document.getElementById('hours-select')?.addEventListener('change', renderReactorHourly);

    fetchStats();

    statsTimer = setInterval(fetchStats, STATS_INTERVAL_MS);
    measurementsTimer = setInterval(() => {
        const tab = document.querySelector('.tab-bar button.active');
        if (tab && tab.dataset.tab === 'reactors') {
            renderReactorHourly();
        }
    }, MEASUREMENTS_INTERVAL_MS);
}

document.addEventListener('DOMContentLoaded', init);
