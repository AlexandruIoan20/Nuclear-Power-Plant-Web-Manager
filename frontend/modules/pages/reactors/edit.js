import { reactorService } from '../../services/reactorService.js';
import { ReactorType, CoolingType, ReactorOperationalStatus } from '../../config/enums.js';
import { showError, showSuccess, clearStatus } from '../../ui/showMessage.js';
import { getQueryParam } from '../../utils/urlHelper.js';

const plantId = getQueryParam("plantId");
const reactorId = getQueryParam("reactorId");

function populateSelect(id, options, selected = "") {
    const select = document.getElementById(id);
    select.innerHTML = `<option value="">-- Selectează --</option>` +
        options.map(o =>
            `<option value="${o.value}" ${selected === o.value ? "selected" : ""}>${o.label}</option>`
        ).join('');
}

function toDatetimeLocal(val) {
    if (!val) return "";
    const m = String(val).match(/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})/);
    return m ? `${m[1]}T${m[2]}` : "";
}

document.addEventListener("DOMContentLoaded", async () => {
    const form = document.getElementById("reactor-form");
    const statusElement = document.getElementById("status-message");

    if (!reactorId) {
        showError(statusElement, "ID reactor lipsă din URL.");
        return;
    }

    try {
        const response = await reactorService.getReactor(reactorId);
        const d = response.data;

        document.getElementById("reactorCode").value = d.reactorCode ?? "";

        populateSelect("reactorType", ReactorType, d.reactorType ?? "");
        populateSelect("coolingType", CoolingType, d.coolingType ?? "");
        populateSelect("operationalStatus", ReactorOperationalStatus, d.operationalStatus ?? "");

        document.getElementById("thermalPowerMw").value = d.thermalPowerMw ?? "";
        document.getElementById("electricalPowerMw").value = d.electricalPowerMw ?? "";
        document.getElementById("fuelCycleDays").value = d.fuelCycleDays ?? "";
        document.getElementById("currentCycleDay").value = d.currentCycleDay ?? "";
        document.getElementById("wearIndex").value = d.wearIndex ?? "";
        document.getElementById("designLifetimeYr").value = d.designLifetimeYr ?? "";
        document.getElementById("commissioningDate").value = d.commissioningDate ?? "";
        document.getElementById("firstCriticality").value = d.firstCriticality ?? "";
        document.getElementById("lastInspectionAt").value = toDatetimeLocal(d.lastInspectionAt);
        document.getElementById("nextPlannedOutage").value = toDatetimeLocal(d.nextPlannedOutage);
        document.getElementById("description").value = d.description ?? "";
    } catch (error) {
        showError(statusElement, "Eroare la încărcarea datelor reactorului: " + (error.message || ""));
        return;
    }

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
            await reactorService.updateReactor(reactorId, formData);
            showSuccess(statusElement, "Reactorul a fost actualizat cu succes!");
            setTimeout(() => {
                window.location.href = `/pages/reactors/list.html?plantId=${plantId}`;
            }, 1000);
        } catch (error) {
            showError(statusElement, "Eroare la actualizarea reactorului: " + (error.message || ""));
        }
    });
});
