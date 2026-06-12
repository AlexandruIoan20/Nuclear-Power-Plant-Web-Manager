import { FRONTEND_BASE } from '../config/api.config.js';
import { api } from '../core/api.js';

export async function redirectIfAuthenticated() {
    try {
        await api.get('/user/status');
        window.location.replace(FRONTEND_BASE + '/pages/map.html');
    } catch (_) {}
}
