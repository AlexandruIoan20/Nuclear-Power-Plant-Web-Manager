import { powerPlantService } from '../services/powerPlantService.js'
import { PlantRequestDTO } from '../dto/PlantRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js'; 
import { getQueryParam } from '../utils/urlHelper.js'; 

const plantId = getQueryParam("id");  

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("plant-form"); 
    const statusElement = document.getElementById("status-message"); 

    /*
        Se cauta plantId 
            -> Daca nu exista => logica pentru post 
            -> Daca exista => logica de get + put
    */ 
    if(!plantId) { 
        form.addEventListener("submit", async(e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 
    
            const dto = PlantRequestDTO({ 
                name: document.getElementById("name").value,    
                country: document.getElementById("country").value, 
                latitude: document.getElementById("latitude").value, 
                longitude: document.getElementById("longitude").value
            }); 
    
            try { 
                const response = await powerPlantService.createPlantDetails(dto); 
                showSuccess(statusElement, "Datele au fost salvate cu succes!"); 

                window.history.replaceState({}, '', `?id=${response.plantId}`); 
                window.location.href = `/pages/power-plants/basics.html?id=${response.plantId}`;

                form.reset(); 
            } catch(error) { 
                console.error(error.message); 
                showError(statusElement, "Eroare la salvarea informatiilor despre centrala."); 
            }
        }); 
    } else { 
        try { 
            const response = await powerPlantService.getPlantDetails(plantId); 
            const d = response.data;  

            document.getElementById("name").value = d.name ?? ""; 
            document.getElementById("country").value = d.country ?? ""; 
            document.getElementById("latitude").value = d.latitude ?? ""; 
            document.getElementById("longitude").value = d.longitude ?? ""; 
        } catch(error) { 
            console.error(error.message); 
            showError(statusElement, "Eroare la gasirea datelor centralei."); 
            return; 
        }

        form.addEventListener("submit", async (e) => { 
            e.preventDefault(); 
            clearStatus(statusElement); 

            const dto = PlantRequestDTO({ 
                name: document.getElementById("name").value,    
                country: document.getElementById("country").value, 
                latitude: document.getElementById("latitude").value, 
                longitude: document.getElementById("longitude").value
            }); 

            try { 
                await powerPlantService.updatePlantDetails(dto, plantId); 
                showSuccess(statusElement, "Datele centralei au fost actualizate cu succes!"); 
            } catch(error) { 
                console.error(error.message); 
                showError(statusElement, "Eroare la actualizarea datelor centralei."); 
            }
        })
    }
})