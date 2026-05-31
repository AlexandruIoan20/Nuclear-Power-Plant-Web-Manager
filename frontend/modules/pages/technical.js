import { powerPlantService } from '../services/powerPlantService.js'
import { ReactorType, CoolingType } from '../config/enums.js'; 
import { getQueryParam } from '../utils/urlHelper.js'; 
import { showError, showSuccess, clearStatus } from '../ui/showMessage.js'; 
import { TechnicalDataRequestDTO } from '../dto/TechnicalDataRequestDTO.js'; 

const plantId = getQueryParam("id"); 
const technicalId = getQueryParam("technicalId"); 

let reactorIndex = 0;

/*
    Adauga in DOM inputuri pentru customizarea unei scheme de reactor
    Numarul final de astfel de inputuri trebuie sa fie egal cu number_of_reactors din inputul din HTML 
*/
function createReactorBlock(index, selectedReactorType = "", selectedCoolingType = "") { 
    const block = document.createElement("div"); 
    block.className = "reactor-block"; 
    block.dataset.index = index; 

    const reactorOptions = ReactorType.map(t =>
        `<option value="${t.value}" ${selectedReactorType === t.value ? "selected" : ""}>${t.label}</option>`
    ).join("");

    const coolingOptions = CoolingType.map(c =>
        `<option value="${c.value}" ${selectedCoolingType === c.value ? "selected" : ""}>${c.label}</option>`
    ).join("");

    block.innerHTML = `
        <h4>Configurație Reactor ${index + 1}</h4>
        <div class="field">
            <label>Tip Reactor:</label>
            <select id="reactor_type_${index}" name="reactor_type_${index}">
                <option value="">-- Selectează tipul de reactor --</option>
                ${reactorOptions}
            </select>
        </div>
        <div class="field">
            <label>Tip Răcire:</label>
            <select id="cooling_type_${index}" name="cooling_type_${index}">
                <option value="">-- Selectează tipul de răcire --</option>
                ${coolingOptions}
            </select>
        </div>
        <button type="button" class="button btn-danger remove-reactor-btn">Șterge</button>
    `;

    return block; 
}

function collectReactorConfigs() { 
    const blocks = document.querySelectorAll(".reactor-block"); 
    const configs = []; 

    blocks.forEach(block => { 
        const index = block.dataset.index; 
        const reactorType = document.getElementById(`reactor_type_${index}`).value; 
        const coolingType = document.getElementById(`cooling_type_${index}`).value; 

        if(reactorType && coolingType) configs.push({ reactorType, coolingType }); 
    }); 

    return configs; 
}


document.addEventListener("DOMContentLoaded", async () => { 
    const form = document.getElementById("technical-form"); 
    const statusMessage = document.getElementById("status-message"); 
    const addButton = document.getElementById("add-reactor-btn"); 
    const container = document.getElementById("reactor-configurations-container"); 

    if(!plantId) { 
        showError(statusMessage, "ID centrala lipsa din URL."); 
        return; 
    }

    addButton.addEventListener("click", () => {
        const block = createReactorBlock(reactorIndex); 
        container.appendChild(block); 
        reactorIndex++; 
    }); 

    container.addEventListener("click", (e) => {
        if(e.target.classList.contains("remove-reactor-btn")) { 
            e.target.closest(".reactor-block").remove(); 
        }
    }); 

    if(!technicalId) { 
        form.addEventListener("submit", async(e) => { 
            e.preventDefault(); 
            clearStatus(statusMessage); 

            const dto = TechnicalDataRequestDTO({
                numberOfReactors: document.getElementById("number_of_reactors").value,
                estimatedEfficiency: document.getElementById("estimated_efficiency").value,
                operationalRiskLevel: document.getElementById("operational_risk_level").value,
                reactorConfigurations: collectReactorConfigs(),
            });

            console.log({ dto }); 

            try { 
                await powerPlantService.createTechnical(dto, plantId); 
                showSuccess(statusMessage, "Datele technice au fost salvate cu succes!"); 

                form.reset(); 
                container.innerHTML = ""; 
            } catch(error) { 
                console.log(error.message); 
                showError(statusMessage, "Eroare la salvarea datelor tehnice"); 
            }
        }); 
    } else { 
        try { 
            const response = await powerPlantService.getTechnical(plantId); 
            const d = response.data; 

            document.getElementById("number_of_reactors").value = d.numberOfReactors ?? "";
            document.getElementById("estimated_efficiency").value = d.estimatedEfficiency ?? "";
            document.getElementById("operational_risk_level").value = d.operationalRiskLevel ?? "";

            if (d.reactorConfigurations && d.reactorConfigurations.length > 0) {
                d.reactorConfigurations.forEach(config => {
                    const block = createReactorBlock(reactorIndex, config.reactorType, config.coolingType);
                    container.appendChild(block);
                    reactorIndex++;
                });
            }
        } catch (error) { 
            console.error(error.message);
            showError(statusMessage, "Eroare la incarcarea datelor tehnice");  
        }

        form.addEventListener("submit", async (e) => { 
            e.preventDefault(); 
            clearStatus(statusMessage); 

            const dto = TechnicalDataRequestDTO({
                numberOfReactors: document.getElementById("number_of_reactors").value,
                estimatedEfficiency: document.getElementById("estimated_efficiency").value,
                operationalRiskLevel: document.getElementById("operational_risk_level").value,
                reactorConfigurations: collectReactorConfigs(),
            });

            try { 
                await powerPlantService.updateTechnical(dto, plantId); 
                showSuccess(statusElement, "Datele tehnice au fost salvate cu succes!"); 
            } catch(error) { 
                console.error(error.message); 
                showError(statusMessage, "Eroare la actualizarea datelor tehnnice."); 
            }
        })
    }
})