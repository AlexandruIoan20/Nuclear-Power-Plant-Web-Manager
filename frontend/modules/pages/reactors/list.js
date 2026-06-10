import { reactorService } from '../../services/reactorService.js';
import { getQueryParam } from '../../utils/urlHelper.js';
import { renderReactorTable } from '../../ui/reactors/reactorTable.js';
import { logger } from '../../core/logger.js';

const plantId = getQueryParam("plantId");

let reactors = [];

function renderTable() {
    renderReactorTable(reactors, plantId);
}

function handleDelete(id) {
    if (!confirm("Sigur doriți să ștergeți acest reactor?")) return;

    reactorService.deleteReactor(id).then(() => {
        logger.info(`Reactorul ${id} a fost sters`);
        reactors = reactors.filter(r => r.id !== id);
        renderTable();
    }).catch(error => {
        logger.error(`Eroare la stergerea reactorului ${id}`, { message: error.message });
        alert("Eroare la ștergerea reactorului: " + (error.message || ""));
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!plantId) {
        document.getElementById('reactors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="8">ID centrală lipsă din URL.</td></tr>`;
        return;
    }

    logger.info(`Se incarca lista reactoarelor pentru centrala ${plantId}`);

    document.getElementById('plant-id-display').textContent = plantId;

    document.getElementById('btn-create-reactor').addEventListener('click', () => {
        window.location.href = `/pages/reactors/create.html?plantId=${plantId}`;
    });

    document.getElementById('btn-back-plant').addEventListener('click', () => {
        window.location.href = `/pages/power-plants/finish.html?id=${plantId}`;
    });

    try {
        const response = await reactorService.getReactorsByPlant(plantId);
        reactors = response.data ?? [];
        logger.info(`Incarcate ${reactors.length} reactoare pentru centrala ${plantId}`);
        renderTable();
    } catch (error) {
        logger.error(`Eroare la incarcarea reactoarelor pentru centrala ${plantId}`, { message: error.message });
        document.getElementById('reactors-tbody').innerHTML =
            `<tr class="state-row"><td colspan="8">Eroare la încărcarea reactoarelor.</td></tr>`;
    }

    document.getElementById('reactors-tbody').addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-id]');
        if (!row) return;
        if (e.target.closest('.btn-delete-reactor')) {
            handleDelete(row.dataset.id);
            return;
        }
        if (e.target.closest('.btn-edit-reactor')) {
            window.location.href = `/pages/reactors/edit.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
            return;
        }
        if (e.target.closest('a, button')) return;
        window.location.href = `/pages/reactors/edit.html?reactorId=${row.dataset.id}&plantId=${plantId}`;
    });
});
