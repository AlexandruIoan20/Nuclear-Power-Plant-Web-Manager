import { escapeHtml } from '../utils/escapeHtml.js';
import { notificationService } from '../services/notificationService.js';
import { getCsrfToken } from '../core/api.js';
import { API_BASE } from '../config/api.config.js';

function buildAlertCards(container, notifications) {
    if (notifications.length === 0) {
        container.innerHTML = `<p style="color: var(--text-muted); text-align: center;">Nu există alerte de senzori.</p>`;
        return;
    }

    const toolbar = document.createElement("div");
    toolbar.style.cssText = "display: flex; justify-content: flex-end; margin-bottom: 16px;";
    const markBtn = document.createElement("button");
    markBtn.className = "button secondary small";
    markBtn.textContent = "Mark All as Read";
    markBtn.addEventListener("click", markAllAsRead);
    toolbar.appendChild(markBtn);
    container.appendChild(toolbar);

    notifications.forEach(notification => {
        const card = document.createElement("div");
        const severityClass = notification.severity.toLowerCase();
        card.className = `notification-card ${severityClass}`;

        card.innerHTML = `
            <div class="notification-header">
                <span class="notification-title">${escapeHtml(notification.title)}</span>
                <span class="notification-meta">${escapeHtml(notification.date)}</span>
            </div>
            <div class="notification-body">
                ${escapeHtml(notification.message)}
            </div>
            <div class="notification-meta" style="margin-top: 8px;">
                TIP: ${escapeHtml(notification.type)} | ROL: ${escapeHtml(notification.targetRole)}
            </div>
        `;
        container.appendChild(card);
    });
}

function buildPlantCards(container, notifications) {
    if (notifications.length === 0) {
        container.innerHTML = `<p style="color: var(--text-muted); text-align: center;">Nu există notificări de centrale.</p>`;
        return;
    }

    const toolbar = document.createElement("div");
    toolbar.style.cssText = "display: flex; justify-content: flex-end; margin-bottom: 16px;";
    const markBtn = document.createElement("button");
    markBtn.className = "button secondary small";
    markBtn.textContent = "Mark All as Read";
    markBtn.addEventListener("click", markAllPlantAsRead);
    toolbar.appendChild(markBtn);
    container.appendChild(toolbar);

    notifications.forEach(notification => {
        const card = document.createElement("div");
        const severityClass = notification.severity.toLowerCase();
        card.className = `notification-card ${severityClass}`;

        card.innerHTML = `
            <div class="notification-header">
                <span class="notification-title">${escapeHtml(notification.title)}</span>
                <span class="notification-meta">${escapeHtml(notification.date)}</span>
            </div>
            <div class="notification-body">
                ${escapeHtml(notification.message)}
            </div>
            <div class="notification-meta" style="margin-top: 8px;">
                TIP: ${escapeHtml(notification.type)} | ROL: ${escapeHtml(notification.targetRole)}
            </div>
            <button class="button secondary small dismiss-btn" style="align-self: flex-end; margin-top: 8px;">Dismiss</button>
        `;

        const dismissBtn = card.querySelector('.dismiss-btn');
        dismissBtn.addEventListener('click', async () => {
            const rawId = notification.id.replace(/^(plant_|approval_)/, '');
            const prefix = notification.id.startsWith('plant_') ? 'plant_' : 'approval_';
            const requestId = prefix === 'plant_' ? rawId : notification.id;
            
            try {
                // Ideal ar fi ca acest apel să fie mutat în notificationService pe viitor
                const resp = await fetch(`${API_BASE}/alerts/${requestId}/read`, {
                    method: "PUT",
                    headers: { "X-CSRF-Token": await getCsrfToken() },
                    credentials: "include"
                });
                
                if (!resp.ok) throw new Error("Eroare la dismiss");
                
                card.remove();
                const remaining = container.querySelectorAll('.notification-card');
                if (remaining.length === 0) {
                    container.innerHTML = `<p style="color: var(--text-muted); text-align: center;">Nu există notificări de centrale.</p>`;
                }
            } catch (e) {
                alert("Eroare: " + e.message);
            }
        });

        container.appendChild(card);
    });
}

async function loadCategory(category) {
    const container = document.getElementById(`tab-${category}`);
    const loadingIndicator = document.getElementById("loading-indicator");
    const errorMessage = document.getElementById("error-message");

    container.innerHTML = "";
    loadingIndicator.style.display = "block";
    errorMessage.style.display = "none";

    try {
        const result = await notificationService.getNotifications(category);

        loadingIndicator.style.display = "none";

        if (Array.isArray(result)) {
            if (category === 'alert') {
                buildAlertCards(container, result);
            } else {
                buildPlantCards(container, result);
            }
        }
    } catch (error) {
        loadingIndicator.style.display = "none";
        
        if (error.status === 401 || error.message?.includes("401")) {
            window.location.href = "login.html";
            return;
        }

        errorMessage.style.display = "block";
        errorMessage.innerHTML = `<strong>Eroare comunicație:</strong> ${escapeHtml(error.message || 'Eroare necunoscută')}`;
        console.error("Eroare fetch notificări:", error);
    }
}

async function markAllAsRead() {
    const btn = document.querySelector("#tab-alert .button.secondary.small");
    if (btn) { btn.disabled = true; btn.textContent = "Se marchează..."; }
    
    try {
        const response = await fetch(`${API_BASE}/alerts/all/read`, {
            method: "PUT",
            headers: { "X-CSRF-Token": await getCsrfToken() },
            credentials: "include"
        });
        
        if (!response.ok) {
            const result = await response.json();
            throw new Error(result.message || `Eroare ${response.status}`);
        }
        
        await loadCategory("alert");
    } catch (error) {
        alert("Eroare: " + error.message);
    }
}

async function markAllPlantAsRead() {
    const btn = document.querySelector("#tab-plant .button.secondary.small");
    if (btn) { btn.disabled = true; btn.textContent = "Se marchează..."; }
    
    try {
        const response = await fetch(`${API_BASE}/alerts/plant-all/read`, {
            method: "PUT",
            headers: { "X-CSRF-Token": await getCsrfToken() },
            credentials: "include"
        });
        
        if (!response.ok) {
            const result = await response.json();
            throw new Error(result.message || `Eroare ${response.status}`);
        }
        
        await loadCategory("plant");
    } catch (error) {
        alert("Eroare: " + error.message);
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const tabs = document.querySelectorAll(".tab");
    
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            document.querySelectorAll(".tab-content").forEach(tc => tc.classList.remove("active"));
            const target = document.getElementById(`tab-${tab.dataset.tab}`);
            if (target) target.classList.add("active");

            loadCategory(tab.dataset.tab);
        });
    });

    await loadCategory("alert");
});