import { redirectIfLocalhost } from '../ui/localhostRedirect.js';
import { redirectIfAuthenticated } from '../ui/sessionGuard.js';
import { authService } from '../services/authService.js';

redirectIfLocalhost('login');
redirectIfAuthenticated();

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const message = document.getElementById('login-message');

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submitButton = loginForm.querySelector('input[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        message.textContent = '';

        try {
            const formData = new FormData(loginForm);
            const { ok, payload } = await authService.login(formData);

            if (ok && payload.redirect) {
                window.location.href = payload.redirect;
                return;
            }

            message.style.color = 'var(--danger)';
            message.textContent = payload.message || 'Datele de autentificare sunt incorecte.';
        } catch (error) {
            console.error('Eroare la autentificare:', error);
            message.style.color = 'var(--danger)';
            message.textContent = 'Eroare la conectare. Verificați rețeaua.';
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
});
