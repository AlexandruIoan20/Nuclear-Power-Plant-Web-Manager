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
        tbody.innerHTML = `<tr class="state-row"><td colspan="7">Nicio centrală găsită.</td></tr>`;
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
                <td class="td-actions">
                    ${href ? `<a class="btn-view" href="${href}">Vezi</a>` : ''}
                    <button class="btn-export-json" data-id="${escapeHtml(p.id)}" title="Export JSON">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </button>
                    <button class="btn-export-csv" data-id="${escapeHtml(p.id)}" title="Export CSV">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </button>
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
