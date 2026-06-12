const currentPath = window.location.pathname;

const UNAUTH_LINKS = [
    { href: '/pages/start.html', label: 'Home' },
    { href: '/pages/login.html', label: 'Login' },
    { href: '/pages/register.html', label: 'Înregistrare' },
];

const USER_LINKS = [
    { href: '/pages/dashboard.html', label: 'Dashboard' },
    { href: '/pages/power-plants/list.html', label: 'Centrale' },
    { href: '/pages/power-plants/create.html', label: 'Creare Centrală' },
    { href: '/pages/statistics.html', label: 'Statistici' },
    { href: '/pages/map.html', label: 'Hartă' },
    { href: '/pages/notifications.html', label: 'Notificări' },
];

const ADMIN_LINKS = [
    { href: '/pages/admin/index.html', label: 'Admin' },
    { href: '/pages/approvals.html', label: 'Aprobări' },
    { href: '/pages/users.html', label: 'Utilizatori' },
    { href: '/pages/logs.html', label: 'Logs' },
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
            <span style="color:var(--muted);margin:0 4px;">|</span>
            <span style="color:var(--green);text-transform:uppercase;letter-spacing:0.06em;font-size:0.82rem;">${user.username}</span>
            <a href="${BACKEND_BASE}/logout" class="button secondary" style="width:auto;padding:6px 12px;font-size:0.78rem;">Logout</a>
        `;
    }

    el.innerHTML = `
        <header class="topbar">
            <div class="brand">
                <strong>Nuclear Plant Control</strong>
            </div>
            <nav class="nav-links" aria-label="Navigare principală">
                ${linksHtml}
                <a href="${BACKEND_BASE}/api/rss/power-plants" target="_blank" title="Flux RSS centrale nucleare" style="display:inline-flex;align-items:center;gap:4px;color:var(--orange,#f60);text-decoration:none;font-size:0.82rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="6" cy="18" r="3"/><path d="M4 11a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6v-3zm0-7a16 16 0 0 1 16 16h-3a13 13 0 0 0-13-13v-3z"/></svg>
                    RSS
                </a>
                ${userHtml}
            </nav>
        </header>
    `;
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
            const isAdmin = user.role === 'admin';
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
