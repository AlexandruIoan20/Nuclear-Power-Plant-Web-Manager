import { BACKEND_BASE } from '../config/api.config.js';
import { getCsrfToken } from '../core/api.js';

export const authService = {
    login: async (formData) => {
        const urlEncodedData = new URLSearchParams(formData);
        const response = await fetch(BACKEND_BASE + '/login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'fetch',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': await getCsrfToken()
            },
            body: urlEncodedData,
            credentials: 'include',
        });
        const payload = await response.json();
        return { ok: response.ok, payload };
    },

    register: async (form) => {
        const response = await fetch(BACKEND_BASE + '/register', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'fetch',
                'X-CSRF-Token': await getCsrfToken()
            },
            body: new FormData(form),
            credentials: 'include',
        });
        const payload = await response.json();
        return { ok: response.ok, payload };
    }
};
