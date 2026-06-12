const currentPath = window.location.pathname;

const ADMIN_TABS = [
    { href: '/pages/admin/index.html', label: 'Centrale' },
    { href: '/pages/admin/approvals.html', label: 'Aprobări' },
    { href: '/pages/admin/users.html', label: 'Utilizatori' },
];

export function renderAdminNavbar() {
    const el = document.getElementById('admin-navbar');
    if (!el) return;

    const linksHtml = ADMIN_TABS.map(tab => {
        const isActive = currentPath === tab.href;
        const cls = isActive ? ' class="active" aria-current="page"' : '';
        return `<a href="${tab.href}"${cls}>${tab.label}</a>`;
    }).join('');

    el.innerHTML = `
        <div class="topbar" style="margin-bottom:16px;justify-content:center;">
            <nav class="nav-links">
                ${linksHtml}
            </nav>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', renderAdminNavbar);
