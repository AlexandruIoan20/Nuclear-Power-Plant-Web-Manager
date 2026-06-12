import { powerPlantService } from '../../services/powerPlantService.js'; 
import { clearHeaderState, } from '../../ui/form-header/formHeaderState.js'; 
import { applyFilters } from '../../ui/power-plants/plantFilters.js'; 
import { PlantStatus } from '../../config/enums.js'; 
import { loadSelect } from '../../ui/selectLoader.js'; 
import { logger } from '../../core/logger.js';
loadSelect('filter-status', PlantStatus); 

let masterPlants = [];
let allPlants = [];
let sortCol = 'name';
let sortDir = 'asc';

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

function navigateToPlant(plantId) {
    const plant = masterPlants.find(p => p.id === plantId);
    if (plant?.status === 'APPROVED') {
        window.location.href = `/pages/reactors/list.html?plantId=${plantId}`;
    } else {
        window.location.href = `/pages/admin/validate.html?id=${plantId}`;
    }
}

document.getElementById('filter-name').addEventListener('input', () => applyFilters(allPlants, sortCol, sortDir));
document.getElementById('filter-country').addEventListener('input', () => applyFilters(allPlants, sortCol, sortDir));

document.getElementById('btn-reset').addEventListener('click', () => {
    document.getElementById('filter-name').value    = '';
    document.getElementById('filter-country').value = '';
    document.getElementById('filter-status').value  = '';
    allPlants = masterPlants;
    applyFilters(allPlants, sortCol, sortDir);
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
        applyFilters(allPlants, sortCol, sortDir);
    });
});

document.getElementById('filter-status').addEventListener('change', () => {
    const status = document.getElementById('filter-status').value;

    logger.info({ status }); 

    if (!status) {
        allPlants = masterPlants;
    } else {
        allPlants = masterPlants.filter(p => p.status === status);
    }

    applyFilters(allPlants, sortCol, sortDir);
});

document.addEventListener('DOMContentLoaded', async () => {
    clearHeaderState(); 
    try {
        const response = await powerPlantService.getAll();

        logger.info({ response }); 

        masterPlants = response ?? [];
        allPlants = masterPlants;
        applyFilters(allPlants, sortCol, sortDir);
    } catch (error) {
        logger.error(error.message);
        alert("Eroare la încărcarea centralelor"); 
    }
});

document.getElementById('plants-tbody').addEventListener('click', (e) => {
    const row = e.target.closest('tr[data-id]');
    if (!row) return;
    if (e.target.closest('a, button')) return;
    navigateToPlant(row.dataset.id);
});