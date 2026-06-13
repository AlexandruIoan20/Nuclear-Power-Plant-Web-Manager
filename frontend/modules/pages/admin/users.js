import { api } from '../../core/api.js';

const VALID_ROLES = ['ADMIN', 'ENGINEER', 'OPERATOR'];

function escapeHtml(value) {
    return String(value).replace(/[&<>"]|'/g, (character) => {
        const escapeMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return escapeMap[character] || character;
    });
}

async function loadUsers() {
    const container = document.getElementById('app-container');
    container.innerHTML = '<p class="inline-status">Se încarcă lista utilizatorilor...</p>';

    try {
        const result = await api.get('/admin/users');
        const users = result.data ?? [];

        if (users.length === 0) {
            container.innerHTML = '<p class="empty-state">Nu există utilizatori înregistrați.</p>';
            return;
        }

        renderTable(users);
    } catch (error) {
        container.innerHTML = `<p style="color: var(--danger);">Nu am putut încărca utilizatorii: ${escapeHtml(error.message)}</p>`;
    }
}

function renderTable(users) {
    const container = document.getElementById('app-container');

    const rows = users.map(user => `
        <tr>
            <td>${escapeHtml(user.id)}</td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.firstName)} ${escapeHtml(user.lastName)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.role)}</td>
            <td class="actions-cell">
                <button class="button secondary" data-action="role" data-id="${escapeHtml(user.id)}" data-username="${escapeHtml(user.username)}" data-role="${escapeHtml(user.role)}">Editează Rol</button>
                <button class="button danger" data-action="delete" data-id="${escapeHtml(user.id)}" data-username="${escapeHtml(user.username)}">Șterge</button>
            </td>
        </tr>
    `).join('');

    container.innerHTML = `
        <h1>Utilizatori Înregistrați</h1>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nume</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;

    container.querySelectorAll('[data-action="role"]').forEach(btn => {
        btn.addEventListener('click', () => {
            showRoleModal({
                id: btn.dataset.id,
                username: btn.dataset.username,
                role: btn.dataset.role
            });
        });
    });

    container.querySelectorAll('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', () => {
            handleDeleteUser(btn.dataset.id, btn.dataset.username);
        });
    });
}

function showRoleModal(user) {
    const existing = document.getElementById('role-modal');
    if (existing) existing.remove();

    const options = VALID_ROLES.map(role => `
        <option value="${role}" ${role === user.role ? 'selected' : ''}>${role}</option>
    `).join('');

    const modal = document.createElement('div');
    modal.id = 'role-modal';
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-box">
            <h2>Schimbă Rol — ${escapeHtml(user.username)}</h2>
            <p>Rolul curent: <strong>${escapeHtml(user.role)}</strong></p>
            <div class="field">
                <label for="role-select">Rol nou</label>
                <select id="role-select">${options}</select>
            </div>
            <div class="modal-actions">
                <button class="button secondary" id="role-cancel">Anulează</button>
                <button class="button primary" id="role-save">Salvează</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById('role-cancel').addEventListener('click', () => modal.remove());
    document.getElementById('role-save').addEventListener('click', async () => {
        const newRole = document.getElementById('role-select').value;
        await handleRoleChange(user.id, newRole);
        modal.remove();
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

async function handleRoleChange(userId, newRole) {
    try {
        await api.patch(`/admin/users/${userId}/role`, { role: newRole });
        loadUsers();
    } catch (error) {
        alert(`Eroare: ${error.message}`);
    }
}

async function handleDeleteUser(userId, username) {
    if (!confirm(`Ești sigur că vrei să ștergi utilizatorul "${username}"?`)) return;

    try {
        await api.delete(`/admin/users/${userId}`);
        loadUsers();
    } catch (error) {
        alert(`Eroare: ${error.message}`);
    }
}

document.addEventListener('DOMContentLoaded', loadUsers);
