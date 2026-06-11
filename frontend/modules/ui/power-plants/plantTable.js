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

export function renderTable(plants, goTo = null) {
    const tbody = document.getElementById('plants-tbody');
    document.getElementById('results-count').textContent = plants.length;

    if (!plants.length) {
        tbody.innerHTML = `<tr class="state-row"><td colspan="6">Nicio centrală găsită.</td></tr>`;
        return;
    }

    tbody.innerHTML = plants.map(p => {
        const href = goTo ? `${goTo}?id=${escapeHtml(p.id)}` : null;

        return `
            <tr data-id="${escapeHtml(p.id)}" style="${!goTo ? '' : 'cursor:pointer;'}">
                <td class="td-name">${escapeHtml(p.name ?? '—')}</td>
                <td>${escapeHtml(p.country ?? '—')}</td>
                <td class="td-id">${shortId(p.id)}</td>
                <td class="td-coords">${coords(p.latitude, p.longitude)}</td>
                <td>${statusTag(p.status)}</td>
                <td>
                    ${href ? `<a class="btn-view" href="${href}">Vezi →</a>` : ''}
                </td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('tr[data-id]').forEach(tr => {
        tr.addEventListener('click', (e) => {
            if (!goTo) return;
            if (e.target.closest('a')) return;
            window.location.href = `${goTo}?id=${tr.dataset.id}`;
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
    return `<span class="tag ${cls}">${escapeHtml(status)}</span>`;
}

function coords(lat, lng) {
    if (lat == null || lng == null) return '—';
    return `${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)}`;
}

function shortId(id) {
    if (!id) return '—';
    return `<span title="${escapeHtml(id)}">${escapeHtml(id.slice(0, 8))}...</span>`;
}
