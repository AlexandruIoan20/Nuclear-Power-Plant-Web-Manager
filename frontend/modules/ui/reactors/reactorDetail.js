export function formatSensorVal(v) {
    if (v === null || v === undefined) return null;
    if (typeof v === 'number') {
        if (Number.isInteger(v)) return v.toLocaleString('ro-RO');
        return v.toLocaleString('ro-RO', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
    }
    return String(v);
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

    const header = document.createElement('div');
    header.className = 'sensor-header';

    const headerLeft = document.createElement('div');
    const codeEl = document.createElement('div');
    codeEl.className = 'sensor-code';
    codeEl.textContent = s.code;
    headerLeft.appendChild(codeEl);

    const typeEl = document.createElement('div');
    typeEl.className = 'sensor-type';
    typeEl.textContent = s.type + (s.location ? ' · ' + s.location : '');
    headerLeft.appendChild(typeEl);
    header.appendChild(headerLeft);

    const badge = document.createElement('span');
    badge.className = 'sensor-status-badge ' + s.status;
    badge.textContent = sensorStatusLabel(s.status);
    header.appendChild(badge);

    card.appendChild(header);

    const valueEl = document.createElement('div');
    valueEl.className = 'sensor-value';
    valueEl.id = 'sv-' + s.id;

    const formatted = formatSensorVal(s.value);
    if (formatted === null) {
        const na = document.createElement('span');
        na.className = 'value-na';
        na.textContent = '\u2014';
        valueEl.appendChild(na);
    } else {
        valueEl.appendChild(document.createTextNode(formatted));
    }

    const unitEl = document.createElement('span');
    unitEl.className = 'sensor-unit';
    unitEl.textContent = s.unit || '';
    valueEl.appendChild(unitEl);

    card.appendChild(valueEl);

    if (s.description) {
        const descEl = document.createElement('div');
        descEl.className = 'sensor-location';
        descEl.textContent = s.description;
        card.appendChild(descEl);
    }

    const barWrap = document.createElement('div');
    barWrap.className = 'sensor-bar-wrap';
    const barFill = document.createElement('div');
    barFill.className = 'sensor-bar-fill';
    barFill.id = 'sb-' + s.id;
    barFill.style.width = barWidth;
    barWrap.appendChild(barFill);
    card.appendChild(barWrap);

    const thresholds = document.createElement('div');
    thresholds.className = 'sensor-thresholds';

    if (s.scramLow !== null) {
        const span = document.createElement('span');
        span.textContent = 'Scram ' + s.scramLow + ' / ' + s.scramHigh;
        thresholds.appendChild(span);
    }
    if (s.alertLow !== null) {
        const span = document.createElement('span');
        span.textContent = 'Alert ' + s.alertLow + ' / ' + s.alertHigh;
        thresholds.appendChild(span);
    }
    if (s.alarmLow !== null) {
        const span = document.createElement('span');
        span.textContent = 'Alarm ' + s.alarmLow + ' / ' + s.alarmHigh;
        thresholds.appendChild(span);
    }
    card.appendChild(thresholds);

    return card;
}

export function updateSensorCard(s) {
    const valEl = document.getElementById('sv-' + s.id);
    if (!valEl) return;

    const formatted = formatSensorVal(s.value);

    const textContent = Array.from(valEl.childNodes)
        .filter(n => n.nodeType === Node.TEXT_NODE)
        .map(n => n.nodeValue)
        .join('');

    const expectedText = formatted === null ? '\u2014' : formatted;
    const hasChanged = textContent !== expectedText;

    if (hasChanged) {
        while (valEl.firstChild && valEl.firstChild.nodeType === Node.TEXT_NODE) {
            valEl.removeChild(valEl.firstChild);
        }
        const valueSpan = valEl.querySelector('.value-na');
        if (formatted === null) {
            if (!valueSpan) {
                const na = document.createElement('span');
                na.className = 'value-na';
                na.textContent = '\u2014';
                valEl.insertBefore(na, valEl.firstChild);
            }
        } else {
            if (valueSpan) valueSpan.remove();
            valEl.insertBefore(document.createTextNode(expectedText), valEl.firstChild);
        }
    }

    const unitEl = valEl.querySelector('.sensor-unit');
    const newUnit = s.unit || '';
    const unitChanged = unitEl && unitEl.textContent !== newUnit;
    if (unitChanged) {
        unitEl.textContent = newUnit;
    }

    if (hasChanged || unitChanged) {
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