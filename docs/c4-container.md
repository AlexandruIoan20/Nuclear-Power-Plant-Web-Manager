# Diagrama C4 — Container (Nivel 2)

## Containerele Sistemului

```mermaid
C4Container
  title Container Diagram — Nuclear Power Plant Web Manager

  Person(engineer, "Inginer Nuclear", "Gestionează centrale, reactoare și senzori")
  Person(operator, "Operator", "Monitorizează reactoare")
  Person(admin, "Administrator", "Aprobă centrale, admin utilizatori")

  System_Boundary(frontend, "Frontend") {
    Container(webapp, "Aplicație Web", "HTML, CSS, JavaScript (ES Modules)", "Interfață utilizator, chart-uri D3.js, hartă Leaflet")
  }

  System_Boundary(backend, "Backend") {
    Container(api, "API REST", "PHP 8.4 + Apache", "67 rute API, autentificare, CSRF, CORS")
    Container(simulator, "Simulator Senzori", "PHP CLI (daemon)", "Rulează în Docker, generează valori la fiecare 3s")
    Container(aggregator, "Agregator Măsurători", "PHP CLI (daemon)", "Agregare date orare la 60s")
    Container(cleanup, "Cleanup Date", "PHP CLI (daemon)", "Curăță date mai vechi de 1 oră")
  }

  System_Boundary(database, "Stocare") {
    ContainerDb(db, "Baza de date", "PostgreSQL 15", "16 tabele, 7 enums, indexes, FKs")
  }

  System_Ext(smtp, "SMTP", "Mailtrap")
  System_Ext(osm, "OpenStreetMap", "Nominatim API")
  System_Ext(d3cdn, "D3.js CDN", "v7")
  System_Ext(leafletcdn, "Leaflet.js CDN", "1.9.4")

  Rel(engineer, webapp, "HTTPS", "Operații CRUD")
  Rel(operator, webapp, "HTTPS", "Monitorizare SSE")
  Rel(admin, webapp, "HTTPS", "Admin actions")

  Rel(webapp, api, "fetch(), AJAX", "JSON, SSE stream")
  Rel(api, db, "PDO", "SQL queries")
  Rel(simulator, db, "PDO", "Scrie valori senzori")
  Rel(aggregator, db, "PDO", "Citește/scrie măsurători orare")
  Rel(cleanup, db, "PDO", "Șterge date vechi")
  Rel(api, smtp, "PHPMailer", "Email-uri notificări")
  Rel(webapp, osm, "fetch()", "Geocodare")
  Rel(webapp, d3cdn, "<script>", "Charts")
  Rel(webapp, leafletcdn, "<script>", "Hărți")

  UpdateLayoutConfig($c4ShapeInRow="2", $c4BoundaryInRow="2")
```

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).

## Descriere

### Containere

| Container | Tehnologie | Rol |
|---|---|---|
| **Aplicație Web** | HTML5, CSS3, JavaScript (ES Modules) | Interfața utilizator; încărcată în browser, comunică cu API-ul |
| **API REST** | PHP 8.4 + Apache (mod_php) | 67 rute, autentificare sesiuni, CSRF, CORS, validări |
| **Simulator Senzori** | PHP CLI (daemon) | Rulează în buclă infinită la 3s, generează valori realiste per tip reactor |
| **Agregator Măsurători** | PHP CLI (daemon) | Agregare date orare la 60s pentru statistici |
| **Cleanup Date** | PHP CLI (daemon) | Șterge date mai vechi de 1 oră (CLEANUP_INTERVAL) |
| **Baza de date** | PostgreSQL 15 | 16 tabele, 7 tipuri enum, indexes optimizați |

### Fluxuri de date principale

1. **CRUD**: Browser → API REST → PostgreSQL
2. **Monitorizare**: Simulator → PostgreSQL → API SSE → Browser (EventSource)
3. **Alerte**: Simulator → Observer Pattern → AlertRepository + EmailService
4. **Statistici**: Agregator → measurements_hourly → API Stats → Browser (D3.js)
5. **Autentificare**: Browser → API → Sesiune PHP → Verificare rol → Răspuns
