import { FeasibilityReportDTO } from '../../dto/FeasibilityReportDTO.js';

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
        } else if (dto.errors && dto.errors.length > 0) {
            dto.errors.forEach((err, index) => {
                const tr = document.createElement('tr');
                const tdIndex = document.createElement('td');
                tdIndex.textContent = index + 1;
                tdIndex.style.color = 'var(--muted)';
                tr.appendChild(tdIndex);
                const tdDesc = document.createElement('td');
                tdDesc.textContent = err;
                tdDesc.style.color = 'var(--red)';
                tr.appendChild(tdDesc);
                const tdPenalty = document.createElement('td');
                const critTag = document.createElement('span');
                critTag.className = 'tag danger';
                critTag.style.fontSize = '0.85rem';
                critTag.textContent = 'CRITIC';
                tdPenalty.appendChild(critTag);
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
