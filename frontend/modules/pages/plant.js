import { powerPlantService } from '../services/powerPlantService.js'
import { PlantRequestDTO } from '../dto/PlantRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js'; 

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("plant-form"); 
    const statusElement = document.getElementById("status-message"); 

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
            const response = await powerPlantService.create(dto); 
            showSuccess(statusElement, "Datele au fost salvate cu succes!"); 
            form.reset(); 

            window.location.href = `/pages/power-plants/basics.html?id=${response.plantId}`;
        } catch(error) { 
            console.error(error.message); 
            showError(statusElement, "Eroare la salvarea informatiilor despre centrala."); 
        }
    })
})