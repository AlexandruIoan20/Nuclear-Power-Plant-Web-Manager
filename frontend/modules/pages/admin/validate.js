import { powerPlantService } from '../../services/powerPlantService.js';
import { feasibilityReportService } from '../../services/feasibilityReportService.js';
import { UpdatePlantStatusRequestDTO } from '../../dto/UpdatePlanStatusRequestDTO.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { logger } from '../../core/logger.js';
import { populateFeasibilityReport } from '../feasibility/report-results.js'; 
import { populatePlantPage } from '../../ui/power-plants/plantPageRenderer.js'; 

const plantId = getQueryParam('id');

async function updateStatus(status) {
    const btnApprove = document.getElementById('btn-approve');
    const btnReject  = document.getElementById('btn-reject');
    btnApprove.disabled = true;
    btnReject.disabled  = true;

    try {
        await powerPlantService.updateStatus(
            UpdatePlantStatusRequestDTO({ status }), plantId
        );
        window.location.href = '/pages/admin.html';
    } catch (e) {
        logger.error(`Eroare la actualizarea statusului: ${e.message}`);
        alert(`Eroare la actualizarea statusului: ${e.message}`);
        btnApprove.disabled = false;
        btnReject.disabled  = false;
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
        const [plantData, reportData] = await Promise.all([
            powerPlantService.getPlant(plantId),
            feasibilityReportService.getReport(plantId),
        ]);

        logger.info(`Date incarcate pentru validarea centralei ${plantId}`);
        hideLoading();

        populatePlantPage(plantData);

        if (reportData.success) {
            populateFeasibilityReport(reportData.data);
        }

        document.getElementById('btn-approve').disabled = false;
        document.getElementById('btn-reject').disabled  = false;

        document.getElementById('btn-approve').addEventListener('click', () => updateStatus('APPROVED'));
        document.getElementById('btn-reject').addEventListener('click',  () => updateStatus('REJECTED'));

    } catch (e) {
        hideLoading();
        logger.error(`Eroare la incarcarea datelor pentru validare centrala ${plantId}`, { message: e.message });
        alert('Eroare la încărcarea datelor.');
    }
});