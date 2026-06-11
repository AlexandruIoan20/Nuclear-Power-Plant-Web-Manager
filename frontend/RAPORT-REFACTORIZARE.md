# Raport de Refactorizare Frontend

## 1. Ierarhia de Pagini Propusă

```
PUBLIC (fără autentificare)
├── /pages/index.html               [Landing public]
├── /pages/start.html               [Landing cu session check]
├── /pages/login.html               [Autentificare]
├── /pages/register.html            [Înregistrare]

DASHBOARD & OVERVIEW (autentificare necesară)
├── /pages/dashboard.html           [Hub principal - centrul aplicației]
├── /pages/map.html                 [Hartă interactivă cu toate centralele]
├── /pages/notifications.html       [Centru de notificări]

MANAGEMENT CENTRALE NUCLEARE
├── /pages/power-plants/list.html   [Lista centralelor cu filtre]
├── Wizard Creare/Editare Centrală (5 pași, query param: id)
│   ├── (1) /pages/power-plants/create.html       [Denumire centrală]
│   ├── (2) /pages/power-plants/basics.html       [Capacitate, durată, descriere]
│   ├── (3) /pages/power-plants/geological.html   [Sol, apă, coordonate pe hartă]
│   ├── (4) /pages/power-plants/technical.html    [Reactoare, eficiență, risc]
│   └── (5) /pages/power-plants/finish.html       [Sumar + Verificare]
├── /pages/feasibility/report-results.html        [Raport de fezabilitate]

MANAGEMENT REACTOARE (subordonat unei centrale, query param: plantId)
├── /pages/reactors/list.html       [Lista reactoarelor unei centrale]
├── /pages/reactors/create.html     [Creare reactor nou]
├── /pages/reactors/edit.html       [Editare reactor]
└── /pages/reactors/detail.html     [Monitorizare live (SSE senzori)]

ADMINISTRARE
├── /pages/admin/index.html         [Lista centralelor cu status (admin)]
├── /pages/admin/validate.html      [Aprobare/Respingere centrală]
├── /pages/approvals.html           [Coadă de aprobări]
└── /pages/users.html               [Lista utilizatorilor]

PLACEHOLDER (de implementat sau eliminat)
├── /pages/reactor.html             [Vedere reactor statică]
└── /pages/reactor-log.html         [Log reactor static]
```

## 2. Plan de Refactorizare a Navbar-ului

### Problema actuală
- Navbar-ul (`navbar.js`) este inclus doar pe 9 din 24 de pagini
- Celelalte pagini (dashboard, notifications, approvals, map, users, reactor, reactor-log) au bare de navigare **hardcodate inline** în HTML, ducând la duplicare masivă
- `isAdmin()` este hardcodat `true` — nu reflectă rolul real
- Conține un link mort (`/pages/my-projects.html`)
- După approve/reject, `admin/validate.js` redirecționează la `/pages/admin.html` (inexistent)

### Soluția propusă

#### A. Unificarea navbar-ului pe toate paginile
1. **Adăugarea `<div id="main-navbar"></div>`** în toate paginile care nu îl au
2. **Adăugarea `<script type="module" src="../../modules/ui/navbar.js"></script>** în toate paginile (cu corecția căii relative)
3. **Eliminarea barelor de navigare inline** din:
   - `dashboard.html`
   - `notifications.html`
   - `approvals.html`
   - `map.html`
   - `users.html`
   - `reactor.html`
   - `reactor-log.html`

#### B. Navbar dinamic în funcție de sesiune și rol
`navbar.js` va fi rescris să:
1. **Verifice sesiunea reală** printr-un `fetch` la endpoint-ul de status (în loc de `isAdmin()` hardcodat)
2. **Determine rolul utilizatorului**: `admin` sau `user`
3. **Seteze link-urile dinamice** pe baza răspunsului:
   - Toți utilizatorii autentificați: Dashboard, Centrale, Hartă, Notificări
   - Doar admin: Admin, Aprobări, Utilizatori
   - Utilizatori neautentificați: Home, Login, Înregistrare

#### C. Navbar Links propus (final)

```
LINKS PENTRU TOȚI (autentificați):
  Dashboard    → /pages/dashboard.html          [icon: 📊]
  Centrale     → /pages/power-plants/list.html  [icon: ⚛]
  Hartă        → /pages/map.html                [icon: 🗺]
  Notificări   → /pages/notifications.html      [icon: 🔔]
  Creare Centrală → /pages/power-plants/create.html [icon: ➕]

LINKS DOAR PENTRU ADMIN:
  Admin        → /pages/admin/index.html        [icon: 🛡️]
  Aprobări     → /pages/approvals.html          [icon: ✅]
  Utilizatori  → /pages/users.html              [icon: 👥]

LINKS PENTRU NEAUTENTIFICAȚI:
  Home         → /pages/index.html              [icon: 🏠]
  Login        → /pages/login.html              [icon: 🔑]
  Înregistrare → /pages/register.html           [icon: 📝]

BUTON DROP-DOWN (utilizator autentificat):
  [Nume utilizator] → Logout (BACKEND_BASE + /logout)
```

#### D. Remedierea bug-urilor existente
1. `/pages/my-projects.html` → eliminat din navbar
2. `admin/validate.js: redirecționare` → `/pages/admin/index.html` în loc de `/pages/admin.html`
3. CSS references broken pe `map.html` și `users.html` (`style.css` → `../style.css`)
4. Reactor monitoring (`detail.js`) — buton "Înapoi" cu fallback logic

## 3. Pași concreti de execuție

| # | Acțiune | Fișiere afectate |
|---|---------|------------------|
| 1 | Rescri `navbar.js` cu session check real și link-uri dinamice | `modules/ui/navbar.js` |
| 2 | Adaugă navbar markup + script pe paginile fără navbar | `dashboard.html`, `notifications.html`, `approvals.html`, `map.html`, `users.html`, `reactor.html`, `reactor-log.html` |
| 3 | Elimină inline topbar-urile de pe paginile respective | Aceleași 7 pagini |
| 4 | Corectează `isAdmin()` → `getUserRole()` | `modules/ui/navbar.js` |
| 5 | Remediază link-ul mort `/pages/admin.html` → `/pages/admin/index.html` | `admin/validate.js` |
| 6 | Remediază CSS references pe `map.html` și `users.html` | `map.html`, `users.html` |

## 4. Diagrama fluxului de navigare (după refactorizare)

```
                    ┌──────────────────┐
                    │   index.html     │ (public)
                    │   start.html     │
                    └────────┬─────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼              ▼              ▼
       ┌──────────┐  ┌───────────┐  ┌──────────┐
       │ login    │  │ register  │  │navbar→  │
       │ .html    │  │ .html     │  │Dashboard │
       └────┬─────┘  └─────┬─────┘  └──────────┘
            └──────┬───────┘
                   ▼
          ┌────────────────┐
          │  dashboard     │ ←─── Navbar pe toate paginile
          │  .html         │      autentificate
          └───┬────┬────┬──┘
              │    │    │
     ┌────────┘    │    └──────────┐
     ▼             ▼               ▼
┌──────────┐ ┌──────────┐ ┌──────────────┐
│Centrale  │ │ Hartă    │ │ Notificări   │
│list.html │ │ map.html │ │ .html        │
└────┬─────┘ └──────────┘ └──────────────┘
     │
     ├──→ create.html → basics.html → geological.html → technical.html → finish.html
     │                                                      │
     └──→ finish.html (dacă există deja)                   │
              │                                             │
              └──→ report-results.html                      │
              │                                             │
              └──→ reactors/list.html → create/edit/detail  │
                                         │                  │
                                         └──────────────────┘
```

## Concluzie

Refactorizarea va elimina duplicarea, va face navigarea consistentă și va corecta bug-urile existente. Navbar-ul unificat va fi singurul punct de navigare principal, adaptat dinamic în funcție de starea de autentificare și rolul utilizatorului.
