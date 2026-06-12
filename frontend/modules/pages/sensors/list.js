import { sensorService } from '../../services/sensorService.js';
import { reactorService } from '../../services/reactorService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { SensorType, SensorQuality } from '../../config/sensorEnums.js';
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js';
import { logger } from '../../core/logger.js';

const reactorId = getQueryParam("reactorId");
const plantId = getQueryParam("plantId");

let sensors = [];
let reactorType = null;

function typeLabel(value) {
    const found = SensorType.find(t => t.value === value);
    return found ? found.label : value;
}

function qualityLabel(value) {
    const found = SensorQuality.find(q => q.value === value);
    return found ? found.label : value;
}

function renderTable() {
    const tbody = document.getElementById('sensors-tbody');
    if (sensors.length === 0) {
        tbody.innerHTML = `<tr class="state-row"><td colspan="7">Niciun senzor găsit.</td></tr>`;
        return;
    }
    tbody.innerHTML = sensors.map(s => `
        <tr data-id="${s.id}">
            <td>${escapeHtml(s.sensorCode)}</td>
            <td>${typeLabel(s.sensorType)}</td>
            <td>${escapeHtml(s.description || '')}</td>
            <td>${s.currentValue !== null && s.currentValue !== undefined ? s.currentValue + (s.unitOfMeasure ? ' ' + s.unitOfMeasure : '') : '—'}</td>
            <td><span class="sensor-status-badge ${s.status}">${qualityLabel(s.status)}</span></td>
            <td>${s.isActive ? 'Da' : 'Nu'}</td>
            <td>
                <button class="button btn-edit-sensor" type="button">Editează</button>
                <button class="button btn-danger btn-delete-sensor" type="button">Șterge</button>
            </td>
        </tr>
    `).join('');
    document.getElementById('results-count').textContent = sensors.length;
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function handleDelete(id) {
    if (!confirm("Sigur doriți să ștergeți acest senzor?")) return;

    sensorService.delete(id).then(() => {
        sensors = sensors.filter(s => s.id !== id);
        renderTable();
        showSuccess(document.getElementById('status-message'), "Senzorul a fost șters.");
    }).catch(error => {
        alert("Eroare la ștergerea senzorului: " + (error.message || ""));
    });
}

async function handlePopulate() {
    if (!reactorType) {
        alert("Nu se cunoaște tipul reactorului. Reîncărcați pagina.");
        return;
    }
    if (!confirm("Se vor genera senzorii lipsă din template pentru acest reactor. Continuați?")) return;

    try {
        const result = await sensorService.populate(reactorId, reactorType);
        showSuccess(document.getElementById('status-message'), result.message || "Senzorii au fost generați din template.");
        await loadSensors();
    } catch (error) {
        showError(document.getElementById('status-message'), "Eroare la populare: " + (error.message || ""));
    }
}

async function loadSensors() {
    try {
        const response = await sensorService.getByReactor(reactorId);
        sensors = response ?? [];
        renderTable();
    } catch (error) {
        logger.error(error.message);
        document.getElementById('sensors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="7">Eroare la încărcarea senzorilor.</td></tr>`;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!reactorId) {
        document.getElementById('sensors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="7">ID reactor lipsă din URL.</td></tr>`;
        return;
    }

    document.getElementById('btn-back-reactor')?.addEventListener('click', () => {
        const url = plantId
            ? `/pages/reactors/detail.html?reactorId=${reactorId}&plantId=${plantId}`
            : `/pages/reactors/detail.html?reactorId=${reactorId}`;
        window.location.href = url;
    });

    document.getElementById('btn-populate')?.addEventListener('click', handlePopulate);

    document.getElementById('btn-create')?.addEventListener('click', () => {
        const url = plantId
            ? `/pages/sensors/create.html?reactorId=${reactorId}&plantId=${plantId}`
            : `/pages/sensors/create.html?reactorId=${reactorId}`;
        window.location.href = url;
    });

    try {
        const reactorResp = await reactorService.getReactor(reactorId);
        reactorType = reactorResp?.reactorType || null;
        const info = document.getElementById('reactor-info');
        if (info && reactorResp) {
            info.textContent = `Reactor: ${reactorResp.reactorCode} (${reactorResp.reactorType})`;
        }
    } catch {
    }

    await loadSensors();

    document.getElementById('sensors-tbody').addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-id]');
        if (!row) return;

        if (e.target.closest('.btn-delete-sensor')) {
            handleDelete(row.dataset.id);
            return;
        }
        if (e.target.closest('.btn-edit-sensor')) {
            const url = plantId
                ? `/pages/sensors/edit.html?sensorId=${row.dataset.id}&reactorId=${reactorId}&plantId=${plantId}`
                : `/pages/sensors/edit.html?sensorId=${row.dataset.id}&reactorId=${reactorId}`;
            window.location.href = url;
            return;
        }
    });
});
