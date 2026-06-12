import { redirectIfLocalhost } from '../ui/localhostRedirect.js';
import { redirectIfAuthenticated } from '../ui/sessionGuard.js';
import { authService } from '../services/authService.js';

redirectIfLocalhost('register');
redirectIfAuthenticated();

document.getElementById('registerForm').addEventListener('submit', async (event) => {
    event.preventDefault();

    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const message = document.getElementById('register-message');

    if (password !== passwordConfirm) {
        message.textContent = 'Parolele nu se potrivesc.';
        return;
    }

    try {
        const { ok, payload } = await authService.register(event.target);

        if (ok && payload.redirect) {
            window.location.href = payload.redirect;
            return;
        }

        message.textContent = payload.message || 'Contul nu a putut fi creat.';
    } catch (error) {
        console.error('Eroare la înregistrare:', error);
        message.textContent = 'Eroare la conectare.';
    }
});
