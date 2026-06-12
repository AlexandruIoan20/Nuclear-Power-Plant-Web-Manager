# Backend Audit Report — Nuclear Power Plant Web Manager

> Generat la scanarea completă a codului sursă, bazei de date și configurațiilor backend.

---

## 🚨 Buguri Critice

### 1. Admin override la fiecare request
**`public/index.php:187-214`** — La runtime, se face un `ON CONFLICT (email) DO UPDATE SET` care rescrie tot contul admin (inclusiv parola) la fiecare request. Dacă un admin își schimbă parola, aceasta e suprascrisă la următorul request.

### 2. Status DRAFT forțat la update plant details
**`src/Services/PlantService/DetailsPlantService.php:36`** — `$status = PlantStatus::DRAFT;` e hardcodat. Dacă editezi o centrală aflată în REVIEW/APPROVED, aceasta revine în DRAFT automat — fără avertisment.

### 3. Login/Register nu funcționează cu JSON
**`src/Controllers/UserController.php:46-51`** — Citește din `$_POST` în loc de `php://input`. Frontend-ul modern (SPA) trimite JSON, deci login/register vor primi mereu câmpuri goale.

### 4. Alertele nu sunt filtrate pe user
**`src/Repositories/AlertRepository.php:23-38`** — `getUnreadAlertsForUser(?string $userId)` **ignoră complet** `$userId`. Returnează TOATE alertele necitite din baza de date, indiferent de utilizator.

### 5. `ValueError` neprins la `ReactorType::from()` / `CoolingType::from()`
**`src/Services/PlantService/TechnicalPlantService.php:46-48, 74-78`** — PHP 8.0+ aruncă `\ValueError`, nu `\Exception`. Blocul `catch(Exception $e)` din controller nu prinde `ValueError` → eroare 500 goală.

### 6. Inconsistență status în pending approvals
- **`src/Services/PlantServiceFacade.php:56-77`** — filtrează `PENDING || DRAFT`.
- **`src/Controllers/PlantController/DetailsPlantController.php:92`** — filtrează doar `REVIEW`.
- Adminul vede centrale DRAFT pe care nu le poate aproba.

### 7. Parola DB hardcodată în fallback
**`public/index.php:143`** și în toate scripturile (`scripts/simulator.php:21`, `scripts/aggregator.php:10`, `scripts/cleanup.php:10`, `scripts/seed_test_reactors.php:11`) — `getenv('DB_PASSWORD') ?: 'glorierebeja'`. Parola de producție apare clar în cod.

---

## ⚠️ Buguri Medii

### 8. Cleanup prea agresiv
**`scripts/cleanup.php:31`** — Șterge date mai vechi de **20 de secunde**. Păstrează doar ultimele 20s de măsurători. Agregatorul (la fiecare 10s) poate rata date între cleanup-uri.

### 9. Duplicat `require_once` pentru `urls.php`
**`public/index.php:83`** — `require_once __DIR__ . '/../src/Constants/urls.php';` apare și la linia 3 și la linia 83.

### 10. `NotificationRepository.php` gol
**`src/Repositories/NotificationRepository.php`** — Fișierul există dar e gol (0 bytes).

### 11. Path incorect în `seed_test_reactors.php`
**`scripts/seed_test_reactors.php:99-107`** — `require_once __DIR__ . '/src/Helpers/generateUUID.php'` — rulează doar când scriptul e executat din `scripts/`. Corect: `__DIR__ . '/../src/...'`.

### 12. Mailtrap credentials hardcodate
**`src/Services/EmailService.php:25-26`** — `'433cd381d8be35'` și parola goală ca fallback.

### 13. Health endpoint fără autentificare
**`public/index.php:62-79`** — `/health` trimite email fără să verifice auth. Oricine poate trigger-ui trimiterea de emailuri.

### 14. `sleep(6)` cu comentariu greșit
**`scripts/test_alert.php:63`** — Comentariu zice "Așteptare 2 secunde" dar codul face `sleep(6)`.

---

## 🔧 Buguri Minore

### 15. Senzori fără `measurement_field`
**`database/seek/sensors_seek.sql:30,52,78,102`** — Senzorii de vibrații au `measurement_field` = `NULL`.

### 16. Lipsește FK constraint pe `alerts.plant_id`
**`database/alerts/tables.sql`** — Tabela `alerts` nu are FOREIGN KEY către `power_plants`. Pot apărea orfani.

### 17. `LogService` neinițializat în scripturi
Scripturile `simulator.php`, `aggregator.php`, `cleanup.php` nu apelează `LogService::init(...)`, deci orice logare aruncă `RuntimeException`.

### 18. Toate listările sînt fără paginare
`/api/power-plants`, `/api/reactors`, `/api/sensors` — returnează toate rândurile odată. Problemă de performanță cu multe date.

### 19. Calea relativă în `ReactorRepository.php`
**`src/Repositories/ReactorRepository.php:3-6`** — `__DIR__ . '../../Entities/...'` vs `__DIR__ . '/../Entities/...'`. Funcționează dar e fragil.

### 20. `throw $e` în TransactionManager pierde stack trace-ul original
**`src/Helpers/TransactionManager.php:32`** — Ar trebui `throw $e;` dar `begin()` nu verifică dacă e deja în tranzacție.

---

## 🏗️ Sugestii de îmbunătățire

| # | Ce | Unde |
|---|-----|------|
| 1 | Remediați duplicate require | `public/index.php:83` |
| 2 | JSON body parser pentru login/register | `UserController::handleLogin/handleRegister` |
| 3 | Filtrare alerte pe user | `AlertRepository::getUnreadAlertsForUser` |
| 4 | Prindeți `\Throwable` în loc de `\Exception` | Toate catch-urile din controllere |
| 5 | Folosiți variabile de mediu, nu fallback-uri hardcodate | Toate scripturile + index.php |
| 6 | Măriți fereastra cleanup (h/zile, nu secunde) | `scripts/cleanup.php` |
| 7 | Adăugați FK pe `alerts.plant_id` | `database/alerts/tables.sql` |
| 8 | Eliminați DEV admin override | `public/index.php:187-214` |
| 9 | Adăugați paginare pe listări | Toate endpoint-urile GET de listare |
| 10 | NU mai forțați DRAFT la update | `DetailsPlantService::updatePlantDetails` |
| 11 | Eliminați `/health` sau puneți-l în spatele auth | `public/index.php:62-79` |
| 12 | Initializați LogService în scripturi | `simulator.php`, `aggregator.php`, `cleanup.php` |
| 13 | Verificați existența reactorului la create sensor | `SensorService::createSensor` |
| 14 | Unificați `getPendingApprovalsList()` | `PlantServiceFacade` vs `DetailsPlantController` |
| 15 | Corectați path-ul în seed script | `scripts/seed_test_reactors.php:99` |
