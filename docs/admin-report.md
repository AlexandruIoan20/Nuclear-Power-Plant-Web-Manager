# Raport Modul Administrare — Nuclear Power Plant Web Manager

## 1. Arhitectură Modul Admin

Spre deosebire de restul aplicației, modulul de administrare este un sub-site separat, localizat în directorul `admin/` la rădăcina proiectului. Are propriul sistem de rutare, autentificare și șabloane, însă partajează aceeași bază de date (PostgreSQL) și aceleași servicii de bază.

### 1.1 Separare față de aplicația principală

| Caracteristică | Aplicație Principală | Modul Admin |
|---|---|---|
| Director rădăcină | `frontend/` + `backend/` | `admin/` |
| Autentificare | Bazată pe sesiuni PHP, verificare rol | Bazată pe sesiuni PHP, verificare rol `ADMIN` |
| Framework | Fără framework | Fără framework |
| Stil | Interfață publică (ingineri/operatori) | Interfață administrativă (staff) |

### 1.2 Stack Tehnologic

- **Backend**: PHP 8.4 (native, fără framework)
- **Bază de date**: PostgreSQL 15
- **Frontend**: HTML5, CSS3, vanilla JavaScript, Bootstrap 5.2.3
- **Templating**: PHP inline (HTML generat de server)
- **Autentificare**: Sesiuni PHP cu verificare pe fiecare pagină

## 2. Funcționalități

### 2.1 Autentificare

- Login cu nume utilizator + parolă
- Rolul utilizatorului trebuie să fie `ADMIN` pentru acces (middleware `requireRole('ADMIN')`)
- Sesiunea expiră după inactivitate prelungită
- Logout distruge sesiunea

### 2.2 Gestiune Utilizatori

- **Vizualizare listă utilizatori**: tabel cu toți utilizatorii înregistrați
- **Filtrare**: după nume, email, rol, status
- **Căutare**: text liber
- **Creare utilizator**: formular cu username, email, nume, prenume, parolă, rol
- **Editare utilizator**: modificare date personale, rol
- **Ștergere utilizator**: confirmare înainte de ștergere
- **Vizualizare detalii**: istoric activitate, centrale create, log-uri asociate

### 2.3 Gestiune Centrale (Power Plants)

- Lista centralelor cu status, nume, utilizator asociat
- Posibilitatea de a schimba statusul între `DRAFT`, `REVIEW`, `APPROVED`, `REJECTED`
- Vizualizare detalii complete (date de bază, geologice, tehnice)
- Vizualizare rapoarte de fezabilitate

### 2.4 Vizualizare Log-uri

- Listă cronologică (invers cronologic) cu toate acțiunile din sistem
- Filtrare după:
  - **Nivel**: INFO, WARN, ERROR, DEBUG
  - **Utilizator**: selectare utilizator specific
  - **Sursă**: modulul care a generat log-ul (auth, reactor, power-plant, sensor, sse, etc.)
  - **Interval de timp**: de la/la date
- Detalii log: vizualizare conținut JSONB (context, request_uri, ip_address etc.)
- Paginare: 50 de log-uri per pagină

### 2.5 Statistici și Rapoarte

- Număr total de utilizatori (per rol)
- Număr de centrale (per status)
- Reactoare active
- Alerte nerezolvate

## 3. Securitate

- **Autentificare**: verificare rol ADMIN la fiecare request
- **CSRF**: token în formulare
- **SQL Injection**: PDO prepared statements
- **XSS**: output escapé cu `htmlspecialchars()`
- **Logger**: toate acțiunile admin sunt logate cu nivel INFO, detaliind acțiunea, utilizatorul și IP-ul
- **Filtrare date**: pe toate intrările

## 4. Interfață

### 4.1 Layout

- Sidebar de navigare (Bootstrap) cu secțiunile principale:
  - Dashboard
  - Utilizatori
  - Centrale
  - Log-uri
  - Setări

### 4.2 Pagini

| Rută | Descriere |
|---|---|
| `admin/dashboard.php` | Dashboard cu statistici generale |
| `admin/users.php` | Listă utilizatori cu căutare și filtrare |
| `admin/user-edit.php` | Formular editare utilizator |
| `admin/user-create.php` | Formular creare utilizator |
| `admin/user-delete.php` | Confirmare ștergere |
| `admin/logs.php` | Vizualizare log-uri cu filtre avansate |
| `admin/log-detail.php` | Detalii log individual |

### 4.3 Componente UI

- Tabel cu datatable (sortare, căutare, paginare)
- Formulare cu validare client-side și server-side
- Modal de confirmare pentru acțiuni distructive
- Badge-uri pentru statusuri (codificare culori)
- Breadcrumb navigare

## 5. Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
