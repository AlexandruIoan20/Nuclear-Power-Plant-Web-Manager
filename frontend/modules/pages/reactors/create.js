import { powerPlantService } from '../../services/powerPlantService.js';
import { reactorService } from '../../services/reactorService.js';
import { ReactorType, CoolingType, ReactorOperationalStatus } from '../../config/enums.js';
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js';
import { getQueryParam } from '../../utils/urlHelper.js';

const plantId = getQueryParam("plantId");

function populateSelect(id, options, selected = "") {
    const select = document.getElementById(id);
    select.innerHTML = `<option value="">-- Selectează --</option>` +
        options.map(o =>
            `<option value="${o.value}" ${selected === o.value ? "selected" : ""}>${o.label}</option>`
        ).join('');
}

function disableForm() {
    const form = document.getElementById("reactor-form");
    const inputs = form.querySelectorAll('input, select, textarea, button[type="submit"]');
    inputs.forEach(el => el.disabled = true);
}

document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("reactor-form");
    const statusElement = document.getElementById("status-message");

    if (!plantId) {
        showError(statusElement, "ID centrală lipsă din URL.");
        return;
    }

    let plantStatus = null;
    try {
        const plantData = await powerPlantService.getPlant(plantId);
        plantStatus = plantData.details?.status || null;
    } catch (err) {
        showError(statusElement, "Nu s-a putut verifica statusul centralei.");
        disableForm();
        return;
    }

    if (plantStatus !== 'APPROVED') {
        const labels = { DRAFT: 'în lucru', REVIEW: 'în verificare', REJECTED: 'respinsă' };
        showError(statusElement,
            'Nu se pot crea reactoare pe o centrală ' + (labels[plantStatus] || plantStatus) + '. ' +
            'Statusul centralei trebuie să fie APPROVED.');
        disableForm();
        return;
    }

    populateSelect("reactorType", ReactorType);
    populateSelect("coolingType", CoolingType);
    populateSelect("operationalStatus", ReactorOperationalStatus, "SHUTDOWN");

    const now = new Date();
    const pad = (n) => String(n).padStart(2, "0");
    const minDatetime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    document.getElementById("nextPlannedOutage").setAttribute("min", minDatetime);

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        clearStatus(statusElement);

        const formData = {
            powerPlantId: plantId,
            reactorCode: document.getElementById("reactorCode").value,
            reactorType: document.getElementById("reactorType").value,
            coolingType: document.getElementById("coolingType").value,
            operationalStatus: document.getElementById("operationalStatus").value,
            thermalPowerMw: document.getElementById("thermalPowerMw").value,
            electricalPowerMw: document.getElementById("electricalPowerMw").value,
            fuelCycleDays: document.getElementById("fuelCycleDays").value,
            currentCycleDay: document.getElementById("currentCycleDay").value,
            wearIndex: document.getElementById("wearIndex").value,
            designLifetimeYr: document.getElementById("designLifetimeYr").value,
            commissioningDate: document.getElementById("commissioningDate").value,
            firstCriticality: document.getElementById("firstCriticality").value,
            lastInspectionAt: document.getElementById("lastInspectionAt").value,
            nextPlannedOutage: document.getElementById("nextPlannedOutage").value,
            description: document.getElementById("description").value,
        };

        try {
            await reactorService.createReactor(formData);
            showSuccess(statusElement, "Reactorul a fost creat cu succes!");
            setTimeout(() => {
                window.location.href = `/pages/reactors/list.html?plantId=${plantId}`;
            }, 1000);
        } catch (error) {
            showError(statusElement, "Eroare la crearea reactorului: " + (error.message || ""));
        }
    });
});
