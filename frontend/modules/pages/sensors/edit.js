import { sensorService } from '../../services/sensorService.js';
import { reactorService } from '../../services/reactorService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { SensorType, SensorQuality, MeasurementField } from '../../config/sensorEnums.js';
import { SensorRequestDTO } from '../../dto/SensorRequestDTO.js';
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js';
import { logger } from '../../core/logger.js';

const sensorId = getQueryParam("sensorId");
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

function populateThresholds(d) {
    ['normalMin','normalMax','alarmLow','alarmHigh','alertLow','alertHigh','scramLow','scramHigh'].forEach(f => {
        document.getElementById(f).value = d[f] ?? '';
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!sensorId) {
        showError(document.getElementById('status-message'), "ID senzor lipsă din URL.");
        return;
    }

    const form = document.getElementById('sensor-form');
    const statusElement = document.getElementById('status-message');

    try {
        const reactorResp = await reactorService.getReactor(reactorId);
        const ctx = document.getElementById('reactor-context');
        if (ctx && reactorResp.data) {
            ctx.textContent = `Reactor: ${reactorResp.data.reactorCode} (${reactorResp.data.reactorType})`;
        }
    } catch {
    }

    populateSelect('sensorType', SensorType, '');
    populateSelect('status', SensorQuality, 'GOOD');
    populateSelect('measurementField', MeasurementField, '');

    try {
        const response = await sensorService.get(sensorId);
        const d = response.data;

        document.getElementById('sensorCode').value = d.sensorCode ?? '';
        document.getElementById('sensorType').value = d.sensorType ?? '';
        document.getElementById('description').value = d.description ?? '';
        document.getElementById('locationZone').value = d.locationZone ?? '';
        document.getElementById('unitOfMeasure').value = d.unitOfMeasure ?? '';
        document.getElementById('measurementField').value = d.measurementField ?? '';
        populateThresholds(d);
        document.getElementById('status').value = d.status ?? 'GOOD';
        document.getElementById('isActive').checked = d.isActive !== false;

        if (d.lastCalibration) {
            document.getElementById('lastCalibration').value = d.lastCalibration.slice(0, 16);
        }
        if (d.calibrationDue) {
            document.getElementById('calibrationDue').value = d.calibrationDue.slice(0, 16);
        }
    } catch (error) {
        logger.error(error.message);
        showError(statusElement, "Eroare la încărcarea datelor senzorului.");
        return;
    }

    document.getElementById('btn-cancel')?.addEventListener('click', () => {
        window.location.href = listUrl();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearStatus(statusElement);

        const getVal = (id) => document.getElementById(id).value;
        const dto = SensorRequestDTO({
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
        });

        try {
            await sensorService.update(sensorId, dto);
            showSuccess(statusElement, "Senzorul a fost actualizat cu succes!");
            setTimeout(() => { window.location.href = listUrl(); }, 1200);
        } catch (error) {
            showError(statusElement, "Eroare la actualizare: " + (error.message || ""));
        }
    });
});
