import { powerPlantService } from '../../services/powerPlantService.js';
import { logger } from '../../core/logger.js';

let parsedData = null;
let isMulti = false;

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('file-input');
    const previewArea = document.getElementById('preview-area');
    const previewList = document.getElementById('plant-preview-list');
    const confirmBtn = document.getElementById('btn-confirm-import');
    const resultArea = document.getElementById('result-area');
    const resultMessage = document.getElementById('result-message');
    const resultLinks = document.getElementById('result-links');
    const errorArea = document.getElementById('error-area');
    const importStatus = document.getElementById('import-status');

    document.getElementById('btn-import-another').addEventListener('click', () => {
        fileInput.value = '';
        previewArea.style.display = 'none';
        resultArea.style.display = 'none';
        errorArea.style.display = 'none';
        parsedData = null;
    });

    fileInput.addEventListener('change', (e) => {
        errorArea.style.display = 'none';
        resultArea.style.display = 'none';
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            try {
                const json = JSON.parse(event.target.result);
                parsedData = json;
                isMulti = false;

                let plants = [];
                if (json.plants && Array.isArray(json.plants)) {
                    plants = json.plants;
                    isMulti = true;
                } else if (Array.isArray(json)) {
                    plants = json;
                    isMulti = true;
                } else if (json.name) {
                    plants = [json];
                } else {
                    throw new Error('Invalid format: expected an object with "plants" array, an array of plants, or a single plant object');
                }

                if (plants.length === 0) {
                    throw new Error('No plants found in the file');
                }

                previewList.innerHTML = plants.map((p, i) => {
                    const reactorCount = (p.reactors || []).length;
                    return `<li><strong>${p.name || 'Unnamed'}</strong> — ${reactorCount} reactor(s)${p.status ? ', status: ' + p.status : ''}</li>`;
                }).join('');

                previewArea.style.display = 'block';
                confirmBtn.disabled = false;
                importStatus.textContent = '';
            } catch (err) {
                errorArea.textContent = 'Error parsing file: ' + err.message;
                errorArea.style.display = 'block';
                previewArea.style.display = 'none';
                parsedData = null;
            }
        };
        reader.readAsText(file);
    });

    confirmBtn.addEventListener('click', async () => {
        if (!parsedData) return;

        confirmBtn.disabled = true;
        importStatus.textContent = 'Importing...';

        try {
            let response;
            if (isMulti) {
                const plantsArray = parsedData.plants && Array.isArray(parsedData.plants)
                    ? parsedData.plants
                    : Array.isArray(parsedData) ? parsedData : [parsedData];
                response = await powerPlantService.importPlants({ plants: plantsArray });
            } else {
                response = await powerPlantService.importPlant(parsedData);
            }

            resultArea.style.display = 'block';
            previewArea.style.display = 'none';

            if (response.status === 'success') {
                const body = response.data ?? response;
                resultMessage.textContent = body.message || 'Import successful!';
                resultMessage.style.color = 'var(--success)';

                const ids = body.plant_ids || (body.plant_id ? [body.plant_id] : []);
                if (ids.length > 0) {
                    resultLinks.innerHTML = '<strong>Created plants:</strong><ul>' +
                        ids.map(id => `<li><a href="/pages/power-plants/finish.html?id=${id}">View plant → ${id.slice(0, 8)}...</a></li>`).join('') +
                        '</ul>';
                }
            } else {
                resultMessage.textContent = response.message || 'Import failed';
                resultMessage.style.color = 'var(--danger)';
            }
        } catch (err) {
            errorArea.textContent = 'Import error: ' + (err.message || 'Unknown error');
            errorArea.style.display = 'block';
            confirmBtn.disabled = false;
            importStatus.textContent = '';
        }
    });
});
