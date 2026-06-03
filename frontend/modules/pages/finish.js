import { powerPlantService } from '../services/powerPlantService.js'; 
import { feasibilityReportService } from '../services/feasibilityReportService.js'; 
import { GetPlantDTO } from '../dto/GetPlantDTO.js'; 
import { getQueryParam } from '../utils/urlHelper.js';
import { clearHeaderState } from '../ui/form-header/formHeaderState.js'; 

const plantId = getQueryParam("id"); 

function setText(id, value, suffix = '') { 
    const element = document.getElementById(id); 
    if(element) element.textContent = (value !== null && value !== undefined && value !== '') ? `${value}${suffix}` : "--"; 
}

function isComplete(dto) {
    const ignored = ['id', 'basicId', 'geologicalId', 'technicalId', "safetySystems"];
    
    return Object.entries(dto).every(([key, value]) => {
        if (ignored.includes(key)) return true;
        
        if (Array.isArray(value)) return value.length > 0;
        
        return value !== null && value !== undefined && value !== '';
    });
}

function populatePlantPage(rawData) { 
    const dto = GetPlantDTO(rawData); 

    const verifyButton = document.getElementById("btn-verify"); 
    
    if(isComplete(dto)) { 
        verifyButton.disabled = false; 
        verifyButton.addEventListener("click", async(e) => { 
            try { 
                const response = await feasibilityReportService.createReport(plantId); 

                console.log({ response }); 

                if(response.success) { 
                    clearHeaderState(); 
                    window.location.href = `/pages/feasibility/report-results.html?id=${plantId}`;
                }
            } catch(error) { 
                console.error(error.message); 
                alert("Eroare la generarea raportului.");
            }
        })
    }
    else verifyButton.disabled = true; 

    console.log(verifyButton); 
    
    setText('plant-name', dto.name, ' (UNIDENTIFIED)'); 

    const locationStr = [dto.country, dto.latitude ? `LAT: ${dto.latitude}` : null, dto.longitude ? `LNG: ${dto.longitude}` : null]
        .filter(Boolean)
        .join(' | ');

    setText('plant-location', locationStr); 

    const statusElement = document.getElementById('plant-status'); 
    if(statusElement && dto.status) { 
        statusElement.textContent = `STATUS: ${dto.status}`; 
        statusElement.className = 'tag'; 

        /*
            TODO: Adaugarea acestor tipuri de enum (?)
        */ 
        if(dto.status.toUpperCase() === 'CRITICAL') statusElement.classList.add('danger'); 
        if(dto.status.toUpperCase() === 'STANDBY') statusElement.classList.add('warn'); 
    } else if(statusElement) { 
        statusElement.style.display = 'none'; 
    }

    setText('plant-description', dto.description);
    setText('plant-capacity', dto.capacity, ' MW');
    setText('plant-efficiency', dto.estimatedEfficiency, '%');
    setText('plant-duration', dto.constructionDurationYears, ' YRS');

    setText('soil-type', dto.soilType);
    setText('seismic-stability', dto.seismicStability);
    setText('flood-risk', dto.floodRisk);
    setText('water-source', dto.waterSourceType);
    setText('water-flow', dto.waterFlowRate);
    setText('groundwater-level', dto.groundwaterLevel);
    setText('operational-risk', dto.operationalRiskLevel);
    setText('geo-risk-score', dto.geologicalRiskScore, ' / 100');
    setText('transport-score', dto.transportInfrastructureScore, ' / 100');

    const tbody = document.getElementById('reactors-table-body');
    if (tbody) {
        tbody.innerHTML = '';
        
        if (dto.reactorConfigurations && dto.reactorConfigurations.length > 0) {
            dto.reactorConfigurations.forEach(config => {
                const tr = document.createElement('tr');
                
                const tdType = document.createElement('td');
                tdType.textContent = config.reactorType || '--';
                
                const tdCooling = document.createElement('td');
                tdCooling.textContent = config.coolingType || '--';
                
                tr.appendChild(tdType);
                tr.appendChild(tdCooling);
                tbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 2;
            td.className = 'empty-state';
            td.style.textAlign = 'center';
            td.textContent = 'NO CONFIGURATIONS DETECTED.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }
    }
}

document.addEventListener("DOMContentLoaded", async () => { 
    if(!plantId) { 
        alert("Centrala nu a fost gasita."); 
        return; 
    }

    try { 
        const rawData = await powerPlantService.getPlant(plantId); 

        console.log({ rawData }); 
        populatePlantPage(rawData); 
    } catch(error) {    
        console.error(error.message);
        alert("A aparut o eroare in preluarea informatiilor despre centrala");  
    }
})