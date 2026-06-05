function isAdmin() {
    return true;
}

const ADMIN_LINK = { href: '/pages/admin.html', label: 'Admin', icon: '🛡️', key: '/pages/admin.html' };

const currentPage = window.location.pathname;

const NAV_LINKS = [
    { href: '/pages/index.html', label: 'Home', icon: '🏠', key: '/pages/index.html' },
    { href: '/pages/power-plants/list.html', label: 'Centrale', icon: '⚛',  key: '/pages/power-plants/list.html' },
    { href: '/pages/power-plants/create.html', label: 'Creare Centrală', icon: '➕', key: '/pages/power-plants/create.html' },
    { href: '/pages/my-projects.html', label: 'Proiectele Mele', icon: '📁', key: '/pages/my-projects.html' },
];

function buildNavLink(link) {
    const isCurrent = currentPage === link.key;

    if (isCurrent) {
        return `
            <span class="nav-btn nav-btn--current">
                <span class="nav-btn__icon">${link.icon}</span>
                <span class="nav-btn__label">${link.label}</span>
                <span class="nav-btn__badge nav-btn__badge--active">Activ</span>
            </span>
        `;
    }

    return `
        <a href="${link.href}" class="nav-btn nav-btn--empty">
            <span class="nav-btn__icon">${link.icon}</span>
            <span class="nav-btn__label">${link.label}</span>
        </a>
    `;
}

export function renderNavbar() {
    const el = document.getElementById('main-navbar');
    if (!el) return;

    const links = isAdmin() ? [...NAV_LINKS, ADMIN_LINK] : NAV_LINKS;

    el.innerHTML = `
        <nav class="form-nav">
            <div class="form-nav__brand">
                <span>⚛</span>
                <span>Nuclear Plant Control</span>
            </div>
            <button class="form-nav__toggle" id="navbar-toggle" aria-label="Deschide meniul">☰</button>
            <div class="form-nav__links" id="navbar-links">
                ${links.map(buildNavLink).join('')}
            </div>
        </nav>
    `;

    document.getElementById('navbar-toggle').addEventListener('click', () => {
        document.getElementById('navbar-links').classList.toggle('form-nav__links--open');
    });
}

document.addEventListener('DOMContentLoaded', renderNavbar);