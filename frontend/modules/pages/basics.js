import { powerPlantService } from '../services/powerPlantService.js'; 
import { BasicDataRequestDTO} from '../dto/BasicDataRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js'; 
import { getQueryParam } from '../utils/urlHelper.js'; 

const plantId = getQueryParam("id");
const basicsId = getQueryParam("basicsId"); 

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("basics-form"); 
    const statusElement = document.getElementById("status-message"); 

    if(!plantId) { 
        showError(statusElement, "ID-ul centralei nu a fost gasit."); 
    }

    /*
        Se cauta basicsId 
            -> Daca nu exista => logica pentru post 
            -> Daca exista => logica de get + put
    */ 
    if(!basicsId) { 
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
                showSuccess(statusElement, "Datele au fost salvate cu succes!"); 

                window.history.replaceState({}, '', `?id=${response.plantId}&basicsId=${response.basicsId}`)
                window.location.href = `/pages/power-plants/geological.html?id=${response.plantId}`;

                form.reset(); 
            } catch(error) { 
                console.error(error.message); 
                showError(statusElement, "Eroare la adaugarea informatiilor despre centrala"); 
            }
        })
    } else { 
        try { 
            const response = await powerPlantService.getBasics(plantId);
            const d = response.data; 
            
            document.getElementById("capacity").value = d.capacity ?? "";
            document.getElementById("constructionDurationYears").value = d.constructionDurationYears ?? "";
            document.getElementById("description").value = d.description ?? "";
        } catch(error) { 
            console.error(error.message); 
            showMessage(statusElement, "Eroare la gasirea datelor despre centrala."); 
            return;
        }

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
                showSuccess(statusElement, "Datele centralei au fost actualzate cu succes!"); 
            } catch(error) { 
                console.erorr(error.message); 
                showError(statusElement, "Eroare la actualizarea datelor centralei."); 
            }
        })      
    }
})