# Diagrama C4 — Context (Nivel 1)

## Sistem: Nuclear Power Plant Web Manager

```mermaid
C4Context
  title System Context — Nuclear Power Plant Web Manager

  Person(engineer, "Inginer Nuclear", "Gestionează centrale, reactoare și senzori")
  Person(operator, "Operator", "Monitorizează reactoare în timp real, vede alerte")
  Person(admin, "Administrator", "Aprobă centrale, administrează utilizatori, vede log-uri")

  System_Boundary(system, "Nuclear Power Plant Web Manager") {
    System(webapp, "Aplicația Web", "PHP + Apache + PostgreSQL")
  }

  System_Ext(smtp, "Serviciu SMTP", "Mailtrap.io — trimitere email-uri")
  System_Ext(osm, "OpenStreetMap API", "Geocodare / Nominatim")
  System_Ext(d3cdn, "CDN D3.js v7", "Diagrame și grafice")
  System_Ext(leafletcdn, "CDN Leaflet.js 1.9.4", "Hartă interactivă")

  Rel(engineer, webapp, "Creează/editează centrale, reactoare, senzori")
  Rel(operator, webapp, "Monitorizează stream SSE, vede notificări")
  Rel(admin, webapp, "Aprobă centrale, administrează utilizatori")
  Rel(webapp, smtp, "Trimite notificări email", "SMTP")
  Rel(webapp, osm, "Completează coordonate", "HTTPS")
  Rel(webapp, d3cdn, "Încarcă biblioteca D3.js", "HTTPS")
  Rel(webapp, leafletcdn, "Încarcă biblioteca Leaflet", "HTTPS")

  UpdateLayoutConfig($c4ShapeInRow="3", $c4BoundaryInRow="1")
```

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).

## Descriere

Diagrama de context C4 (Nivel 1) ilustrează sistemul și interacțiunile sale cu actorii externi:

- **Inginer Nuclear** — utilizator principal care creează și administrează centrale, reactoare și senzori
- **Operator** — monitorizează în timp real datele senzorilor și primește alerte
- **Administrator** — gestionează fluxul de aprobare al centralelor și utilizatorii
- **Servicii externe**: SMTP (Mailtrap), OpenStreetMap (geocodare), CDN-uri (D3.js, Leaflet.js)
