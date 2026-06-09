export function renderReactorTable(reactors, plantId) {
    const tbody = document.getElementById('reactors-tbody');
    document.getElementById('results-count').textContent = reactors.length;

    if (!reactors.length) {
        tbody.innerHTML = `<tr class="state-row"><td colspan="8">Niciun reactor găsit.</td></tr>`;
        return;
    }

    tbody.innerHTML = reactors.map(r => `
        <tr data-id="${r.id}">
            <td class="td-id">${shortId(r.id)}</td>
            <td>${r.reactorCode ?? '—'}</td>
            <td>${r.reactorType ?? '—'}</td>
            <td>${r.coolingType ?? '—'}</td>
            <td>${statusTag(r.operationalStatus)}</td>
            <td>${r.thermalPowerMw != null ? r.thermalPowerMw + ' MW' : '—'}</td>
            <td>${r.electricalPowerMw != null ? r.electricalPowerMw + ' MW' : '—'}</td>
            <td>
                <button class="btn-edit-reactor button" style="padding: 6px 10px; font-size: 0.75rem; width: auto; margin-right: 4px;">Editează</button>
                <button class="btn-delete-reactor button" style="padding: 6px 10px; font-size: 0.75rem; width: auto; background: linear-gradient(180deg, rgba(255,77,77,0.14), rgba(255,77,77,0.05)); border-color: rgba(255,77,77,0.7); color: #ff8787;">Șterge</button>
            </td>
        </tr>
    `).join('');
}

function statusTag(status) {
    if (!status) return '<span class="tag draft">—</span>';
    const s = status.toUpperCase();
    let cls = '';
    if (s === 'FULL_POWER' || s === 'STARTUP' || s === 'POWER_ASCENT') cls = '';
    else if (s === 'SHUTDOWN' || s === 'COLD_STANDBY' || s === 'HOT_STANDBY' || s === 'PARTIAL_POWER') cls = 'warn';
    else if (s === 'EMERGENCY_SHUTDOWN' || s === 'UNPLANNED_OUTAGE') cls = 'danger';
    else cls = 'warn';
    return `<span class="tag ${cls}">${status.replace(/_/g, ' ')}</span>`;
}

function shortId(id) {
    if (!id) return '—';
    return `<span title="${id}">${id.slice(0, 8)}...</span>`;
}
