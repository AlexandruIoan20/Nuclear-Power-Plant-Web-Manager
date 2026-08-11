# Diagrama C1 — Context (Nivel 1)

## Sistem: Nuclear Power Plant Web Manager

```mermaid
C4Context
  title C1 — Context Diagram: Nuclear Power Plant Web Manager

  Person(inginer, "Inginer Nuclear", "Creează centrale nucleare, reactoare și senzori\nRulează studii de fezabilitate NSVI\nImportă/exportă date JSON/CSV")
  Person(operator, "Operator", "Monitorizează reactoare în timp real (SSE 3s)\nPrimește alerte WARNING / ALERT / SCRAM\nVizualizează hărți și statistici")
  Person(admin, "Administrator", "Aprobă sau respinge centrale\nAdministrează utilizatori și roluri\nVizualizează log-uri și statistici sistem")

  System_Boundary(system, "Nuclear Power Plant Web Manager") {
    System(webapp, "Platforma Web", "PHP 8.4 + Apache + PostgreSQL 15\nFrontend SPA (vanilla JS) + Admin sub-site\n3 daemoni CLI (Simulator, Aggregator, Cleanup)")
  }

  System_Ext(smtp, "Serviciu SMTP", "Mailtrap.io —\ntrimitere email-uri\nde alertă")
  System_Ext(osm, "OpenStreetMap API", "Nominatim —\ngeocodare inversă")
  System_Ext(bigdata, "BigDataCloud API", "Reverse geocode\n+ țară + fus orar")
  System_Ext(usgs, "USGS Earthquake API", "Date seismice\nistorice")
  System_Ext(meteo, "Open-Meteo API", "Risc inundații\n+ nivel freatic")
  System_Ext(soil, "SoilGrids API", "Tip sol din\ncoordonate")
  System_Ext(cdn, "CDN-uri externe", "D3.js v7 (charts)\nLeaflet.js 1.9.4\nBootstrap 5.2.3")

  Rel(inginer, webapp, "HTTPS (REST JSON)", "Operații CRUD\nStudii fezabilitate\nImport/export date")
  Rel(operator, webapp, "HTTPS (SSE)", "Monitorizare live\nAlerte și notificări")
  Rel(admin, webapp, "HTTPS (REST JSON)", "Admin sub-site\nAprobări centrale\nGestiune utilizatori")

  Rel(webapp, smtp, "SMTP (PHPMailer)", "Email-uri alertă")
  Rel(webapp, osm, "HTTPS (REST)", "Geocodare inversă")
  Rel(webapp, bigdata, "HTTPS (REST)", "Auto-geolocație")
  Rel(webapp, usgs, "HTTPS (REST)", "Istoric seismic")
  Rel(webapp, meteo, "HTTPS (REST)", "Date climatice")
  Rel(webapp, soil, "HTTPS (REST)", "Tip sol")
  Rel(webapp, cdn, "HTTPS (CDN)", "Biblioteci JS + CSS")

  UpdateLayoutConfig($c4ShapeInRow="4", $c4BoundaryInRow="2")
```

## Actori

| Actor | Rol | Acțiuni principale |
|---|---|---|
| **Inginer Nuclear** | Utilizator principal | CRUD centrale, reactoare, senzori; studii fezabilitate; import/export |
| **Operator** | Monitorizare | Stream SSE timp real, alerte și notificări, hărți și statistici |
| **Administrator** | Staff | Aprobări centrale, gestionare utilizatori, vizualizare log-uri |

## Sisteme externe (7)

| Sistem | Protocol | Scop |
|---|---|---|
| SMTP (Mailtrap) | SMTP | Trimitere email-uri de alertă |
| OpenStreetMap/Nominatim | HTTPS REST | Geocodare inversă (lat/lon → adresă) |
| BigDataCloud | HTTPS REST | Reverse geocode + țară + fus orar |
| USGS Earthquake API | HTTPS REST | Date seismice istorice per coordonate |
| Open-Meteo API | HTTPS REST | Risc inundații și nivel freatic |
| SoilGrids API | HTTPS REST | Tip sol din coordonate |
| CDN-uri (D3.js, Leaflet, Bootstrap) | HTTPS | Biblioteci client-side |

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
