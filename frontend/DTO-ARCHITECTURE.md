# DTO Architecture Report — Frontend ↔ Backend Communication

## 1. Communication Layer

### 1.1 Transport
- Toate request-urile merg prin **HTTP** la `API_BASE` (configurat în `modules/config/api.config.js`).
- Autentificare prin **cookie de sesiune** (`credentials: 'include'`).
- CSRF protejat prin token obținut din `GET /api/csrf-token` și trimis ca header `X-CSRF-TOKEN` la orice metodă care modifică date (POST/PUT/PATCH/DELETE).

### 1.2 API Client — `modules/core/api.js`
Punctul central de comunicare. Expune un obiect `api` cu metodele:
```
api.get(url)
api.post(url, body)
api.put(url, body)
api.patch(url, body)
api.delete(url)
api.download(url)   // pentru blob/download
```

**Fluxul unui request:**
```
Page consumer
    ↓
Service layer (powerPlantService, reactorService, …)
    ↓  apelează api.get/post/…
api.js  (request())
    ├── adaugă Content-Type: application/json
    ├── adaugă X-CSRF-TOKEN (dacă nu e GET/HEAD/OPTIONS)
    ├── JSON.stringify(body)
    ├── fetch(API_BASE + endpoint, { method, headers, body, credentials })
    ├── parseResponseBody() — extrage JSON din răspuns
    ├── dacă !response.ok → aruncă { status, message }
    └── returnează body-ul parsat (care vine de la backend)
```

**Backend** răspunde întotdeauna într-un format consistent:
```json
// Success
{ "status": "success", "data": { ... }, "message": "..." }

// Error  
{ "status": "error", "message": "..." }
```

---

## 2. Backend DTOs

Toate backend DTO-urile extind `BaseDTO` care implementează `JsonSerializable`:
```php
class BaseDTO implements JsonSerializable {
    public function jsonSerialize(): array {
        return get_object_vars($this);  // serializare automată a proprietăților
    }
}
```
Când un DTO face `json_encode`, PHP convertește automat toate `public readonly` properties în chei JSON snake_case după numele variabilei PHP.

### 2.1 Lista completă backend DTOs

| DTO | Câmpuri | Folosit în |
|---|---|---|
| **`ApiResponseDTO`** | `status`, `data`, `message` | Înveliș pentru toate răspunsurile API |
| **`CreateDataResponseDTO`** | `dataId`, `message` | Răspuns la creare (plantă, basics, etc.) |
| **`PlantDTO`** | `id`, `name`, `status`, `createdBy`, `createdAt`, `updatedAt` | Detalii sumare plantă |
| **`PlantDetailsDTO`** | `id`, `name`, `status` | Detalii de bază plantă |
| **`PlantListDTO`** | `id`, `name`, `country`, `latitude`, `longitude`, `status`, `createdBy`, `createdAt`, `updatedAt` | Listă de centrale |
| **`PlantMapDTO`** | `id`, `name`, `country`, `latitude`, `longitude`, `status`, `createdBy`, `createdAt`, `updatedAt`, `editUrl` | Date pentru hartă |
| **`PlantStatusListDTO`** | `id`, `name`, `status` | Filtrare după status |
| **`GetPlantDTO`** | `details` (PlantDTO), `basic` (BasicPlantDataDTO), `geological` (GeologicalPlantDataDTO), `technical` (TechnicalPlantDataDTO) | Profil complet centrală (wizard finish + validate) |
| **`BasicPlantDataDTO`** | `id`, `powerPlantId`, `capacity`, `constructionDurationYears`, `description`, `createdAt`, `updatedAt` | Date generale |
| **`GeologicalPlantDataDTO`** | `id`, `powerPlantId`, `country`, `latitude`, `longitude`, `soilType`, `waterSourceType`, `seismicStability`, `floodRisk`, `groundwaterLevel`, `waterProximity`, `waterFlowRate`, `populationDensity`, `transportInfrastructureScore`, `geologicalRiskScore`, `createdAt`, `updatedAt` | Date geologice |
| **`TechnicalPlantDataDTO`** | `id`, `powerPlantId`, `numberOfReactors`, `estimatedEfficiency`, `operationalRiskLevel`, `safetySystems`, `reactorConfigs`, `createdAt`, `updatedAt` | Date tehnice + configurații reactoare |
| **`FeasibilityReportDTO`** | `reportId`, `status`, `nsviScore`, `deficiencies`, `errors`, `message`, `createdAt` | Raport de fezabilitate |
| **`ReactorListDTO`** | `id`, `reactorCode`, `reactorType`, `coolingType`, `operationalStatus`, `thermalPowerMw`, `electricalPowerMw` | Listă reactoare |
| **`ReactorDetailsDTO`** | `id`, `powerPlantId`, `reactorCode`, `reactorType`, `coolingType`, `operationalStatus`, `thermalPowerMw`, `electricalPowerMw`, `fuelCycleDays`, `currentCycleDay`, `wearIndex`, `designLifetimeYr`, `commissioningDate`, `firstCriticality`, `lastInspectionAt`, `nextPlannedOutage`, `description`, `createdAt` | Detalii complete reactor |
| **`ReactorStreamDTO`** | `id`, `sensors`, `timestamp` | Date SSE live |
| **`SensorListDTO`** | `id`, `code`, `type`, `location`, `status`, `unit` | Listă senzori |
| **`SensorDetailsDTO`** | `id`, `reactorId`, `sensorCode`, `sensorType`, `description`, `locationZone`, `unitOfMeasure`, `measurementField`, `normalMin`, `normalMax`, `alarmLow`, `alarmHigh`, `alertLow`, `alertHigh`, `scramLow`, `scramHigh`, `status`, `isActive`, `lastCalibration`, `calibrationDue`, `createdAt` | Detalii complete senzor |
| **`NotificationDTO`** | `id`, `type`, `severity`, `title`, `message`, `date`, `targetRole`, `targetEmail` | Notificări |
| **`AlertListDTO`** | `id`, `plantId`, `plantName`, `type`, `severity`, `message`, `isRead`, `createdAt` | Alerte |
| **`LogListDTO`** | `id`, `reactorId`, `action`, `details`, `performedBy`, `createdAt` | Loguri reactor |
| **`UserDTO`** | `id`, `username`, `email`, `role`, `firstName`, `lastName` | User (fără parolă) |
| **`UserAuthDTO`** | extends UserDTO + `passwordHash` | Date complete user (backend intern) |
| **`CoordinatesPreviewResponseDTO`** | `latitude`, `longitude`, `label`, `country`, `geologicalPreview` | Previzualizare coordonate |
| **`GeoLocationPreviewDTO`** | `soilType`, `waterSourceType`, `seismicStability`, `floodRisk`, `groundwaterLevel`, `waterProximity`, `waterFlowRate`, `populationDensity` | Previzualizare date geologice |

---

## 3. Frontend DTOs

DTO-urile din frontend sunt simple **funcții de transformare** (nu clase). Rolul lor:

1. **Normalizare date** — convertesc valorile din formular (string-uri) în tipuri corecte (number, boolean, null)
2. **Validare implicită** — prin `|| null`, `parseFloat()`, `parseInt()` elimină valorile invalide
3. **Aplatizare obiecte imbricate** — `GetPlantDTO` extrage datele din structura `{ details: {...}, basic: {...}, geological: {...}, technical: {...} }` și le pune într-un singur obiect plat
4. **Mapare nume câmpuri** — convertește între convenția backend (snake_case in DB → camelCase in JSON) și ce folosește frontend-ul

### 3.1 Request DTOs (frontend → backend)

| DTO | Input (formular) | Output (JSON trimis la API) |
|---|---|---|
| **`PlantRequestDTO`** | `{ name }` | `{ name }` |
| **`BasicDataRequestDTO`** | `{ capacity, constructionDurationYears, description }` | `{ capacity: float, constructionDurationYears: int, description }` |
| **`GeologicalDataRequestDTO`** | `{ soilType, waterSourceType, seismicStability, floodRisk, …, latitude, longitude }` | Toate câmpurile cu `parseFloat` unde e cazul, `|| null` altfel |
| **`TechnicalDataRequestDTO`** | `{ numberOfReactors, estimatedEfficiency, operationalRiskLevel, reactorConfigurations[] }` | Convertit la numere, `reactorConfigurations` pasat direct |
| **`ReactorDTO`** | Formular reactor (16 câmpuri) | Toate câmpurile cu `parseFloat`/`parseInt` după tip; `|| null` pentru string-uri |
| **`UpdatePlantStatusRequestDTO`** | `{ status }` | `{ status }` |
| **`SensorRequestDTO`** | Formular senzor (21 câmpuri) | Build dinamic: adaugă doar câmpurile definite, `parseFloat` pentru threshold-uri |

### 3.2 Response DTOs (backend → frontend)

| DTO | Input (JSON de la API) | Output (obiect plat pentru UI) |
|---|---|---|
| **`GetPlantDTO`** | `{ details: {...}, basic: {...} || null, geological: {...} || null, technical: {...} || null }` | `{ id, name, country, capacity, soilType, …, reactorConfigurations }` — un singur nivel |
| **`FeasibilityReportDTO`** | `{ reportId, status, nsviScore, deficiencies[], errors[], message, createdAt }` | Același, cu `|| []` pentru array-uri, `|| null` pentru scalari |
| **`PlantListResponseDTO`** | `{ id, name, country, latitude, longitude, status }` | `parseFloat` pe coordonate |

### 3.3 Unde se aplică DTO-urile

```
Form submit handler (ex: basics.js)
    ↓
DTO de request (ex: BasicDataRequestDTO({ capacity, ... }))
    ↓
Service layer (ex: powerPlantService.createBasics(dto, plantId))
    ↓  api.post(endpoint, dto)
Backend → json_encode(răspuns) → JSON
    ↓  api.js parsează răspunsul
Service returnează JSON brut (ex: { data: { basicsId: ... } })
    ↓
Page consumer folosește direct datele (ex: saveHeaderState({ basicsId: response.basicsId }))
```

**Pentru response-uri complexe**, DTO-ul se aplică în UI renderer:
```
api.get(...) → JSON brut
    ↓
DTO de response (ex: FeasibilityReportDTO(data))
    ↓
UI renderer (ex: populateFeasibilityReport(dto))
    ↓
DOM manipulation
```

---

## 4. Mapping Backend ↔ Frontend

### 4.1 Exemplu complet: Wizard-ul de creare centrală

```
CREATE FLOW:
┌─────────────┐     ┌─────────────────┐     ┌──────────────────┐
│  create.html │────▶│  basics.html    │────▶│ geological.html  │
│              │     │                 │     │                  │
│ plantRequest │     │ BasicDataReqDTO │     │ GeologicalReqDTO │
│ POST /plants │     │ POST /basics    │     │ POST /geological │
└─────────────┘     └─────────────────┘     └────────┬─────────┘
                                                      │
                                                      ▼
┌─────────────┐     ┌─────────────────┐              │
│  finish.html │◀────│ technical.html  │◀──────────────┘
│              │     │                 │
│ GET /plant   │     │ TechnicalReqDTO │
│ GetPlantDTO  │     │ POST /technical │
└─────────────┘     └─────────────────┘
```

**Backend** construiește răspunsul `GetPlantDTO` prin `PlantServiceFacade::getCompletePlantProfile()` care asamblează datele din 4 repository-uri diferite (details, basic, geological, technical) și le împachetează într-un `GetPlantDTO` PHP.

**Frontend** primește JSON-ul și îl normalizează cu `GetPlantDTO(data)` în `plantPageRenderer.js` înainte de a-l folosi în DOM.

### 4.2 Particularități

| Aspect | Backend | Frontend |
|---|---|---|
| **Convenție nume** | `camelCase` în PHP (`$estimatedEfficiency`) | `camelCase` în JS (`estimatedEfficiency`) |
| **Serializare** | `jsonSerialize()` → `get_object_vars($this)` | JSON nativ |
| **Valori nule** | `?float` / `?string` → `null` | `?? null` / `|| null` |
| **Enum-uri** | `$enum->value` (ex: `PlantStatus::DRAFT->value`) | String-uri direct (ex: `"DRAFT"`) |
| **Array-uri** | `array` PHP → JSON array | `|| []` pentru defaultValue |
| **Date** | `DateTime` → string prin `jsonSerialize()` | Păstrate ca string |

---

## 5. Endpoint-uri API și DTO-urile implicate

| Endpoint | Method | Request DTO | Response DTO |
|---|---|---|---|
| `/api/power-plants` | GET | — | `ApiResponseDTO { data: PlantListDTO[] }` |
| `/api/power-plants/my` | GET | — | `ApiResponseDTO { data: PlantListDTO[] }` |
| `/api/power-plants` | POST | `PlantRequestDTO` | `{ status, message, plantId }` |
| `/api/power-plants/{id}` | GET | — | `GetPlantDTO` (composite) |
| `/api/power-plants/{id}/details` | PUT | `PlantRequestDTO` | `ApiResponseDTO` |
| `/api/power-plants/{id}/basics` | GET | — | `ApiResponseDTO { data: BasicPlantDataDTO }` |
| `/api/power-plants/{id}/basics` | POST | `BasicDataRequestDTO` | `{ status, basicsId, plantId }` |
| `/api/power-plants/{id}/basics` | PUT | `BasicDataRequestDTO` | `ApiResponseDTO` |
| `/api/power-plants/{id}/geological` | GET | — | `ApiResponseDTO { data: GeologicalPlantDataDTO }` |
| `/api/power-plants/{id}/geological` | POST | `GeologicalDataRequestDTO` | `{ status, geologicalId, plantId }` |
| `/api/power-plants/{id}/geological` | PUT | `GeologicalDataRequestDTO` | `ApiResponseDTO` |
| `/api/power-plants/{id}/technical` | GET | — | `ApiResponseDTO { data: TechnicalPlantDataDTO }` |
| `/api/power-plants/{id}/technical` | POST | `TechnicalDataRequestDTO` | `{ status, technicalId, plantId }` |
| `/api/power-plants/{id}/technical` | PUT | `TechnicalDataRequestDTO` | `ApiResponseDTO` |
| `/api/power-plants/{id}/feasibility` | POST | — | `{ success, message }` |
| `/api/power-plants/{id}/feasibility` | GET | — | `{ success, data: FeasibilityReportDTO }` |
| `/api/power-plants/{plantId}/reactors` | GET | — | `{ status, data: ReactorListDTO[] }` |
| `/api/reactors` | POST | `ReactorDTO` | `{ status, data }` |
| `/api/reactors/{id}` | GET | — | `{ status, data: ReactorDetailsDTO }` |
| `/api/reactors/{id}` | PUT | `ReactorDTO` | `{ status, message }` |
| `/api/reactors/{id}` | DELETE | — | `{ status, message }` |

---

## 6. Probleme identificate și recomandări

### 6.1 Problema: Frontend DTO-urile nu sunt sincronizate cu backend DTO-urile

**Exemplu:** `PlantListResponseDTO.js` pe frontend ignoră câmpurile `createdBy`, `createdAt`, `updatedAt` care sunt prezente în `PlantListDTO.php` pe backend.

**Risc:** Când un consumator nou are nevoie de `createdBy`, va trebui să citească direct din `response.data[i].createdBy` în loc să treacă printr-un DTO.

**Recomandare:** Actualizați `PlantListResponseDTO` să includă toate câmpurile din `PlantListDTO`:
```js
export function PlantListResponseDTO({ id, name, country, latitude, longitude, status, createdBy, createdAt, updatedAt }) { 
    return { 
        id, name, country, 
        latitude: parseFloat(latitude), 
        longitude: parseFloat(longitude), 
        status, createdBy, createdAt, updatedAt 
    }; 
}
```

### 6.2 Problema: Frontend DTO-urile nu sunt folosite consecvent

- `PlantListResponseDTO` este definit dar **nu este importat** în niciun consumator. `list.js`, `admin/index.js` și `my-plants.js` citesc direct din `response.data[i]`.
- `FeasibilityReportDTO` este folosit doar în `feasibilityReportRenderer.js`.
- `ReactorDTO` este aplicat corect în `reactorService.createReactor` și `updateReactor`.
- `SensorRequestDTO` este folosit în `sensors.js`.

**Recomandare:** Aplicați DTO-urile consecvent la intrarea datelor în frontend (după `api.get(...)`) pentru a normaliza tipurile și a preveni erori de acces la proprietăți `null`/`undefined`.

### 6.3 Problema: Răspunsurile de creare POST nu au un DTO standardizat

Endpoint-urile de creare (`/basics`, `/geological`, `/technical`) nu returnează un `ApiResponseDTO` standard, ci un obiect JSON ad-hoc:
```json
{ "status": "success", "basicsId": "...", "plantId": "..." }
```

**Recomandare:** Standardizați răspunsurile POST să folosească `ApiResponseDTO { status, data: CreateDataResponseDTO }`.

### 6.4 Problema: Mapare reactoare — `reactorConfigurations` vs `reactorConfigs`

Backend `TechnicalPlantDataDTO` expune `reactorConfigs` (când e serializat din `fromEntity`), dar frontend `GetPlantDTO` așteaptă `reactorConfigurations` (linia 32: `data.technical?.reactorConfigs ?? []`). De fapt, `GetPlantDTO.js` citește `data.technical?.reactorConfigs`, deci mapping-ul e corect în cod, dar denumirea diferită de variabila din `fromEntity` (`$formattedReactorConfigs`) este confuză.

**Recomandare:** Aliniați numele — `reactorConfigurations` peste tot pentru claritate.

### 6.5 Problema: Consistență pe GET /api/power-plants/{id}

Endpoint-ul returnează un `GetPlantDTO` PHP fără înveliș `ApiResponseDTO`:
```json
{ "details": {...}, "basic": {...}, "geological": {...}, "technical": {...} }
```
În schimb, `GET /api/power-plants/{id}/details` returnează `ApiResponseDTO { status, data: PlantDetailsDTO }`.

**Recomandare:** Înveliți `GetPlantDTO` în `ApiResponseDTO` pentru consistență cu restul API-urilor.

---

## 7. Concluzii

### Puncte tari
- Arhitectură三层 clară: **Controller → Service → Repository** pe backend, **Page → Service → api.js** pe frontend
- Backend DTO-urile sunt bine structurate, cu `readonly` properties și factory methods (`fromEntity`, `fromDbArray`)
- Frontend api.js oferă un layer de transport robust cu CSRF, error handling și logging
- Serviciile sunt bine separate pe domenii (powerPlant, reactor, notification, feasibility)

### Puncte slabe
- Frontend DTO-urile sunt slab integrate (deseori nu se folosesc, datele se consumă direct din JSON)
- Răspunsurile POST nu respectă același format ca restul API-urilor
- `GetPlantDTO` nu e învelit în `ApiResponseDTO`
- Maparea numelor de câmpuri între backend și frontend nu este verificată automat

### Recomandări prioritare

1. **Adăugați `ApiResponseDTO` la GET /api/power-plants/{id}** — consistență
2. **Standardizați răspunsurile POST** — toate să returneze `{ status, data: { id } }`
3. **Folosiți `PlantListResponseDTO` în `list.js`, `admin/index.js`, `my-plants.js`** — normalizare tipuri
4. **Extindeți `PlantListResponseDTO`** cu `createdBy`, `createdAt`, `updatedAt`
5. **Adăugați test automat de contract** — un script care verifică că fiecare backend DTO are un frontend DTO corespondent cu aceleași câmpuri
