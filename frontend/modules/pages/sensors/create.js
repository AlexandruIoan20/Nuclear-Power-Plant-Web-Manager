import { sensorService } from '../../services/sensorService.js';
import { reactorService } from '../../services/reactorService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { SensorType, SensorQuality, MeasurementField } from '../../config/sensorEnums.js';
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js';

const reactorId = getQueryParam("reactorId");
const plantId = getQueryParam("plantId");

function listUrl() {
    return plantId
        ? `/pages/sensors/list.html?reactorId=${reactorId}&plantId=${plantId}`
        : `/pages/sensors/list.html?reactorId=${reactorId}`;
}

function populateSelect(id, options, selectedValue) {
    const sel = document.getElementById(id);
    sel.innerHTML = options.map(o =>
        `<option value="${o.value}" ${o.value === selectedValue ? 'selected' : ''}>${o.label}</option>`
    ).join('');
}

document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('sensor-form');
    const statusElement = document.getElementById('status-message');

    if (!reactorId) {
        showError(statusElement, "ID reactor lipsă din URL.");
        return;
    }

    try {
        const reactorResp = await reactorService.getReactor(reactorId);
        const ctx = document.getElementById('reactor-context');
        if (ctx && reactorResp) {
            ctx.textContent = `Reactor: ${reactorResp.reactorCode} (${reactorResp.reactorType})`;
        }
    } catch {
    }

    populateSelect('sensorType', SensorType, '');
    populateSelect('status', SensorQuality, 'GOOD');
    populateSelect('measurementField', MeasurementField, '');

    document.getElementById('btn-cancel')?.addEventListener('click', () => {
        window.location.href = listUrl();
    });

    document.getElementById('btn-help')?.addEventListener('click', () => {
        document.getElementById('help-overlay').classList.add('open');
    });

    document.getElementById('help-close')?.addEventListener('click', () => {
        document.getElementById('help-overlay').classList.remove('open');
    });

    document.getElementById('help-overlay')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            e.currentTarget.classList.remove('open');
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearStatus(statusElement);

        const getVal = (id) => document.getElementById(id).value;
        const data = {
            sensorCode: getVal('sensorCode'),
            sensorType: getVal('sensorType'),
            description: getVal('description'),
            locationZone: getVal('locationZone'),
            unitOfMeasure: getVal('unitOfMeasure'),
            measurementField: getVal('measurementField'),
            normalMin: getVal('normalMin'),
            normalMax: getVal('normalMax'),
            alarmLow: getVal('alarmLow'),
            alarmHigh: getVal('alarmHigh'),
            alertLow: getVal('alertLow'),
            alertHigh: getVal('alertHigh'),
            scramLow: getVal('scramLow'),
            scramHigh: getVal('scramHigh'),
            status: getVal('status'),
            isActive: document.getElementById('isActive').checked,
            lastCalibration: getVal('lastCalibration'),
            calibrationDue: getVal('calibrationDue'),
        };

        try {
            const result = await sensorService.create(reactorId, data);
            showSuccess(statusElement, "Senzorul a fost adăugat cu succes!");
            setTimeout(() => { window.location.href = listUrl(); }, 1200);
        } catch (error) {
            showError(statusElement, "Eroare la creare: " + (error.message || ""));
        }
    });
});
