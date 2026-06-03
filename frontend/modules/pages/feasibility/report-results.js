import { feasibilityReportService } from '../../services/feasibilityReportService.js';  
import { FeasibilityReportDTO } from '../../dto/FeasibilityReportDTO.js'; 
import { getQueryParam } from '../../utils/urlHelper.js'; 

const plantId = getQueryParam("id"); 

export function populateFeasibilityReport(rawData) {
    const dto = FeasibilityReportDTO(rawData);

    const dateText = dto.createdAt ? new Date(dto.createdAt).toLocaleDateString('ro-RO') : '--/--/----';
    const metaEl = document.getElementById('report-meta');
    if (metaEl) {
        metaEl.textContent = `DATA GENERĂRII: ${dateText}`;
    }

    const statusEl = document.getElementById('report-status');
    if (statusEl && dto.status) {
        statusEl.textContent = `STATUS: ${dto.status}`;
        statusEl.className = 'tag';
        
        const upperStatus = dto.status.toUpperCase();
        if (upperStatus === 'REJECTED' || upperStatus === 'FAILED' || upperStatus === 'CRITICAL') {
            statusEl.classList.add('danger');
        } else if (upperStatus === 'PENDING' || upperStatus === 'WARNING' || upperStatus === 'REVIEW') {
            statusEl.classList.add('warn');
        }
    }

    const msgEl = document.getElementById('report-message');
    if (msgEl) msgEl.textContent = dto.message || 'Niciun mesaj suplimentar furnizat de sistem.';

    const scoreEl = document.getElementById('report-nsvi-score');
    if (scoreEl) scoreEl.textContent = dto.nsviScore !== null ? dto.nsviScore : '--';

    const tbody = document.getElementById('deficiencies-table-body');
    if (tbody) {
        tbody.innerHTML = ''; 

        if (dto.deficiencies && dto.deficiencies.length > 0) {
            dto.deficiencies.forEach((defRaw, index) => {
                let def = defRaw;
                if (typeof defRaw === 'string') {
                    try {
                        def = JSON.parse(defRaw);
                    } catch (e) {
                        def = { reason: defRaw }; 
                    }
                }

                const tr = document.createElement('tr');
                
                const tdIndex = document.createElement('td');
                tdIndex.textContent = index + 1;
                tdIndex.style.color = 'var(--muted)';
                tr.appendChild(tdIndex);
                
                const tdDesc = document.createElement('td');
                
                const reasonDiv = document.createElement('div');
                reasonDiv.style.marginBottom = '10px';
                reasonDiv.style.color = 'var(--text)';
                reasonDiv.textContent = def.reason || 'Nespecificat';
                tdDesc.appendChild(reasonDiv);

                const tagsDiv = document.createElement('div');
                tagsDiv.style.display = 'flex';
                tagsDiv.style.gap = '8px';
                tagsDiv.style.flexWrap = 'wrap';

                if (def.parameter) {
                    const paramTag = document.createElement('span');
                    paramTag.className = 'tag';
                    paramTag.style.fontSize = '0.7rem';
                    paramTag.textContent = `PARAM: ${def.parameter}`;
                    tagsDiv.appendChild(paramTag);
                }

                if (def.reactor_source) {
                    const sourceTag = document.createElement('span');
                    sourceTag.className = 'tag warn';
                    sourceTag.style.fontSize = '0.7rem';
                    sourceTag.textContent = `SURSĂ: ${def.reactor_source}`;
                    tagsDiv.appendChild(sourceTag);
                }

                tdDesc.appendChild(tagsDiv);
                tr.appendChild(tdDesc);

                // Coloana 3: Impact (Penalizare)
                const tdPenalty = document.createElement('td');
                tdPenalty.style.textAlign = 'right';
                
                if (def.penalty !== undefined && def.penalty !== null) {
                    const penaltyTag = document.createElement('span');
                    penaltyTag.className = 'tag danger';
                    penaltyTag.style.fontSize = '0.85rem';
                    penaltyTag.textContent = def.penalty > 0 ? `-${def.penalty}` : def.penalty; // Asigură formatul negativ
                    tdPenalty.appendChild(penaltyTag);
                } else {
                    tdPenalty.textContent = '--';
                    tdPenalty.style.color = 'var(--muted)';
                }
                tr.appendChild(tdPenalty);
                
                tbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'empty-state';
            td.style.textAlign = 'center';
            td.textContent = 'ZERO DEFICIENȚE DETECTATE. PARAMETRI OPTIMI.';
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
        const response = await feasibilityReportService.getReport(plantId); 
        
        console.log({ response }); 
        if(!response.success) { 
            alert("A aparut o problema la cautarea raportului"); 
            return; 
        }

        populateFeasibilityReport(response.data); 
    } catch(error) {    
        console.error(error.message); 
        alert("A aparut o eroare la cautarea raportului"); 
    }
}); 