import { powerPlantService } from '../../services/powerPlantService.js'; 
import { clearHeaderState, } from '../../ui/form-header/formHeaderState.js'; 
import { applyFilters } from '../../ui/power-plants/plantFilters.js'; 
import { PlantStatus } from '../../config/enums.js'; 
import { loadSelect } from '../../ui/selectLoader.js'; 

loadSelect('filter-status', PlantStatus); 

let goTo = "/pages/admin/validate.html"; 

console.log({ goTo });

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

document.getElementById('filter-name').addEventListener('input', () => applyFilters(allPlants, sortCol, sortDir));
document.getElementById('filter-country').addEventListener('input', () => applyFilters(allPlants, sortCol, sortDir));

document.getElementById('btn-reset').addEventListener('click', () => {
    document.getElementById('filter-name').value    = '';
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

document.getElementById('filter-status').addEventListener('change', async () => {
    const status = document.getElementById('filter-status').value;

    console.log({ status }); 

    if (!status) {
        const response = await powerPlantService.getAll();
        allPlants = response.data ?? [];
    } else {
        const response = await powerPlantService.getPlantsByStatus(status);
        allPlants = response.data ?? [];
    }

    applyFilters(allPlants, sortCol, sortDir, goTo);
});

document.addEventListener('DOMContentLoaded', async () => {
    clearHeaderState(); 
    try {
        const response = await powerPlantService.getAll();

        console.log({ response }); 

        allPlants = response.data ?? [];
        applyFilters(allPlants, sortCol, sortDir, goTo);
    } catch (error) {
        console.error(error.message);
        alert("Eroare la încărcarea centralelor"); 
    }
});