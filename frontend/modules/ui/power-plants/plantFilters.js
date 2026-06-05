import { renderTable } from './plantTable.js'; 

export function applyFilters(allPlants, sortCol, sortDir) {
    const name = document.getElementById('filter-name').value.trim().toLowerCase();
    const country = document.getElementById('filter-country').value.trim().toLowerCase();

    let filtered = allPlants.filter(p => {
        const matchName = !name || (p.name ?? '').toLowerCase().includes(name);
        const matchCountry = !country || (p.country ?? '').toLowerCase().includes(country);
        return matchName && matchCountry;
    });

    filtered = sortPlants(filtered, sortCol, sortDir);
    renderTable(filtered);
}

export function sortPlants(list, sortCol, sortDir) {
    return [...list].sort((a, b) => {
        let av = a[sortCol] ?? '';
        let bv = b[sortCol] ?? '';
        if (typeof av === 'string') av = av.toLowerCase();
        if (typeof bv === 'string') bv = bv.toLowerCase();
        if (av < bv) return sortDir === 'asc' ? -1 : 1;
        if (av > bv) return sortDir === 'asc' ?  1 : -1;
        return 0;
    });
}
