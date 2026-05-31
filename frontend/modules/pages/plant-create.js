import { powerPlantService } from '../services/powerPlantService.js'
import { CreatePlantRequestDTO } from '../dto/CreatePlantRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js'; 

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("plant-form"); 
    const statusElement = document.getElementById("status-message"); 

    form.addEventListener("submit", async(e) => { 
        e.preventDefault(); 
        clearStatus(statusElement); 

        const dto = CreatePlantRequestDTO({ 
            name: document.getElementById("name").value,    
            country: document.getElementById("country").value, 
            latitude: document.getElementById("latitude").value, 
            longitude: document.getElementById("longitude").value
        }); 

        try { 
            await powerPlantService.create(dto); 
            showSuccess(statusElement, "Datele au fost salvate cu succes!"); 
            form.reset(); 
        } catch(error) { 
            showError(statusElement, error.message || "Eroare la salvare"); 
        }
    })
})