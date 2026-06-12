import { escapeHtml } from '../utils/escapeHtml.js';
import { notificationService } from '../services/notificationService.js';

document.addEventListener("DOMContentLoaded", async () => {
    const container = document.getElementById("notifications-container");
    const loadingIndicator = document.getElementById("loading-indicator");
    const errorMessage = document.getElementById("error-message");

    try {
        const result = await notificationService.getNotifications();

        loadingIndicator.style.display = "none";

        if (result.status === 'success' && Array.isArray(result.data)) {
            if (result.data.length === 0) {
                container.innerHTML = `<p style="color: var(--text-muted); text-align: center;">Nu există nicio notificare înregistrată în sistem.</p>`;
                return;
            }

            result.data.forEach(notification => {
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
                        TIP: ${escapeHtml(notification.type)} | ROL: ${escapeHtml(notification.target_role)}
                    </div>
                `;
                container.appendChild(card);
            });
        }
    } catch (error) {
        loadingIndicator.style.display = "none";

        if (error.status === 401) {
            window.location.href = "login.html";
            return;
        }

        errorMessage.style.display = "block";
        errorMessage.innerHTML = `<strong>Eroare comunicație:</strong> ${escapeHtml(error.message || 'Eroare necunoscută')}`;
        console.error("Eroare fetch notificări:", error);
    }
});
