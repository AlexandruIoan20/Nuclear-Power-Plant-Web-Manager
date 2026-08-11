# Diagrama C3 — Frontend: Privire de ansamblu

## Arhitectură SPA (Vanilla JavaScript)

```mermaid
flowchart LR
  subgraph Entry["Punct de Intrare"]
    INDEX["index.html\nLayout principal"]
    NAV["Navbar\nDinamic per rol"]
  end

  subgraph Core["Module de Bază (modules/core/)"]
    ROUTER["router.js\nParsează URL\nDynamic import()\nGestionează istoric"]
    AUTH["auth.js\nSesiune utilizator\nTimeout\nRedirect login"]
    API["api.js\nfetch() wrapper\nJSON + CSRF token\nGestionare erori"]
    CSRF["csrf.js\nToken CSRF\ndin <meta> tag"]
    FH["form-handler.js\nValidare client\nSubmit API\nErori inline"]
    VAL["validator.js\nReguli: required,\nemail, minLength,\nrange, pattern"]
    LOAD["loader.js\nAnimator CSS\nOperații async"]
  end

  subgraph Pages["Pagini"]
    P1["12 pagini HTML\n+ 12 module JS"]
    P2["Încărcare dinamică\nla navigare"]
  end

  subgraph Services["Strat Servicii (modules/services/)"]
    S1["powerPlantService.js"]
    S2["reactorService.js"]
    S3["sensorService.js"]
    S4["authService.js"]
    S5["statsService.js"]
    S6["exportService.js"]
    S7["geolocationService.js"]
  end

  subgraph Backend["Backend API"]
    BE["PHP 8.4\n67 rute REST\nSSE endpoint"]
  end

  Entry --> Core
  Core --> ROUTER
  ROUTER --> Pages
  Pages --> Services
  Services --> API
  API --> Backend
  BACKEND["Backend API"] -.-> SSE["SSE EventSource\nStream senzori 3s"]
  SSE --> PAGES["Pagina monitorizare"]
```

## Fluxul unei navigări

```
  Utilizator dă click pe link
        │
        ▼
  Router.parseURL(window.location)
        │
        ▼
  Dynamic import() → pagină.html + pagină.js
        │
        ▼
  Pagina se inițializează:
  ├── Încarcă template HTML în container
  ├── Inițializează event listeners
  ├── Fetch date inițiale (api.js)
  └── Render UI (tabele, formulare, hărți, chart-uri)
        │
        ▼
  La submit formular:
  ├── Validare client-side (validator.js)
  ├── api.js → fetch() + JSON + CSRF token
  ├── Backend → JSON response
  └── Update DOM cu răspunsul
        │
        ▼
  Pentru SSE (monitorizare):
  ├── new EventSource(url)
  ├── onmessage → parse JSON
  └── Update DOM la fiecare 3s
```

## Module de bază — detalii

| Modul | Fișier | Responsabilitate |
|---|---|---|
| **Router** | `core/router.js` | Parsează URL, dynamic import pagini, istoric browser |
| **Auth** | `core/auth.js` | Verifică sesiunea, timeout, redirect la login |
| **API Client** | `core/api.js` | Wrapper fetch cu JSON, CSRF, erori HTTP |
| **CSRF** | `core/csrf.js` | Citește token-ul CSRF din meta tag |
| **Form Handler** | `core/form-handler.js` | Validare + submit formulare cu feedback vizual |
| **Validator** | `core/validator.js` | Reguli de validare (required, email, range, etc.) |
| **Loader** | `core/loader.js` | Loader animat pentru operații asincrone |

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
