import { powerPlantService } from '../../services/powerPlantService.js';
import { feasibilityReportService } from '../../services/feasibilityReportService.js';
import { UpdatePlantStatusRequestDTO } from '../../dto/UpdatePlanStatusRequestDTO.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { populateFeasibilityReport } from '../../ui/feasibility/feasibilityReportRenderer.js';
import { populatePlantPage } from '../../ui/power-plants/plantPageRenderer.js'; 
import { logger } from '../../core/logger.js';

const plantId = getQueryParam('id');

async function updateStatus(status) {
    const btnApprove = document.getElementById('btn-approve');
    const btnReject  = document.getElementById('btn-reject');
    if (btnApprove) btnApprove.disabled = true;
    if (btnReject) btnReject.disabled  = true;

    try {
        await powerPlantService.updateStatusAdmin(
            UpdatePlantStatusRequestDTO({ status }), plantId
        );
        window.location.href = '/pages/admin/index.html';
    } catch (e) {
        logger.error(e);
        alert(`Eroare la actualizarea statusului: ${e.message}`);
        if (btnApprove) btnApprove.disabled = false;
        if (btnReject) btnReject.disabled  = false;
    }
}


function showLoading(message) {
    const shell = document.querySelector('.page-shell');
    if (!shell) return;
    let loader = document.getElementById('loading-indicator');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'loading-indicator';
        loader.style.cssText = 'text-align:center;padding:40px;color:var(--muted);font-size:1.1rem;';
        shell.prepend(loader);
    }
    loader.textContent = message;
}

function hideLoading() {
    const loader = document.getElementById('loading-indicator');
    if (loader) loader.remove();
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!plantId) { alert('ID centrală lipsă.'); return; }

    showLoading('Se încarcă datele...');

    try {
        const plantData = await powerPlantService.getPlant(plantId);
        hideLoading();

        populatePlantPage(plantData);

        const status = plantData.details?.status || '';

        if (status === 'REVIEW' || status === 'REJECTED') {
            try {
                const reportData = await feasibilityReportService.getReport(plantId);
                if (reportData.success) {
                    populateFeasibilityReport(reportData.data);
                }
            } catch (reportErr) {
                logger.warn('Raportul nu a putut fi încărcat:', reportErr.message);
            }
        }

        const btnApprove = document.getElementById('btn-approve');
        const btnReject  = document.getElementById('btn-reject');

        if (status === 'REVIEW' && btnApprove && btnReject) {
            btnApprove.style.display = '';
            btnReject.style.display = '';
            btnApprove.disabled = false;
            btnReject.disabled  = false;

            btnApprove.addEventListener('click', () => updateStatus('APPROVED'));
            btnReject.addEventListener('click',  () => updateStatus('REJECTED'));
        }

    } catch (e) {
        hideLoading();
        logger.error(e);
        alert('Eroare la încărcarea datelor.');
    }
});