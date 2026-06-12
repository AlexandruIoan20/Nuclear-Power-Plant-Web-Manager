# Raport de Implementare — Panou Administrare Utilizatori

## 1. Stare Curentă

| Component | Stare |
|-----------|-------|
| `frontend/pages/admin/users.html` | Fișier mutat recent din `pages/`, încă folosește `fetch(API_BASE + '/users')` direct în inline `<script>` — **nu există rută backend** |
| `backend/src/Services/UserService.php` | Conține `getAllUsers(): array` — funcțional |
| `backend/src/Repositories/UserRepository.php` | Conține `findAll(): array` (returnează obiecte `User`) și `findById()` — funcțional |
| `backend/src/Repositories/UserRepository.php` | **Lipsește**: `updateRole()`, `delete()` |
| `backend/src/Controllers/UserController.php` | Conține doar: `handleRegister`, `handleLogin`, `handleLogout`, `getUserStatus` — **lipsește** orice metodă de administrare |
| `backend/public/index.php` | **Nu există rută** pentru `/api/users` sau pentru operații de administrare utilizatori |
| `backend/src/Entities/User.php` | Conține `setRole()` — util, dar nu și metodă de serializare completă |

---

## 2. Arhitectura Propusă

### 2.1 Backend — Endpoint-uri API

```
GET    /api/admin/users              -> listează toți utilizatorii (doar ADMIN)
GET    /api/admin/users/{id}         -> detalii utilizator (doar ADMIN)
PATCH  /api/admin/users/{id}/role    -> schimbă rolul (doar ADMIN)
DELETE /api/admin/users/{id}         -> șterge utilizator (doar ADMIN)
```

Toate rutele sunt protejate cu `auth('ADMIN', ...)`.

### 2.2 Frontend — Pagini și Module

```
frontend/pages/admin/users.html          -> interfața admin utilizatori
frontend/modules/pages/admin/users.js    -> logică JS (încărcare, render, acțiuni)
```

`adminNavbar.js` are deja tab-ul "Utilizatori" → `pages/admin/users.html`.

---

## 3. Implementare Backend

### 3.1 Repository — metode lipsă

În `UserRepository.php` se adaugă:

| Metodă | SQL | Descriere |
|--------|-----|-----------|
| `findAllWithDetails(): array` | `SELECT id, username, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC` | Listă completă (fără password_hash) |
| `updateRole(string $id, string $role): void` | `UPDATE users SET role = :role WHERE id = :id` | Schimbare rol |
| `delete(string $id): void` | `DELETE FROM users WHERE id = :id` | Ștergere utilizator |
| `countByRole(string $role): int` | `SELECT COUNT(*) FROM users WHERE role = :role` | Validare — nu șterge ultimul ADMIN |

### 3.2 Service — metode noi

În `UserService.php` se adaugă:

| Metodă | Descriere |
|--------|-----------|
| `getAllUsers(): array` | Există deja — returnează `User[]`, trebuie adaptat să returneze array asociativ curat |
| `getUserById(string $id): ?array` | Există deja |
| `updateUserRole(string $id, string $role): void` | Validatează rolul (împotriva enum `user_roles`), previne ștergerea ultimului ADMIN, deleagă repository |
| `deleteUser(string $id): void` | Validatează că utilizatorul există și nu e ultimul ADMIN, șterge |
| `getAllUsersFormatted(): array` | Returnează array de array-uri curate (fără password_hash) pentru JSON |

### 3.3 Controller — metode noi

În `UserController.php` se adaugă:

| Metodă | Rută | Comportament |
|--------|------|-------------|
| `adminListUsers(): void` | `GET /api/admin/users` | JSON cu toți utilizatorii (id, username, email, first_name, last_name, role, created_at) |
| `adminGetUser(string $id): void` | `GET /api/admin/users/{id}` | JSON cu un utilizator |
| `adminUpdateRole(string $id): void` | `PATCH /api/admin/users/{id}/role` | Citește `{"role": "ENGINEER"}` din body, validează, actualizează |
| `adminDeleteUser(string $id): void` | `DELETE /api/admin/users/{id}` | Șterge utilizatorul după validări |

Toate metodele pornesc cu `header('Content-Type: application/json; charset=UTF-8')` și returnează `{'status': 'success', 'data': ...}` sau `{'status': 'error', 'message': ...}`.

### 3.4 Rute — de adăugat în `index.php`

```php
// După celelalte rute admin
$router->get('/api/admin/users', auth('ADMIN', function() use ($userService) {
    (new UserController($userService))->adminListUsers();
}));

$router->get('/api/admin/users/{id}', auth('ADMIN', function($id) use ($userService) {
    (new UserController($userService))->adminGetUser($id);
}));

$router->patch('/api/admin/users/{id}/role', auth('ADMIN', function($id) use ($userService) {
    (new UserController($userService))->adminUpdateRole($id);
}));

$router->delete('/api/admin/users/{id}', auth('ADMIN', function($id) use ($userService) {
    (new UserController($userService))->adminDeleteUser($id);
}));
```

### 3.5 Validări și Securitate

1. **Protecție ADMIN**: rutele folosesc `auth('ADMIN', ...)` → `AuthHelper::requireRole('ADMIN')`
2. **Protecție CSRF**: toate request-urile care modifică date (`PATCH`, `DELETE`) trec prin verificarea CSRF token din `index.php`
3. **Ultimul ADMIN**: nu se permite schimbarea rolului sau ștergerea ultimului utilizator cu rol ADMIN
4. **Auto-protecție**: un admin nu își poate schimba propriul rol sau șterge propriul cont prin acest API (se verifică `$_SESSION['user_id']` vs `$id`)
5. **Validare rol**: doar valorile din enum-ul `user_roles` (`ADMIN`, `ENGINEER`, `OPERATOR`) sînt acceptate

---

## 4. Implementare Frontend

### 4.1 Fișierul `frontend/modules/pages/admin/users.js`

Se creează un modul JS dedicat, similar cu `admin/index.js`:

```js
// Responsabilități:
// 1. Fetch GET /api/admin/users la încărcare
// 2. Construire dinamică a tabelului cu coloane: ID, Username, Nume, Email, Rol, Acțiuni
// 3. Butoane "Schimbă Rol" -> modal/select inline + PATCH /api/admin/users/{id}/role
// 4. Butoane "Șterge" -> confirmare + DELETE /api/admin/users/{id}
// 5. Gestionare stare: încărcare, succes, gol, eroare
```

**Funcții principale:**

| Funcție | Descriere |
|---------|-----------|
| `loadUsers()` | Fetch utilizatori, apelează `renderTable()` |
| `renderTable(users)` | Construiește HTML tabel cu rînduri și butoane de acțiune |
| `handleRoleChange(userId, newRole)` | PATCH către API, reîncarcă tabelul |
| `handleDeleteUser(userId)` | Confirmare + DELETE către API, reîncarcă tabelul |
| `showRoleModal(user)` | Modal cu select pentru roluri disponibile |
| `escapeHtml(value)` | Prevenire XSS (existent deja în users.html) |

### 4.2 Actualizare `frontend/pages/admin/users.html`

Înlocuirea inline `<script>` cu:

```html
<div id="main-navbar"></div>
<div id="admin-navbar"></div>
<div id="app-container" class="panel">
    <!-- Loader/eroare/tabel generate dinamic de users.js -->
</div>

<script type="module" src="../../modules/ui/navbar.js"></script>
<script type="module" src="../../modules/ui/adminNavbar.js"></script>
<script type="module" src="../../modules/pages/admin/users.js"></script>
```

### 4.3 Design Interfață

```
┌─────────────────────────────────────────────────────┐
│  [Admin Navbar: Centrale | Aprobări | ... | Utilizatori] │
├─────────────────────────────────────────────────────┤
│  Registered Users                                    │
│  ┌──────────────────────────────────────────────┐   │
│  │ ID │ Username │ Nume    │ Email    │ Rol    │   │
│  │────│──────────│─────────│──────────│────────│   │
│  │ 1  │ admin    │ Admin.. │ a@nuc.ro│ ADMIN  │   │
│  │    │          │         │          │ [--]   │   │
│  │ 2  │ op1      │ Op..    │ o@nuc.ro│ OPERAT.│   │
│  │    │          │         │          │ [Edit]🗑│   │
│  └──────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

Coloana "Acțiuni" conține:
- Dropdown/button "Schimbă Rol" → deschide modal cu select rol + buton Salvare
- Buton "Șterge" → dialog de confirmare

### 4.4 Flux Operații

**Schimbare Rol:**
1. Admin face click "Editează Rol"
2. Modal cu select precompletat cu rolul curent
3. Admin selectează noul rol, apasă "Salvează"
4. Frontend: `PATCH /api/admin/users/{id}/role` cu body `{"role": "ENGINEER"}`
5. Backend: validează, actualizează, returnează succes
6. Frontend: reîncarcă lista de utilizatori

**Ștergere Utilizator:**
1. Admin face click "Șterge"
2. Modal de confirmare: "Ești sigur că vrei să ștergi utilizatorul X?"
3. Admin confirmă
4. Frontend: `DELETE /api/admin/users/{id}`
5. Backend: validează (nu ultimul ADMIN, nu auto-ștergere), șterge
6. Frontend: reîncarcă lista

---

## 5. Riscuri și Considerații

| Risc | Mitigare |
|------|----------|
| Ștergerea accidentală a utilizatorilor | Confirmare în 2 pași; prevenire auto-ștergere |
| Pierderea ultimului ADMIN | Validare backend: `countByRole('ADMIN') > 1` înainte de update/delete |
| Roluri invalide | Validare împotriva enum-ului `user_roles` |
| XSS prin username/email | `escapeHtml()` la render |
| CSRF | Token-ul CSRF existent în `api.js` |

---

## 6. Ordine Implementare

1. **Backend — Repository**: adaugă `findAllWithDetails()`, `updateRole()`, `delete()`, `countByRole()`
2. **Backend — Service**: adaugă `updateUserRole()`, `deleteUser()`, `getAllUsersFormatted()`
3. **Backend — Controller**: adaugă `adminListUsers()`, `adminGetUser()`, `adminUpdateRole()`, `adminDeleteUser()`
4. **Backend — Routes**: înregistrează cele 4 rute în `index.php`
5. **Frontend — JS Module**: creează `modules/pages/admin/users.js`
6. **Frontend — HTML**: actualizează `pages/admin/users.html` să folosească modulul JS
7. **Testare**: verifică fiecare operație (listare, schimbare rol, ștergere)
