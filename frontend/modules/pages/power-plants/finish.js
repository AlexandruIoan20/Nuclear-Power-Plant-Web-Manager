import { powerPlantService } from '../../services/powerPlantService.js';
import { feasibilityReportService } from '../../services/feasibilityReportService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { saveHeaderState, clearHeaderState } from '../../ui/form-header/formHeaderState.js';
import { renderHeader } from '../../ui/form-header/formHeader.js';
import { populatePlantPage } from '../../ui/power-plants/plantPageRenderer.js';
import { logger } from '../../core/logger.js';

const plantId = getQueryParam("id");


function isComplete(dto) {
    const ignored = ['id', 'basicId', 'geologicalId', 'technicalId', "safetySystems"];

    return Object.entries(dto).every(([key, value]) => {
        if (ignored.includes(key)) return true;
        if (Array.isArray(value)) return value.length > 0;
        return value !== null && value !== undefined && value !== '';
    });
}


document.addEventListener("DOMContentLoaded", async () => {
    if (!plantId) {
        alert("Centrala nu a fost găsită.");
        return;
    }

    const btnExportJson = document.getElementById('btn-export-json');
    if (btnExportJson) {
        btnExportJson.addEventListener('click', async () => {
            try {
                await powerPlantService.exportPlantJson(plantId);
            } catch (e) {
                alert('Export error: ' + (e.message || 'Unknown error'));
            }
        });
    }

    const btnExportCsv = document.getElementById('btn-export-csv');
    if (btnExportCsv) {
        btnExportCsv.addEventListener('click', async () => {
            try {
                await powerPlantService.exportPlantCsv(plantId);
            } catch (e) {
                alert('Export error: ' + (e.message || 'Unknown error'));
            }
        });
    }

    try {
        const rawData = await powerPlantService.getPlant(plantId);
        logger.info({ rawData });
        populatePlantPage(rawData);

        if (rawData.details) {
            saveHeaderState({
                basicsId: rawData.basic?.id,
                geologicalId: rawData.geological?.id,
                technicalId: rawData.technical?.id,
            });
            renderHeader();
        }

        const status = rawData.details?.status || 'DRAFT';
        const verifyButton = document.getElementById("btn-verify");
        const originalBtnText = verifyButton.textContent; 
        const container = document.querySelector('.nav-links');

        let statusMsg = document.getElementById('status-message');
        if (!statusMsg) {
            statusMsg = document.createElement('div');
            statusMsg.id = 'status-message';
            statusMsg.style.marginTop = '8px';
            statusMsg.style.fontSize = '0.9rem';
            container?.appendChild(statusMsg);
        }

        if (status === 'DRAFT') {
            if (isComplete(rawData)) {
                verifyButton.disabled = false;
                verifyButton.style.display = 'inline-block';
                statusMsg.textContent = '';
            } else {
                verifyButton.disabled = true;
                verifyButton.style.display = 'inline-block';
                statusMsg.textContent = 'Completați toate datele pentru a putea trimite spre verificare.';
                statusMsg.style.color = 'var(--yellow)';
            }

            verifyButton.addEventListener("click", async () => {
                verifyButton.disabled = true;
                verifyButton.textContent = "Se generează raportul...";
                
                try {
                    const response = await feasibilityReportService.createReport(plantId);
                    logger.info({ response });

                    if (response.status === 'success') {
                        verifyButton.textContent = "Se actualizează statusul...";
                        try {
                            await powerPlantService.submitForReview(plantId);
                        } catch (error) {
                            logger.info(error.message);
                        }
                        
                        clearHeaderState();
                        window.location.href = `/pages/feasibility/report-results.html?id=${plantId}`;
                    } else {
                        verifyButton.disabled = false;
                        verifyButton.textContent = originalBtnText;
                        alert(response.message || "Eroare la generarea raportului.");
                    }
                } catch (error) {
                    logger.error(error.message);
                    verifyButton.disabled = false;
                    verifyButton.textContent = originalBtnText;
                    alert("Eroare la generarea raportului.");
                }
            });
        } else if (status === 'REVIEW') {
            verifyButton.style.display = 'none';
            statusMsg.textContent = 'Centrala este în așteptarea validării de către un administrator.';
            statusMsg.style.color = 'var(--yellow)';
        } else if (status === 'APPROVED') {
            verifyButton.style.display = 'none';
        } else if (status === 'REJECTED') {
            verifyButton.style.display = 'none';
            statusMsg.textContent = 'Centrala a fost respinsă. Corectați datele și redeschideți proiectul.';
            statusMsg.style.color = 'var(--red)';

            let reopenBtn = document.getElementById('btn-reopen');
            if (!reopenBtn) {
                reopenBtn = document.createElement('button');
                reopenBtn.id = 'btn-reopen';
                reopenBtn.className = 'button';
                reopenBtn.textContent = 'Redeschide';
                container?.appendChild(reopenBtn);
            }
            reopenBtn.style.display = 'inline-block';
            reopenBtn.addEventListener('click', async () => {
                reopenBtn.disabled = true;
                reopenBtn.textContent = 'Se redeschide...';
                try {
                    await powerPlantService.reopenDraft(plantId);
                    window.location.reload();
                } catch (error) {
                    logger.error(error.message);
                    reopenBtn.disabled = false;
                    reopenBtn.textContent = 'Redeschide';
                    alert('Eroare la redeschiderea centralei.');
                }
            });
        }
    } catch(error) {
        logger.error(error.message);
        alert("A apărut o eroare în preluarea informațiilor despre centrală.");
    }
});