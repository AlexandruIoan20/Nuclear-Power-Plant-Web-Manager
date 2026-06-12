import { renderStatusBadge } from './plantStatusBadge.js';

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
        tbody.innerHTML = `<tr class="state-row"><td colspan="5">Nicio centrală găsită.</td></tr>`;
        return;
    }

    tbody.innerHTML = plants.map(p => {
        const href = goTo ? `${goTo}?id=${escapeHtml(p.id)}` : null;

        return `
            <tr data-id="${escapeHtml(p.id)}" style="${!goTo ? '' : 'cursor:pointer;'}">
                <td class="td-name">${escapeHtml(p.name ?? '—')}</td>
                <td>${escapeHtml(p.country ?? '—')}</td>
                <td class="td-coords">${coords(p.latitude, p.longitude)}</td>
                <td>${renderStatusBadge(p.status)}</td>
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

function coords(lat, lng) {
    if (lat == null || lng == null) return '—';
    return `${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)}`;
}


