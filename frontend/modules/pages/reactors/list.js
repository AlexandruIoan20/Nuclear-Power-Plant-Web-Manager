import { powerPlantService } from '../../services/powerPlantService.js';
import { reactorService } from '../../services/reactorService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { renderReactorTable } from '../../ui/reactors/reactorTable.js';
import { logger } from '../../core/logger.js';

const plantId = getQueryParam("plantId");

let reactors = [];

function renderTable(readonly) {
    renderReactorTable(reactors, plantId, readonly);
}

function handleDelete(id) {
    if (!confirm("Sigur doriți să ștergeți acest reactor?")) return;

    reactorService.deleteReactor(id).then(() => {
        reactors = reactors.filter(r => r.id !== id);
        renderTable(true);
    }).catch(error => {
        alert("Eroare la ștergerea reactorului: " + (error.message || ""));
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!plantId) {
        document.getElementById('reactors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="8">ID centrală lipsă din URL.</td></tr>`;
        return;
    }

    if (document.getElementById('plant-id-display')) {
        document.getElementById('plant-id-display').textContent = plantId;
    }

    document.getElementById('btn-back-plant')?.addEventListener('click', () => {
        window.location.href = `/pages/power-plants/finish.html?id=${plantId}`;
    });

    let technicalLocked = false;
    try {
        const plantResponse = await powerPlantService.getPlant(plantId);
        technicalLocked = !!(plantResponse.technical?.id);
    } catch {
    }

    if (technicalLocked) {
        const createBtn = document.getElementById('btn-create-reactor');
        if (createBtn) {
            createBtn.textContent = 'Configurație automată';
            createBtn.disabled = true;
            createBtn.style.opacity = '0.5';
            createBtn.style.cursor = 'not-allowed';
        }
    } else {
        document.getElementById('btn-create-reactor')?.addEventListener('click', () => {
            window.location.href = `/pages/reactors/create.html?plantId=${plantId}`;
        });
    }

    try {
        const response = await reactorService.getReactorsByPlant(plantId);
        reactors = response.data ?? [];
        renderTable(technicalLocked);
    } catch (error) {
        logger.error(error.message);
        document.getElementById('reactors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="8">Eroare la încărcarea reactoarelor.</td></tr>`;
    }

    document.getElementById('reactors-tbody').addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-id]');
        if (!row) return;
        if (!technicalLocked && e.target.closest('.btn-delete-reactor')) {
            handleDelete(row.dataset.id);
            return;
        }
        if (e.target.closest('.btn-monitor-reactor')) {
            window.location.href = `/pages/reactors/detail.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        if (e.target.closest('.btn-sensors-reactor')) {
            window.location.href = `/pages/sensors/list.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        if (e.target.closest('.btn-edit-reactor')) {
            window.location.href = `/pages/reactors/edit.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        if (e.target.closest('a, button')) return;
        window.location.href = `/pages/reactors/edit.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
    });
});
