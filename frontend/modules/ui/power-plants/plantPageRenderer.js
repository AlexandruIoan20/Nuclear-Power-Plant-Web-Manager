import { GetPlantDTO } from '../../dto/GetPlantDTO.js';
import { renderStatusBadge } from './plantStatusBadge.js';

export function setText(id, value, suffix = '') { 
    const element = document.getElementById(id); 
    if(element) element.textContent = (value !== null && value !== undefined && value !== '') ? `${value}${suffix}` : "--"; 
}

/*
    Populeaza sectiunea de cod ce se ocupa cu afisarea informatiilor despre proiectul 
    de centrala ce trebuie / a fost verificat 

    Momentan folosit in: finish.html si admin/validate.html
*/ 
export function populatePlantPage(rawData) { 
    const dto = GetPlantDTO(rawData); 
    
    setText('plant-name', dto.name); 

    const locationStr = [dto.country, dto.latitude ? `LAT: ${dto.latitude}` : null, dto.longitude ? `LNG: ${dto.longitude}` : null]
        .filter(Boolean)
        .join(' | ');

    setText('plant-location', locationStr); 

    const statusElement = document.getElementById('plant-status'); 
    if(statusElement && dto.status) { 
        statusElement.outerHTML = renderStatusBadge(dto.status); 
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