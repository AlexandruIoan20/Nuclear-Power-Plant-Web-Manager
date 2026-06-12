import { powerPlantService } from '../../services/powerPlantService.js';
import { clearHeaderState } from '../../ui/form-header/formHeaderState.js';
import { applyFilters } from '../../ui/power-plants/plantFilters.js';
import { PlantListResponseDTO } from '../../dto/PlantListResponseDTO.js';
import { logger } from '../../core/logger.js';
import { clearHeaderState } from '../../ui/form-header/formHeaderState.js'; 

let allPlants = [];
let sortCol = 'name';
let sortDir = 'asc';
let goTo = "/pages/power-plants/finish.html";

function updateSortHeaders() {
    document.querySelectorAll('th[data-col]').forEach(th => {
        th.classList.remove('sorted');
        th.querySelector('.sort-icon').textContent = '↕';
    });
    const active = document.querySelector(`th[data-col="${sortCol}"]`);
    if (active) {
        active.classList.add('sorted');
        active.querySelector('.sort-icon').textContent = sortDir === 'asc' ? '↑' : '↓';
    }
}

document.getElementById('filter-name').addEventListener('input', () => applyFilters(allPlants, sortCol, sortDir, goTo));
document.getElementById('filter-country').addEventListener('input', () => applyFilters(allPlants, sortCol, sortDir, goTo));

document.getElementById('btn-reset').addEventListener('click', () => {
    document.getElementById('filter-name').value = '';
    document.getElementById('filter-country').value = '';
    applyFilters(allPlants, sortCol, sortDir, goTo);
});

document.querySelectorAll('th[data-col]').forEach(th => {
    if (!th.querySelector('.sort-icon')) return;
    th.addEventListener('click', () => {
        const col = th.dataset.col;
        if (col === 'coords') return;
        if (sortCol === col) {
            sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            sortCol = col;
            sortDir = 'asc';
        }
        updateSortHeaders();
        applyFilters(allPlants, sortCol, sortDir, goTo);
    });
});

document.addEventListener('DOMContentLoaded', async () => {
    clearHeaderState();

    // ---- Export All ----
    document.getElementById('btn-export-all-json').addEventListener('click', async () => {
        try {
            await powerPlantService.exportAllPlantsJson();
        } catch (e) {
            alert('Export error: ' + (e.message || 'Unknown error'));
        }
    });

    document.getElementById('btn-export-all-csv').addEventListener('click', async () => {
        try {
            await powerPlantService.exportAllPlantsCsv();
        } catch (e) {
            alert('Export error: ' + (e.message || 'Unknown error'));
        }
    });

    // ---- Import ----
    document.getElementById('btn-import').addEventListener('click', () => {
        window.location.href = '/pages/power-plants/import.html';
    });

    // ---- Load plants ----
    try {
        const response = await powerPlantService.getAll();

        allPlants = (response.data ?? []).map(p => PlantListResponseDTO(p));

        // Delegate export
        applyFilters(allPlants, sortCol, sortDir, goTo);

        // Attach per-row export buttons (delegated via event bubbling)
        document.getElementById('plants-tbody').addEventListener('click', async (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            const id = btn.dataset.id;
            if (!id) return;

            if (btn.classList.contains('btn-export-json')) {
                e.stopPropagation();
                try {
                    await powerPlantService.exportPlantJson(id);
                } catch (err) {
                    alert('Export error: ' + (err.message || 'Unknown error'));
                }
            } else if (btn.classList.contains('btn-export-csv')) {
                e.stopPropagation();
                try {
                    await powerPlantService.exportPlantCsv(id);
                } catch (err) {
                    alert('Export error: ' + (err.message || 'Unknown error'));
                }
            }
        });
    } catch (error) {
        logger.error(error.message);
        alert("Eroare la încărcarea centralelor");
    }
});
