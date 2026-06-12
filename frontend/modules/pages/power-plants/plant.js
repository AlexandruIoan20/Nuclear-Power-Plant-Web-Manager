import { powerPlantService } from '../../services/powerPlantService.js'
import { PlantRequestDTO } from '../../dto/PlantRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js'; 
import { getQueryParam } from '../../utils/urlHelper.js'; 

const plantId = getQueryParam("id");  

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("plant-form"); 
    const statusElement = document.getElementById("status-message"); 

    if(!plantId) { 
        form.addEventListener("submit", async(e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 
    
            const dto = PlantRequestDTO({ 
                name: document.getElementById("name").value,    
            }); 
    
            try { 
                const response = await powerPlantService.createPlantDetails(dto);
                showSuccess(statusElement, "Datele au fost salvate cu succes!"); 

                window.history.replaceState({}, '', `?id=${response.plantId}`); 
                window.location.href = `/pages/power-plants/basics.html?id=${response.plantId}`;

                form.reset(); 
            } catch(error) { 
                showError(statusElement, "Eroare la salvarea informațiilor despre centrală."); 
            }
        }); 
    } else { 
        try { 
            const response = await powerPlantService.getPlantDetails(plantId); 
            const d = response.data;  

            document.getElementById("name").value = d.name ?? ""; 
        } catch(error) { 
            showError(statusElement, "Eroare la găsirea datelor centralei."); 
            return; 
        }

        form.addEventListener("submit", async (e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 

            const dto = PlantRequestDTO({ 
                name: document.getElementById("name").value,    
            }); 

            try { 
                await powerPlantService.updatePlantDetails(dto, plantId); 
                showSuccess(statusElement, "Datele centralei au fost actualizate cu succes!"); 
            } catch(error) { 
                showError(statusElement, "Eroare la actualizarea datelor centralei."); 
            }
        })
    }
})
