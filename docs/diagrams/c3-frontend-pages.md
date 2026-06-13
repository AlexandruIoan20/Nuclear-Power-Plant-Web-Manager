# Diagrama C3 — Frontend: Pagini și Componente

## Arhitectura paginilor

```mermaid
flowchart TB
  subgraph Auth["Autentificare"]
    LOGIN["/login → login.js\nFormular login\n+ validator"]
    REG["/register → register.js\nFormular register\n+ validator"]
  end

  subgraph Plants["Centrale Nucleare"]
    MAP["/ → index.js (main)\nHartă Leaflet\ncu markeri și popup-uri"]
    MY_PLANTS["/my-plants → my-plants.js\nListă centrale proprii\nTabel cu filtre + status badge"]
    PLANT_CREATE["/power-plants/create → create.js\nWizard pas 1: denumire"]
    PLANT_DETAILS["/power-plants/{id}/details → details.js\nProfil complet centrală"]
    PLANT_BASICS["/power-plants/{id}/basics → basics.js\nWizard pas 2: capacitate,\ndurată construcție, descriere"]
    PLANT_GEO["/power-plants/{id}/geological → geological.js\nWizard pas 3: coordonate,\ntip sol, seismic, inundații\nauto-geolocație (4 API-uri)"]
    PLANT_TECH["/power-plants/{id}/technical → technical.js\nWizard pas 4: configurații\nreactoare, eficiență, risc"]
    PLANT_FINISH["/power-plants/{id}/finish → finish.js\nSumar final + submit review"]
    PLANT_FEAS["/power-plants/{id}/feasibility → feasibility.js\nRaport NSVI:\nscor, deficiențe, erori"]
  end

  subgraph Reactors["Reactoare"]
    R_LIST["/reactors → list.js\nListă reactoare\nper centrală"]
    R_CREATE["/reactors/create → create.js\nFormular reactor:\ntip, răcire, parametri"]
    R_EDIT["/reactors/edit/{id} → edit.js\nEditare reactor"]
  end

  subgraph Sensors["Senzori"]
    S_MONITOR["/sensors/monitor/{id} → monitor.js\nStream SSE live (3s)\nTabel + gauge +\nalerte în timp real"]
    S_LIST["/sensors/list/{reactorId} → list.js\nListă senzori per reactor"]
    S_CREATE["/sensors/create → create.js\nCreare senzor manual"]
    S_EDIT["/sensors/edit/{id} → edit.js\nEditare praguri și calibrare"]
  end

  subgraph Other["Alte pagini"]
    STATS["/stats → stats.js\nDashboard D3.js:\nbar chart, donut, grouped bar\nKPI-uri centrale/reactoare"]
    ALERTS["/alerts → alerts.js\nListă alerte\nfiltre + mark as read"]
    IMPORT["/import-export → import-export.js\nImport/export\nJSON/CSV/ZIP"]
  end

  subgraph UI["Componente UI Reutilizabile (modules/ui/)"]
    D3["d3charts.js\nBar chart, donut,\ngrouped bar"]
    ALERT_POPUP["alertPopup.js\nToast + notificări\nîn timp real"]
    MAP["map.js\nHartă Leaflet\nmarkeri + popup"]
    TABLE["plantTable.js\nTabel sortabil\ncu paginare"]
    FILTERS["plantFilters.js\nFiltrare după\nstatus și țară"]
    SELECT["selectLoader.js\nDropdown cu\nîncărcare dinamică"]
  end

  subgraph HTML["Pagini HTML (pages/)"]
    H_AUTH["login.html\nregister.html"]
    H_PLANTS["power-plants/create.html\nbasics.html\ngeological.html\ntechnical.html\nfinish.html"]
    H_REACTORS["reactors/list.html\ncreate.html\nedit.html"]
    H_SENSORS["sensors/monitor.html\nlist.html\ncreate.html\nedit.html"]
    H_OTHER["my-plants.html\nstats.html\nalerts.html\nimport-export.html"]
  end

  HTML --> Auth
  HTML --> Plants
  HTML --> Reactors
  HTML --> Sensors
  HTML --> Other

  Plants --> UI
  Reactors --> UI
  Sensors --> UI
  Other --> UI
```

## Harta rutelor complete

| Rută URL | Fișier HTML | Modul JS | Descriere |
|---|---|---|---|
| `/` | `index.html` | `index.js` | Hartă Leaflet cu toate centralele |
| `/login` | `login.html` | `login.js` | Autentificare |
| `/register` | `register.html` | `register.js` | Înregistrare |
| `/my-plants` | `my-plants.html` | `my-plants.js` | Centralele mele |
| `/power-plants/create` | `create.html` | `create.js` | Wizard pas 1 |
| `/power-plants/{id}/details` | `details.html` | `details.js` | Detalii centrală |
| `/power-plants/{id}/basics` | `basics.html` | `basics.js` | Wizard pas 2 |
| `/power-plants/{id}/geological` | `geological.html` | `geological.js` | Wizard pas 3 |
| `/power-plants/{id}/technical` | `technical.html` | `technical.js` | Wizard pas 4 |
| `/power-plants/{id}/finish` | `finish.html` | `finish.js` | Wizard final |
| `/power-plants/{id}/feasibility` | `feasibility.html` | `feasibility.js` | Raport NSVI |
| `/reactors` | `list.html` | `list.js` | Listă reactoare |
| `/reactors/create` | `create.html` | `create.js` | Creare reactor |
| `/reactors/edit/{id}` | `edit.html` | `edit.js` | Editare reactor |
| `/sensors/monitor/{id}` | `monitor.html` | `monitor.js` | Monitorizare SSE |
| `/sensors/list/{id}` | `list.html` | `list.js` | Listă senzori |
| `/sensors/create` | `create.html` | `create.js` | Creare senzor |
| `/sensors/edit/{id}` | `edit.html` | `edit.js` | Editare senzor |
| `/stats` | `stats.html` | `stats.js` | Dashboard statistici |
| `/alerts` | `alerts.html` | `alerts.js` | Listă alerte |
| `/import-export` | `import-export.html` | `import-export.js` | Import/export |

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
