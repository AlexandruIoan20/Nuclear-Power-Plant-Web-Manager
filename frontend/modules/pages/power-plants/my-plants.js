import { powerPlantService } from '../../services/powerPlantService.js';
import { PlantListResponseDTO } from '../../dto/PlantListResponseDTO.js';
import { renderStatusBadge } from '../../ui/power-plants/plantStatusBadge.js';

const NAV_MAP = {
    DRAFT:     { href: (id) => `/pages/power-plants/create.html?id=${encodeURIComponent(id)}`,          label: 'Editează' },
    REVIEW:    { href: (id) => `/pages/feasibility/report-results.html?id=${encodeURIComponent(id)}`,  label: 'Vezi Raportul' },
    REJECTED:  { href: (id) => `/pages/feasibility/report-results.html?id=${encodeURIComponent(id)}`,  label: 'Vezi Raportul' },
    APPROVED:  { href: (id) => `/pages/reactors/list.html?plantId=${encodeURIComponent(id)}`,          label: 'Gestionează' },
};

function escapeHtml(value) {
    return String(value).replace(/[&<>"]|'/g, (c) => {
        const m = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return m[c] || c;
    });
}

function navigate(plant) {
    const entry = NAV_MAP[plant.status];
    if (!entry) return;
    window.location.href = entry.href(plant.id);
}

function renderTable(plants) {
    const tbody = document.getElementById('plants-tbody');
    document.getElementById('results-count').textContent = plants.length;

    if (!plants.length) {
        tbody.innerHTML = '<tr class="state-row"><td colspan="4">Nu ai nicio centrală înregistrată.</td></tr>';
        return;
    }

    tbody.innerHTML = plants.map(p => {
        const entry = NAV_MAP[p.status];
        const actionsHtml = entry
            ? `<a href="${entry.href(p.id)}" class="button" style="width:auto;padding:6px 12px;font-size:0.78rem;">${entry.label}</a>`
            : '';
        return `
            <tr data-id="${escapeHtml(p.id)}" data-status="${escapeHtml(p.status)}" style="cursor:pointer;">
                <td class="td-name">${escapeHtml(p.name || '—')}</td>
                <td>${escapeHtml(p.country || '—')}</td>
                <td>${renderStatusBadge(p.status)}</td>
                <td>${actionsHtml}</td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('tr[data-id]').forEach(tr => {
        tr.addEventListener('click', (e) => {
            if (e.target.closest('a, button')) return;
            const id = tr.dataset.id;
            const status = tr.dataset.status;
            navigate({ id, status });
        });
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await powerPlantService.getMyPlants();
        const plants = (response.data ?? []).map(p => PlantListResponseDTO(p));
        renderTable(plants);
    } catch (error) {
        document.getElementById('plants-tbody').innerHTML =
            '<tr class="state-row"><td colspan="4" style="color:var(--danger);">Eroare la încărcarea centralelor.</td></tr>';
    }
});
