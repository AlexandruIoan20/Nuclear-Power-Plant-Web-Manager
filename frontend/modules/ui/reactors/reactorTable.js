function escapeHtml(value) {
    return String(value).replace(/[&<>"]|'/g, (character) => {
        const escapeMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return escapeMap[character] || character;
    });
}

export function renderReactorTable(reactors, plantId) {
    const tbody = document.getElementById('reactors-tbody');
    document.getElementById('results-count').textContent = reactors.length;

    if (!reactors.length) {
        tbody.innerHTML = '<tr class="state-row"><td colspan="8">Niciun reactor găsit.</td></tr>';
        return;
    }

    tbody.innerHTML = reactors.map(r => `
        <tr data-id="${escapeHtml(r.id)}">
            <td class="td-id">${shortId(r.id)}</td>
            <td>${escapeHtml(r.reactorCode ?? '—')}</td>
            <td>${escapeHtml(r.reactorType ?? '—')}</td>
            <td>${escapeHtml(r.coolingType ?? '—')}</td>
            <td>${statusTag(r.operationalStatus)}</td>
            <td>${r.thermalPowerMw != null ? r.thermalPowerMw + ' MW' : '—'}</td>
            <td>${r.electricalPowerMw != null ? r.electricalPowerMw + ' MW' : '—'}</td>
            <td class="td-actions">
                <button class="btn-monitor-reactor button" style="font-size:0.72rem;padding:4px 8px;width:auto;margin-right:3px;">Live</button>
                <button class="btn-edit-reactor button" style="font-size:0.72rem;padding:4px 8px;width:auto;margin-right:3px;">Editează</button>
                <button class="btn-delete-reactor button" style="font-size:0.72rem;padding:4px 8px;width:auto;color:#a06060;border-color:rgba(160,96,96,0.4);">Șterge</button>
            </td>
        </tr>
    `).join('');
}

function statusTag(status) {
    if (!status) return '<span class="tag">—</span>';
    const s = status.toUpperCase();
    let cls = '';
    if (s === 'EMERGENCY_SHUTDOWN' || s === 'UNPLANNED_OUTAGE') cls = 'danger';
    else if (s === 'SHUTDOWN' || s === 'COLD_STANDBY' || s === 'HOT_STANDBY' || s === 'PARTIAL_POWER') cls = 'warn';
    return '<span class="tag ' + cls + '">' + status.replace(/_/g, ' ') + '</span>';
}

function shortId(id) {
    if (!id) return '—';
    return '<span title="' + id + '">' + id.slice(0, 8) + '…</span>';
}
