export function formatSensorVal(v) {
    if (v === null || v === undefined) return '<span class="value-na">—</span>';
    if (typeof v === 'number') {
        if (Number.isInteger(v)) return v.toLocaleString('ro-RO');
        return v.toLocaleString('ro-RO', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
    }
    return v;
}

export function sensorStatusLabel(s) {
    const labels = { GOOD: 'Bună', SUSPECT: 'Suspectă', BAD: 'Rea', MAINTENANCE: 'Mentenanță', SIMULATED: 'Simulată' };
    return labels[s] || s;
}

function barPct(value, min, max) {
    if (value === null || value === undefined || min === null || max === null || max <= min) return null;
    return Math.min(100, Math.max(0, ((value - min) / (max - min)) * 100));
}

export function createSensorCard(s) {
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
            '<span class="sensor-status-badge ' + s.status + '">' + sensorStatusLabel(s.status) + '</span>' +
        '</div>' +
        '<div class="sensor-value" id="sv-' + s.id + '">' + formatSensorVal(s.value) + '<span class="sensor-unit">' + (s.unit || '') + '</span></div>' +
        (s.description ? '<div class="sensor-location">' + s.description + '</div>' : '') +
        '<div class="sensor-bar-wrap"><div class="sensor-bar-fill" id="sb-' + s.id + '" style="width:' + barWidth + '"></div></div>' +
        '<div class="sensor-thresholds">' +
            (s.scramLow !== null ? '<span>Scram ' + s.scramLow + ' / ' + s.scramHigh + '</span>' : '') +
            (s.alertLow !== null ? '<span>Alert ' + s.alertLow + ' / ' + s.alertHigh + '</span>' : '') +
            (s.alarmLow !== null ? '<span>Alarm ' + s.alarmLow + ' / ' + s.alarmHigh + '</span>' : '') +
        '</div>';

    return card;
}

export function updateSensorCard(s) {
    const valEl = document.getElementById('sv-' + s.id);
    if (!valEl) return;

    const newHtml = formatSensorVal(s.value) + '<span class="sensor-unit">' + (s.unit || '') + '</span>';
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
            badge.textContent = sensorStatusLabel(s.status);
        }
    }
}

export function buildSensorGrid(sensors, containerEl, countEl) {
    if (!containerEl) return;
    containerEl.innerHTML = '';
    if (countEl) countEl.textContent = 'Total senzori: ' + sensors.length;
    for (const s of sensors) {
        containerEl.appendChild(createSensorCard(s));
    }
}

export function renderReactorStats(r) {
    const g = (id) => document.getElementById(id);
    if (g('reactor-name'))    g('reactor-name').textContent    = r.reactorCode + ' (' + r.reactorType + ')';
    if (g('reactor-meta'))    g('reactor-meta').textContent    = 'Răcire: ' + r.coolingType + ' · Status: ' + r.operationalStatus;
    if (g('stat-thermal'))    g('stat-thermal').textContent    = r.thermalPowerMw !== null    ? r.thermalPowerMw.toLocaleString('ro-RO') : '—';
    if (g('stat-electrical')) g('stat-electrical').textContent = r.electricalPowerMw !== null ? r.electricalPowerMw.toLocaleString('ro-RO') : '—';
    if (g('stat-cycle'))      g('stat-cycle').textContent      = r.fuelCycleDays !== null     ? r.fuelCycleDays : '—';
    if (g('stat-wear'))       g('stat-wear').textContent       = r.wearIndex !== null         ? r.wearIndex.toFixed(4) : '—';
    if (g('stat-status'))     g('stat-status').textContent     = r.operationalStatus || '—';
    if (g('stat-cycle-day'))  g('stat-cycle-day').textContent  = r.currentCycleDay !== null   ? r.currentCycleDay : '—';
    if (g('stat-cycle-total') && r.fuelCycleDays !== null) g('stat-cycle-total').textContent = r.fuelCycleDays;
}