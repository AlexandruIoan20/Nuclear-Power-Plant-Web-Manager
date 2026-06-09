# Raport sistem de autentificare

## Arhitectura fluxului login

```
login.html (frontend, port 5500)
  → fetch POST /login (backend, port 8081, credentials: include)
    → index.php: session_start() + CORS check + CSRF validation
    → Router → UserController::handleLogin()
      → UserService::authenticateUser()
        → UserRepository::findByEmail() [prepared statement]
        → password_verify() vs bcrypt hash
      ← { status: "success", redirect: "http://localhost:5500/dashboard.html" }
  → window.location.href = payload.redirect
```

Sesiunea PHP stochează `user_id`, `user_email`, `user_role`, `username`.
Frontendul verifică starea sesiunii pe `GET /api/user/status` cu `credentials: include`.

---

## Ce funcționează corect

| Aspect | Status |
|--------|--------|
| Parole stocate cu `password_hash(PASSWORD_BCRYPT)` | OK |
| Query-uri parametrizate (PDO prepared statements) | OK — fără SQL injection |
| Cookie `httponly=true` | OK — JS nu poate citi session cookie |
| Logout șterge cookie-ul + distruge sesiunea | OK |
| CORS cu `Allow-Credentials: true` | OK — permite cross-origin cu cookie |
| CORS verifică origin explicit (`in_array`) + regex fallback | OK |
| `htmlspecialchars()` în PHP views (XSS output encoding) | OK |
| Validare email server-side (`filter_var`) | OK |
| Verificare existență email la register | OK |

---

## Ce s-a rezolvat între timp

### [REZOLVAT] #2 — Cookie de sesiune blocat de browser (SameSite)

`index.php:7` — `'cookie_samesite' => 'Lax'` în loc de `'None'`.
Nu mai e nevoie de `Secure=true`, cookie-ul funcționează cross-port pe localhost.

### [REZOLVAT] #9 — Session lifetime

`index.php:3-4`:
```php
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
```
Sesiunea expiră după 1 oră de inactivitate.

### [REZOLVAT] #3 — CSRF protection

Backend: token random (`bin2hex(random_bytes(32))`) generat la `session_start()`,
validat pe orice request POST/PUT/PATCH/DELETE cu `hash_equals()`. Rută
`GET /api/csrf-token` care expune token-ul.

Frontend:
- **Module pages** → `core/api.js` adaugă `X-CSRF-Token` automat la orice
  request mutativ
- **Inline pages** → `globals.js` expune `window.getCsrfToken()`, fiecare
  fetch POST/PUT include header-ul manual

### [REZOLVAT] #13 — `first_name`, `last_name`, `username` incorecte

Acum formularul de register are 3 câmpuri separate (`Prenume`, `Nume`,
`Nume de utilizator`). User entity, repository, service, controller — toate
tratează corect cele 3 câmpuri. Zero referințe rămase la
`$user->getName()` / `$user->setName()` pe User.

---

## Ce mai trebuie rezolvat

### 🔴 Critic

#### 1. `session_regenerate_id()` comentat — vulnerabilitate de session fixation

`UserController.php:147`:
```php
// session_regenerate_id(true);
```

Acum că SameSite e `Lax`, se poate decomenta. Cookie-ul nu se mai pierde
cross-port.

---

#### 4. Fără rate limiting / brute-force protection

Poți încerca 10.000 de parole pe secundă la `/login`. Niciun fel de blocaj.

**Soluție:** Middleware care memorează timestamp-urile login-urilor eșuate
per IP (fișier / Redis / DB) și blochează după N încercări (ex: 5 eșuate
în 15 minute).

---

#### 5. Role assignment by email domain

`UserService.php`:
```php
$role = str_ends_with($email, '@admin.ro') ? 'ADMIN' : 'OPERATOR';
```

Oricine se înregistrează cu `ceva@admin.ro` devine automat administrator.

**Soluție:** Toți utilizatorii noi sunt OPERATOR. Un admin îi promovează
dintr-un panou de administrare.

---

#### 6. Parola DB în Git

`.env` conține `DB_PASSWORD=glorierebeja` și e în versionare.

**Soluție:** Adaugi `.env` în `.gitignore`. Creezi `.env.example` cu
valorile goale. Rotesti parola.

---

### 🟡 High

#### 7. Redirect-uri hardcodate în PHP backend

Toate rutele redirectează la `http://localhost:5500/*` și
`http://localhost:8081/*`. Răspândit în:

- `UserController.php` — `header('Location: http://localhost:5500/...')`
- `AuthHelper.php` — `header('Location: http://localhost:8081/login')`
- `login.view.php`, `register.view.php`, `dashboard.view.php`, `start.view.php`
- `RssService.php`

**Soluție:** Extragi URL-urile în constante PHP (similar cu pattern-ul din
frontend cu `api.config.js` + `globals.js`).

---

#### 8. Fără HTTPS

Tot traficul e pe HTTP. Parola circulă în plaintext în POST body. Acceptabil
în local dev, dar critic dacă aplicația e deployată pe un domeniu real.

**Soluție:** Adaugi un reverse proxy (nginx / Caddy / Traefik) cu TLS în
fața containerului PHP. Pentru local dev, mkcert + Caddy.

---

#### 10. User registration fără email verification

Oricine poate crea cont. Niciun fel de confirmare.

**Soluție:** La register, trimiți un email cu link de confirmare. Câmp
`email_verified` în DB. Conturile neverificate expiră după 24h.

---

### 🔵 Medium

#### 11. CORS regex prea permisiv

`index.php`:
```php
preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin)
```
Orice port e acceptat (ex: localhost:3000).

**Soluție:** Restrângi la porturile folosite: `(:(5500|8081))?`.

---

#### 12. PHP view-urile hardcodează URL-uri

`login.view.php`, `register.view.php`, `dashboard.view.php`, `start.view.php`
au `http://localhost:5500/...` și `http://localhost:8081/...` în CSS links,
form actions, anchor hrefs.

**Soluție:** Același pattern ca în frontend: definești constante PHP și le
folosești în view-uri.

---

## Ordinea de priorități actualizată

1. **#1** → Decomentezi `session_regenerate_id(true)` (acum sigur, SameSite e Lax)
2. **#5** → Elimini role assignment by email (admin instant)
3. **#6** → Scoți parola DB din Git
4. **#4** → Rate limiting
5. **#7 + #12** → URL-uri în constante de configurare
6. **#8 + #10 + #11** → HTTPS, email verification, CORS restrictiv
