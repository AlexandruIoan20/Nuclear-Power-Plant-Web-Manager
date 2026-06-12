import { escapeHtml } from '../../utils/escapeHtml.js';
import { adminApprovalService } from '../../services/adminApprovalService.js';

document.addEventListener("DOMContentLoaded", async () => {
    const tableBody = document.getElementById("approvals-table-body");
    const statusMessage = document.getElementById("status-message");
    const statPending = document.getElementById("stat-pending");

    try {
        const result = await adminApprovalService.getPendingApprovals();
        const plants = result.data ?? [];
        tableBody.innerHTML = "";

        statPending.textContent = plants.length < 10 ? `0${plants.length}` : plants.length;

        if (plants.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" style="padding: 24px; text-align: center; color: var(--text-muted);">Nu există centrale care așteaptă aprobare.</td></tr>`;
            return;
        }

        plants.forEach(plant => {
            const tr = document.createElement("tr");
            tr.style.borderBottom = "1px solid var(--border)";

            tr.innerHTML = `
                <td style="padding: 12px;"><strong>${escapeHtml(plant.name)}</strong></td>
                <td style="padding: 12px;">${escapeHtml(plant.country)}</td>
                <td style="padding: 12px; font-family: monospace;">${escapeHtml(plant.latitude)}, ${escapeHtml(plant.longitude)}</td>
                <td style="padding: 12px;"><span class="badge">${escapeHtml(plant.status)}</span></td>
                <td style="padding: 12px; text-align: right; white-space: nowrap;">
                    <button class="action-approve" data-id="${escapeHtml(plant.id)}" style="width:auto;display:inline-flex;padding:6px 12px;font-size:0.78rem;margin-right:6px;border:1px solid rgba(57,255,20,0.7);background:linear-gradient(180deg,rgba(57,255,20,0.12),rgba(57,255,20,0.04));color:var(--green);cursor:pointer;text-transform:uppercase;letter-spacing:0.08em;font-family:inherit;">Aprobă</button>
                    <button class="action-reject" data-id="${escapeHtml(plant.id)}" style="width:auto;display:inline-flex;padding:6px 12px;font-size:0.78rem;border:1px solid rgba(255,77,77,0.7);background:linear-gradient(180deg,rgba(255,77,77,0.12),rgba(255,77,77,0.04));color:#ff8787;cursor:pointer;text-transform:uppercase;letter-spacing:0.08em;font-family:inherit;">Respinge</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        document.querySelectorAll(".action-approve").forEach(btn => {
            btn.addEventListener("click", () => {
                executeStatusChange(btn.getAttribute("data-id"), "APPROVED");
            });
        });

        document.querySelectorAll(".action-reject").forEach(btn => {
            btn.addEventListener("click", () => {
                executeStatusChange(btn.getAttribute("data-id"), "REJECTED");
            });
        });

    } catch (error) {
        tableBody.innerHTML = `<tr><td colspan="5" style="padding: 20px; text-align: center; color: var(--danger);">Eroare la încărcarea datelor.</td></tr>`;
    }

    async function executeStatusChange(id, targetStatus) {
        statusMessage.innerHTML = "";
        try {
            const result = await adminApprovalService.updateStatus(id, targetStatus);

            if (result.status === "success") {
                statusMessage.innerHTML = `<span class="inline-status" style="color: var(--success);">✔ ${escapeHtml(result.message)}</span>`;
                window.location.reload();
            } else {
                statusMessage.innerHTML = `<span style="color: var(--danger);">❌ ${escapeHtml(result.message || 'Eroare necunoscuta')}</span>`;
            }
        } catch (error) {
            statusMessage.innerHTML = `<span style="color: var(--danger);">❌ ${escapeHtml(error.message || 'Eroare de rețea.')}</span>`;
        }
    }
});
