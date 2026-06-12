import { api } from '../core/api.js';
import { escapeHtml } from '../utils/escapeHtml.js';
import { mapService } from '../services/mapService.js';

document.addEventListener('DOMContentLoaded', async () => {
    const statusEl = document.getElementById('map-status');
    const listEl = document.getElementById('map-list');

    try {
        try {
            await api.get('/user/status');
        } catch (e) {
            if (e.status === 401) {
                window.location.href = 'login.html';
                return;
            }
            throw e;
        }

        const payload = await mapService.getMapData();
        const plants = Array.isArray(payload.data) ? payload.data.filter(p => p.status === 'APPROVED') : [];

        if (plants.length === 0) {
            statusEl.textContent = 'Nu există centrale cu coordonate.';
            listEl.innerHTML = '<div class="map-empty">Niciun rezultat.</div>';
            return;
        }

        listEl.innerHTML = plants.map(p => `
            <article class="map-item">
                <h3>${escapeHtml(p.name || 'Centrală')}</h3>
                <div class="map-item-meta">${escapeHtml(p.country || '')}</div>
                <div class="map-item-meta">${escapeHtml(p.coordinates_label || '')}</div>
                <div class="map-item-actions"><a class="button secondary" href="${escapeHtml(p.edit_url)}">Editează</a></div>
            </article>
        `).join('');

        window.PlantMap.setupPlantsOverviewMap({ mapId: 'plants-map', plants });
        statusEl.textContent = `${plants.filter(p => p.has_coordinates).length} centrală(e) afișată(e)`;
    } catch (err) {
        console.error('Map load error', err);
        statusEl.textContent = 'Eroare la încărcarea hărții.';
        listEl.innerHTML = '<div class="map-empty">Eroare la încărcare.</div>';
    }
});
