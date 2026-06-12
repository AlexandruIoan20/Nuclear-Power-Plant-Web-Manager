import { FRONTEND_BASE } from '../config/api.config.js';

export function redirectIfLocalhost(page) {
    if (window.location.hostname === '127.0.0.1') {
        const pattern = new RegExp(`/${page}\\.html$`);
        window.location.replace(FRONTEND_BASE + window.location.pathname.replace(pattern, `/${page}.html`));
    }
}
