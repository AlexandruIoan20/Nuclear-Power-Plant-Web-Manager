# Diagrama C2 — Container (Nivel 2)

## Containerele Sistemului

```mermaid
C4Container
  title C2 — Container Diagram: Nuclear Power Plant Web Manager

  Person(inginer, "Inginer Nuclear", "Gestionează centrale,\nreactoare și senzori")
  Person(operator, "Operator", "Monitorizează\nreactoare în timp real")
  Person(admin, "Administrator", "Aprobă centrale,\nadminează utilizatori")

  System_Boundary(frontend, "Frontend (Browser)") {
    Container(spa, "Single-Page Application", "HTML5, CSS3, JavaScript ES Modules\nRouter custom, 12 pagini dinamice", "Interfața principală: centrale,\nreactoare, senzori, hartă Leaflet,\nchart-uri D3.js, stream SSE")
    Container(adminSite, "Admin Sub-site", "PHP + Bootstrap 5.2.3\n7 pagini server-rendered", "Interfață administrativă:\ndashboard, utilizatori,\ncentrale, log-uri")
  }

  System_Boundary(backend, "Backend (Docker)") {
    Container(api, "API REST", "PHP 8.4 + Apache\n67 rute API\nFront Controller Pattern", "Autentificare sesiuni,\nCSRF, CORS, validare,\nController → Service → Repository")
    ContainerDb(db, "Baza de date", "PostgreSQL 15\next: uuid-ossp, pgcrypto", "18 tabele, 8 enum-uri,\n7 indecși, constrângeri FK")
    Container(sim, "Simulator Senzori", "PHP CLI daemon\nBuclă la 3s", "12 strategii generare valori\n4 simulatoare (PWR/BWR/PHWR/FBR)\nObserver Pattern, ThresholdChecker")
    Container(agg, "Aggregator Măsurători", "PHP CLI daemon\nBuclă la 60s", "Agregare orară medii/min/max\n→ measurements_hourly")
    Container(cln, "Cleanup Date", "PHP CLI daemon\nBuclă la 3600s", "Șterge măsurători\nmai vechi de 1 oră")
  }

  System_Ext(smtp, "SMTP", "Mailtrap.io")
  System_Ext(osm, "OpenStreetMap", "Nominatim API")
  System_Ext(bigdata, "BigDataCloud", "Reverse geocode")
  System_Ext(usgs, "USGS", "Seismic API")
  System_Ext(meteo, "Open-Meteo", "Flood risk")
  System_Ext(soil, "SoilGrids", "Soil type")
  System_Ext(d3cdn, "D3.js CDN", "v7 charts")
  System_Ext(leafcdn, "Leaflet CDN", "1.9.4 hărți")

  Rel(inginer, spa, "HTTPS", "CRUD centrale/reactoare/senzori,\nfezabilitate, import/export")
  Rel(operator, spa, "HTTPS (SSE)", "Monitorizare live, alerte")
  Rel(admin, adminSite, "HTTPS", "Admin actions")
  Rel(admin, spa, "HTTPS", "Vizualizare centrale/statistici")

  Rel(spa, api, "fetch() JSON + CSRF", "REST API calls")
  Rel(spa, d3cdn, "<script>", "Charts library")
  Rel(spa, leafcdn, "<script>", "Maps library")
  Rel(spa, osm, "fetch()", "Geocodare directă")
  Rel(adminSite, api, "fetch() JSON + CSRF", "Admin API calls")

  Rel(api, db, "PDO prepared statements", "SQL queries")
  Rel(api, smtp, "PHPMailer", "Email-uri alertă")
  Rel(api, bigdata, "cURL HTTPS", "Auto-geolocație")
  Rel(api, usgs, "cURL HTTPS", "Date seismice")
  Rel(api, meteo, "cURL HTTPS", "Date climatice")
  Rel(api, soil, "cURL HTTPS", "Tip sol")

  Rel(sim, db, "PDO", "Scrie valori senzori +\nmăsurători compuse")
  Rel(agg, db, "PDO", "Citește measurements,\nscrie measurements_hourly")
  Rel(cln, db, "PDO", "Șterge measurements\nmai vechi de 1h")

  UpdateLayoutConfig($c4ShapeInRow="3", $c4BoundaryInRow="2")
```

## Containere

| Container | Tehnologie | Funcție principală |
|---|---|---|
| **SPA** | HTML5, CSS3, JS ES Modules | Interfață utilizator, router custom, 12 pagini |
| **Admin Sub-site** | PHP + Bootstrap 5.2.3 | Interfață admin server-rendered, 7 pagini |
| **API REST** | PHP 8.4 + Apache | 67 rute REST, autentificare, CSRF, CORS |
| **PostgreSQL** | PostgreSQL 15 | Stocare date, 18 tabele, 8 enum-uri |
| **Simulator** | PHP CLI daemon | Generare valori senzori la 3s |
| **Aggregator** | PHP CLI daemon | Agregare orară la 60s |
| **Cleanup** | PHP CLI daemon | Curățare date vechi la 3600s |

## Fluxuri principale

```
Flux 1 — CRUD:         Browser → fetch() → API REST → PDO → PostgreSQL → JSON
Flux 2 — Monitorizare: Simulator (tick 3s) → INSERT measurements → API SSE → EventSource
Flux 3 — Alerte:       Simulator → ThresholdChecker → Observeri → DB + Email
Flux 4 — Statistici:   Aggregator (60s) → measurements_hourly → API Stats → D3.js
Flux 5 — Autentificare: Browser → API → Sesiune PHP → Middleware → Răspuns
Flux 6 — Geolocație:   Browser/API → BigDataCloud/USGS/Open-Meteo/SoilGrids → Auto-complete
```

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
