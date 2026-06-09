import { powerPlantService } from '../../services/powerPlantService.js'; 
import { feasibilityReportService } from '../../services/feasibilityReportService.js'; 
import { UpdatePlantStatusRequestDTO } from '../../dto/UpdatePlanStatusRequestDTO.js'; 
import { getQueryParam } from '../../utils/urlHelper.js';
import { clearHeaderState } from '../../ui/form-header/formHeaderState.js'; 
import { populatePlantPage } from '../../ui/power-plants/plantPageRenderer.js'; 

const plantId = getQueryParam("id"); 

/*
    Verifica daca se poate creea raportul 
    Un proiect este considerat finalizat cand exista toate datele cerute despre centrala

    safetySystems ramane deprecated si trebuie tratat special 
*/
function isComplete(dto) {
    const ignored = ['id', 'basicId', 'geologicalId', 'technicalId', "safetySystems"];
    
    return Object.entries(dto).every(([key, value]) => {
        if (ignored.includes(key)) return true;
        
        if (Array.isArray(value)) return value.length > 0;
        
        return value !== null && value !== undefined && value !== '';
    });
}

/*
    Buton specific doar pentru finish.html 

    Verifica daca se poate creea raportul si devine enabled butonul daca este indeplinita
    conditia. 

    Se face functionalitatea din backend si trecerea la REVIEW a proiectului 
*/
document.addEventListener("DOMContentLoaded", async () => { 
    if(!plantId) { 
        alert("Centrala nu a fost gasita."); 
        return; 
    }

    try { 
        const rawData = await powerPlantService.getPlant(plantId); 

        console.log({ rawData }); 
        populatePlantPage(rawData); 

        const verifyButton = document.getElementById("btn-verify"); 
        const originalBtnText = verifyButton.textContent; 

        if (isComplete(rawData)) { 
            verifyButton.disabled = false; 
            verifyButton.addEventListener("click", async () => { 
                verifyButton.disabled = true; 
                verifyButton.textContent = "Se generează raportul..."; 
                try { 
                    const response = await feasibilityReportService.createReport(plantId); 
                    console.log({ response }); 

                    if (response.success) { 
                        try { 
                            verifyButton.textContent = "Se actualizează statusul..."; 
                            const r = await powerPlantService.updateStatus(UpdatePlantStatusRequestDTO({ status: "REVIEW" }), plantId); 
                            console.log({ r }); 
                        } catch (error) { 
                            console.log(error.message); 
                        } 
                        clearHeaderState(); 
                        window.location.href = `/pages/feasibility/report-results.html?id=${plantId}`; 
                    } else { 
                        verifyButton.disabled = false; 
                        verifyButton.textContent = originalBtnText; 
                        alert(response.message || "Eroare la generarea raportului."); 
                    } 
                } catch (error) { 
                    console.error(error.message); 
                    verifyButton.disabled = false; 
                    verifyButton.textContent = originalBtnText; 
                    alert("Eroare la generarea raportului."); 
                } 
            }); 
        } else { 
            verifyButton.disabled = true; 
        } 
    } catch(error) {    
        console.error(error.message);
        alert("A aparut o eroare in preluarea informatiilor despre centrala");  
    }
})