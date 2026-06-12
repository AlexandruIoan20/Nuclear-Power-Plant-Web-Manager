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
        const plantData = await powerPlantService.getPlant(plantId);
        plantStatus = plantData.details?.status || null;
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

    if (plantStatus === 'APPROVED') {
        if (isAdmin) {
            statusMsg.style.display = 'none';
            if (createBtn) {
                createBtn.style.display = 'none';
                const clone = createBtn.cloneNode(true);
                createBtn.parentNode?.replaceChild(clone, createBtn);
            }
        } else {
            statusMsg.style.display = 'none';
            if (createBtn) createBtn.style.display = 'inline-block';
        }

        document.getElementById('btn-back-plant').addEventListener('click', () => {
            window.location.href = `/pages/power-plants/finish.html?id=${plantId}`;
        });
    } else {
        const labels = { DRAFT: 'în lucru', REVIEW: 'în verificare', REJECTED: 'respinsă' };
        statusMsg.style.display = 'block';
        statusMsg.style.background = '#3d2e00';
        statusMsg.style.color = 'var(--yellow)';
        statusMsg.style.border = '1px solid var(--yellow)';
        statusMsg.innerHTML =
            '⚠️ Centrala este <strong>' + (labels[plantStatus] || plantStatus) + '</strong>. ' +
            'Reactorii pot fi gestionați doar după aprobarea centralei.';

        if (createBtn) {
            createBtn.style.display = 'none';
            const clone = createBtn.cloneNode(true);
            createBtn.parentNode?.replaceChild(clone, createBtn);
        }

        document.getElementById('btn-back-plant')?.addEventListener('click', () => {
            window.location.href = `/pages/power-plants/finish.html?id=${plantId}`;
        });
    }

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
        reactors = response ?? [];
        renderTable(technicalLocked);
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
        if (!technicalLocked && e.target.closest('.btn-delete-reactor')) {
            handleDelete(row.dataset.id);
            return;
        }
        if (e.target.closest('.btn-monitor-reactor')) {
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
