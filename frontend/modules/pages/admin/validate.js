import { powerPlantService } from '../../services/powerPlantService.js';
import { feasibilityReportService } from '../../services/feasibilityReportService.js';
import { UpdatePlantStatusRequestDTO } from '../../dto/UpdatePlanStatusRequestDTO.js';
import { getQueryParam } from '../../utils/urlHelper.js';
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
        console.error(e);
        alert(`Eroare la actualizarea statusului: ${e.message}`);
        btnApprove.disabled = false;
        btnReject.disabled  = false;
    }
}


document.addEventListener('DOMContentLoaded', async () => {
    if (!plantId) { alert('ID centrală lipsă.'); return; }

    try {
        const [plantData, reportData] = await Promise.all([
            powerPlantService.getPlant(plantId),
            feasibilityReportService.getReport(plantId),
        ]);

        console.log({ plantData, reportData }); 

        populatePlantPage(plantData);

        if (reportData.success) {
            populateFeasibilityReport(reportData.data);
        }

        document.getElementById('btn-approve').disabled = false;
        document.getElementById('btn-reject').disabled  = false;

        document.getElementById('btn-approve').addEventListener('click', () => updateStatus('APPROVED'));
        document.getElementById('btn-reject').addEventListener('click',  () => updateStatus('REJECTED'));

    } catch (e) {
        console.error(e);
        alert('Eroare la încărcarea datelor.');
    }
});