import { powerPlantService } from '../../services/powerPlantService.js'; 
import { clearHeaderState, } from '../../ui/form-header/formHeaderState.js'; 
import { applyFilters } from '../../ui/power-plants/plantFilters.js'; 
 
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

document.addEventListener('DOMContentLoaded', async () => {
    clearHeaderState(); 
    try {
        const response = await powerPlantService.getAll();

        console.log({ response }); 

        allPlants = response.data ?? [];
        applyFilters(allPlants, sortCol, sortDir);
    } catch (error) {
        console.error(error.message);
        alert("Eroare la încărcarea centralelor"); 
    }
});