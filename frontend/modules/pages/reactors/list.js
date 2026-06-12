import { API_BASE } from '../../config/api.config.js';

import { powerPlantService } from '../../services/powerPlantService.js';
import { reactorService } from '../../services/reactorService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { renderReactorTable } from '../../ui/reactors/reactorTable.js';
import { logger } from '../../core/logger.js';

import { clearHeaderState } from '../../ui/form-header/formHeaderState.js'; 

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
            `<tr class="state-row"><td colspan="7">ID centrală lipsă din URL.</td></tr>`;
        return;
    }

    let plantStatus = null;
    try {
        const plantResp = await powerPlantService.getPlant(plantId);
        plantStatus = plantResp.data?.details?.status || null;
    } catch (err) {
        logger.error('Nu s-a putut verifica statusul centralei: ' + err.message);
    }

    let isAdmin = false;
    try {
        const res = await fetch(API_BASE + '/user/status', { credentials: 'include' });
        if (res.ok) {
            const body = await res.json();
            if (body.status === 'success' && body.data) {
                isAdmin = body.data.role?.toUpperCase() === 'ADMIN';
            }
        }
    } catch (_) {}

    const container = document.querySelector('.page-shell');
    let statusMsg = document.getElementById('plant-status-msg');
    if (!statusMsg) {
        statusMsg = document.createElement('div');
        statusMsg.id = 'plant-status-msg';
        statusMsg.style.padding = '12px 16px';
        statusMsg.style.borderRadius = '6px';
        statusMsg.style.marginBottom = '16px';
        statusMsg.style.fontSize = '0.9rem';
        container?.insertBefore(statusMsg, container.querySelector('.results-meta'));
    }

    const createBtn = document.getElementById('btn-create-reactor');

    

    document.getElementById('btn-back-plant')?.addEventListener('click', () => {
        window.location.href = `/pages/power-plants/finish.html?id=${plantId}`;
    });

    let technicalLocked = false;
    try {
        const plantResp2 = await powerPlantService.getPlant(plantId);
        technicalLocked = !!(plantResp2.data?.technical?.id);
    } catch {
    }

    

    try {
        const response = await reactorService.getReactorsByPlant(plantId);
        reactors = response.data ?? [];
        renderTable(technicalLocked || plantStatus === 'APPROVED');
    } catch (error) {
        logger.error(error.message);
        document.getElementById('reactors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="7">Eroare la încărcarea reactoarelor.</td></tr>`;
    }

    if (isAdmin) {
        document.querySelectorAll('.btn-edit-reactor').forEach(el => el.style.display = 'none');
    }

    document.getElementById('reactors-tbody').addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-id]');
        if (!row) return;
        if (!technicalLocked && plantStatus !== 'APPROVED' && e.target.closest('.btn-delete-reactor')) {
            handleDelete(row.dataset.id);
            return;
        }
        if (e.target.closest('.btn-monitor-reactor') || e.target.closest('.btn-sensors-reactor')) {
            window.location.href = `/pages/reactors/detail.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        if (e.target.closest('.btn-edit-reactor') && !isAdmin) {
            window.location.href = `/pages/reactors/edit.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        if (e.target.closest('.btn-sensors-reactor')) {
            window.location.href = `/pages/sensors/list.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        
        if (e.target.closest('a, button')) return;
        if (!isAdmin) {
            window.location.href = `/pages/reactors/edit.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
        }
    });
});
