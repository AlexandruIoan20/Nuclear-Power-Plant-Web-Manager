import { API_BASE, BACKEND_BASE } from '../config/api.config.js';

const currentPath = window.location.pathname;

const UNAUTH_LINKS = [
    { href: '/pages/index.html', label: 'Acasă' },
    { href: '/pages/login.html', label: 'Autentificare' },
    { href: '/pages/register.html', label: 'Înregistrare' },
];

const USER_LINKS = [
    { href: '/pages/power-plants/list.html', label: 'Centrale' },
    { href: '/pages/power-plants/my-plants.html', label: 'Centralele Mele' },
    { href: '/pages/power-plants/create.html', label: 'Creare Centrală' },
    { href: '/pages/map.html', label: 'Hartă' },
    { href: '/pages/notifications.html', label: 'Notificări' },
];

const ADMIN_LINKS = [
    { href: '/pages/admin/index.html', label: 'Admin' },
];

function buildLinkHtml(links) {
    return links.map(link => {
        const isActive = currentPath === link.href;
        const cls = isActive ? ' class="active" aria-current="page"' : '';
        return `<a href="${link.href}"${cls}>${link.label}</a>`;
    }).join('');
}

function renderTopbar(el, links, user) {
    const linksHtml = buildLinkHtml(links);

    let userHtml = '';
    if (user) {
        userHtml = `
            <a href="${BACKEND_BASE}/logout" class="button secondary" style="width:auto;padding:6px 12px;font-size:0.78rem;">Deconectare</a>
        `;
    }

    el.innerHTML = `
        <header class="topbar">
            <div class="brand">
                <strong>Panou Control</strong>
            </div>
            <button class="navbar__toggle" id="navbar-toggle" aria-label="Deschide meniul">Meniu</button>
            <nav class="nav-links" id="navbar-links" aria-label="Navigare principală">
                ${linksHtml}
                ${userHtml}
            </nav>
        </header>
    `;

    const toggle = document.getElementById('navbar-toggle');
    const navLinks = document.getElementById('navbar-links');
    if (toggle && navLinks) {
        toggle.addEventListener('click', () => {
            navLinks.classList.toggle('nav-links--open');
        });
    }
}

export async function renderNavbar() {
    const el = document.getElementById('main-navbar');
    if (!el) return;

    try {
        const response = await fetch(API_BASE + '/user/status', { credentials: 'include' });

        if (response.status === 401 || !response.ok) {
            renderTopbar(el, UNAUTH_LINKS);
            return;
        }

        const result = await response.json();
        if (result.status === 'success' && result.data) {
            const user = result.data;
            const isAdmin = user.role.toUpperCase() === 'ADMIN';
            const links = isAdmin ? [...USER_LINKS, ...ADMIN_LINKS] : USER_LINKS;
            renderTopbar(el, links, user);
        } else {
            renderTopbar(el, UNAUTH_LINKS);
        }
    } catch {
        renderTopbar(el, UNAUTH_LINKS);
    }
}

document.addEventListener('DOMContentLoaded', renderNavbar);
