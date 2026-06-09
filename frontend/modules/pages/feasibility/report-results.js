import { feasibilityReportService } from '../../services/feasibilityReportService.js';  
import { FeasibilityReportDTO } from '../../dto/FeasibilityReportDTO.js'; 
import { getQueryParam } from '../../utils/urlHelper.js'; 

const plantId = getQueryParam("id"); 

function populateErrors(errors) {
    const card = document.getElementById('errors-card');
    const container = document.getElementById('errors-container');
    if (!card || !container) return;

    if (!errors || errors.length === 0) {
        card.style.display = 'none';
        return;
    }

    card.style.display = 'block';
    container.innerHTML = '';

    errors.forEach((err, index) => {
        const errorItem = document.createElement('div');
        errorItem.style.cssText = 'display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);';
        
        if (index === errors.length - 1) {
            errorItem.style.borderBottom = 'none';
        }

        const bullet = document.createElement('span');
        bullet.textContent = '✕';
        bullet.style.cssText = 'color:var(--red);font-size:1.1rem;flex-shrink:0;margin-top:2px;';
        errorItem.appendChild(bullet);

        const text = document.createElement('span');
        text.textContent = typeof err === 'string' ? err : (err.reason || err.message || JSON.stringify(err));
        text.style.cssText = 'color:var(--text);line-height:1.5;';
        errorItem.appendChild(text);

        container.appendChild(errorItem);
    });
}

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

    populateErrors(dto.errors);

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
                    penaltyTag.textContent = def.penalty > 0 ? `-${def.penalty}` : def.penalty;
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

function showLoading(message) {
    const shell = document.querySelector('.page-shell');
    if (!shell) return;
    let loader = document.getElementById('loading-indicator');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'loading-indicator';
        loader.style.cssText = 'text-align:center;padding:40px;color:var(--muted);font-size:1.1rem;';
        shell.prepend(loader);
    }
    loader.textContent = message;
}

function hideLoading() {
    const loader = document.getElementById('loading-indicator');
    if (loader) loader.remove();
}

document.addEventListener("DOMContentLoaded", async () => { 
    if(!plantId) { 
        alert("Centrala nu a fost gasita."); 
        return; 
    }

    showLoading("Se încarcă raportul..."); 

    try { 
        const response = await feasibilityReportService.getReport(plantId); 
        
        console.log({ response }); 
        if(!response.success) { 
            hideLoading(); 
            alert("A aparut o problema la cautarea raportului"); 
            return; 
        }

        hideLoading(); 
        populateFeasibilityReport(response.data); 
    } catch(error) {    
        hideLoading(); 
        console.error(error.message); 
        alert("A aparut o eroare la cautarea raportului"); 
    }
}); 