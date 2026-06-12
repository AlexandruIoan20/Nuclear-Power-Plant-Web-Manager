import { powerPlantService } from '../../services/powerPlantService.js'; 
import { BasicDataRequestDTO} from '../../dto/BasicDataRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js'; 
import { getQueryParam } from '../../utils/urlHelper.js'; 
import { saveHeaderState, getHeaderState } from '../../ui/form-header/formHeaderState.js'; 
import { logger } from '../../core/logger.js';

const plantId = getQueryParam("id");
const urlBasicsId = getQueryParam("basicsId");
const basicsId = urlBasicsId ?? getHeaderState().basicsId ?? null;

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("basics-form"); 
    const statusElement = document.getElementById("status-message"); 

    if(!plantId) { 
        showError(statusElement, "ID-ul centralei nu a fost găsit."); 
    }

    let isEdit = !!basicsId;

    if (!isEdit) {
        try {
            const checkResponse = await powerPlantService.getBasics(plantId);
            if (checkResponse.data && checkResponse.data.id) {
                saveHeaderState({ basicsId: checkResponse.data.id });
                window.history.replaceState({}, '', `?id=${plantId}&basicsId=${checkResponse.data.id}`);
                isEdit = true;
            }
        } catch {
        }
    }

    if(isEdit) { 
        try { 
            const response = await powerPlantService.getBasics(plantId);
            const d = response.data; 
            
            document.getElementById("capacity").value = d.capacity ?? "";
            document.getElementById("constructionDurationYears").value = d.constructionDurationYears ?? "";
            document.getElementById("description").value = d.description ?? "";
        } catch(error) { 
            logger.error(error.message); 
            showError(statusElement, "Eroare la găsirea datelor despre centrală."); 
            return;
        }
    }

    if(!isEdit) { 
        form.addEventListener("submit", async(e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 
    
            const dto = BasicDataRequestDTO({ 
                capacity: document.getElementById("capacity").value, 
                constructionDurationYears: document.getElementById("constructionDurationYears").value, 
                description: document.getElementById("description").value
            }); 
    
            try { 
                const response = await powerPlantService.createBasics(dto, plantId); 
                saveHeaderState({ basicsId: response.basicsId }); 
                showSuccess(statusElement, "Datele au fost salvate cu succes!"); 

                window.history.replaceState({}, '', `?id=${response.plantId}&basicsId=${response.basicsId}`)
                window.location.href = `/pages/power-plants/geological.html?id=${response.plantId}`;

                form.reset(); 
            } catch(error) { 
                logger.error(error.message); 
                showError(statusElement, "Eroare la adăugarea informațiilor despre centrală"); 
            }
        })
    } else { 
        form.addEventListener("submit", async (e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 

            const dto = BasicDataRequestDTO({ 
                capacity: document.getElementById("capacity").value, 
                constructionDurationYears: document.getElementById("constructionDurationYears").value, 
                description: document.getElementById("description").value
            }); 

            try { 
                await powerPlantService.updateBasics(dto, plantId); 
                showSuccess(statusElement, "Datele centralei au fost actualizate cu succes!"); 
            } catch(error) { 
                logger.error(error.message); 
                showError(statusElement, "Eroare la actualizarea datelor centralei."); 
            }
        })      
    }
})