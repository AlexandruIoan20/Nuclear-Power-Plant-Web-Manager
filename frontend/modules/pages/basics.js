import { powerPlantService } from '../services/powerPlantService.js'; 
import { BasicDataRequestDTO} from '../dto/BasicDataRequestDTO.js'; 
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js'; 
import { getQueryParam } from '../utils/urlHelper.js'; 

const plantId = getQueryParam("id");

document.addEventListener("DOMContentLoaded", async() => { 
    const form = document.getElementById("basics-form"); 
    const statusElement = document.getElementById("status-message"); 

    form.addEventListener("submit", async(e) => { 
        e.preventDefault(); 
        clearStatus(statusElement); 

        const dto = BasicDataRequestDTO({ 
            capacity: document.getElementById("capacity").value, 
            constructionDurationYears: document.getElementById("constructionDurationYears").value, 
            description: document.getElementById("description").value
        }); 

        console.log({ dto }); 

        try { 
            await powerPlantService.createBasics(dto, plantId); 
            showSuccess(statusElement, "Datele au fost salvate cu succes!"); 
            form.reset(); 
        } catch(error) { 
            console.error(error.message); 
            showError(statusElement, "Eroare la adaugarea informatiilor despre centrala"); 
        }
    })
})