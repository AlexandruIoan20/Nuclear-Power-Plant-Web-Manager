export function renderTable(plants) {
    const tbody = document.getElementById('plants-tbody');
    document.getElementById('results-count').textContent = plants.length;

    if (!plants.length) {
        tbody.innerHTML = `<tr class="state-row"><td colspan="6">Nicio centrală găsită.</td></tr>`;
        return;
    }

    tbody.innerHTML = plants.map(p => `
        <tr data-id="${p.id}">
            <td class="td-name">${p.name ?? '—'}</td>
            <td>${p.country ?? '—'}</td>
            <td class="td-id">${shortId(p.id)}</td>
            <td class="td-coords">${coords(p.latitude, p.longitude)}</td>
            <td>${statusTag(p.status)}</td>
            <td>
                <a class="btn-view" href="/pages/power-plants/finish.html?id=${p.id}">
                    Vezi →
                </a>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('tr[data-id]').forEach(tr => {
        tr.addEventListener('click', (e) => {
            if (e.target.closest('a')) return;
            window.location.href = `/pages/power-plants/finish.html?id=${tr.dataset.id}`;
        });
    });
}

function statusTag(status) {
    if (!status) return '<span class="tag draft">—</span>';
    const s = status.toUpperCase();
    let cls = '';
    if (s === 'ACTIVE' || s === 'APPROVED')  cls = '';
    else if (s === 'REVIEW' || s === 'DRAFT') cls = 'warn';
    else if (s === 'REJECTED' || s === 'CRITICAL' || s === 'INACTIVE') cls = 'danger';
    else cls = 'draft';
    return `<span class="tag ${cls}">${status}</span>`;
}

function coords(lat, lng) {
    if (lat == null || lng == null) return '—';
    return `${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)}`;
}

function shortId(id) {
    if (!id) return '—';
    return `<span title="${id}">${id.slice(0, 8)}...</span>`;
}