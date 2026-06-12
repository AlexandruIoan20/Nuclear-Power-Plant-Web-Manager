# Raport Refactor DTO — Backend Nuclear Power Plant

## 1. Obiectiv

Eliminarea completă a logicii de construire inline a răspunsurilor (`['key' => $value, ...]`) în controllere/ser vicii și înlocuirea cu DTO-uri tipizate, urmând modelul `ReactorStreamDTO.php`.

---

## 2. Starea Curentă

### 2.1 DTO-uri existente (14 fișiere în `src/Dto/`)

| DTO | Stil | Observații |
|---|---|---|
| `ReactorStreamDTO` + `StreamSensorDTO` | Public props, `fromEntity()` / `create()` | Exemplu de urmat — DTO-uri înnested |
| `BasicPlantDataDTO` | Constructor promotion `readonly`, `fromEntity()` | Curat, folosește named arguments |
| `GeologicalPlantDataDTO` | Constructor promotion `readonly`, `fromEntity()` | Curat |
| `TechnicalPlantDataDTO` | Constructor promotion `readonly`, `fromEntity()` | Primește și `array $configs` |
| `PlantDTO` | Public props, constructor manual, `fromEntity()` | Mixt |
| `PlantDetailsDTO` | Public props, constructor manual, `fromEntity()` + `fromRequest()` | Mixt |
| `ReactorDetailsDTO` | Public props, `fromEntity()` | Stil vechi |
| `ReactorListDTO` | Public props, `fromEntity()` | Stil vechi |
| `SensorDetailsDTO` | Public props, `fromEntity()` | Stil vechi |
| `SensorListDTO` | Public props, `fromEntity()` | Stil vechi |
| `SensorTemplateDTO` | Public props, `fromEntity()` | Stil vechi |
| `GetPlantDTO` | Constructor promotion `readonly`, `fromServiceArray()` | DTO compozit |
| `CreateDataResponseDTO` | Public prop + constructor | DTO generic pentru create |
| `FeasibilityReportDTO` | Constructor promotion `readonly`, `fromResult()` + `fromDatabase()`, **`implements JsonSerializable`** | Singurul cu serializare explicită |

### 2.2 Inconsistențe identificate

1. **Stil mixt** — unele DTO-uri folosesc constructor promotion (`readonly`), altele public properties asignate manual
2. **Fără clasă de bază** — niciun DTO nu extinde o clasă generică `BaseDTO` / `AbstractDTO`
3. **Fără interfață comună** — doar `FeasibilityReportDTO` implementează `JsonSerializable`
4. **Fără namespacing** — toate clasele sunt în namespace-ul global, se folosesc `require_once`
5. **`fromEntity()` vs constructor direct** — pattern-uri diferite de creare
6. **Lipsă `jsonSerialize()`** — majoritatea DTO-urilor sunt echate direct cu `echo json_encode($dto)`, ceea ce funcționează doar pentru proprietăți publice; nu există control asupra numelor câmpurilor

---

## 3. Locurile unde se construiesc array-uri inline

### 3.1 Controllere — construcție directă în răspuns

| Controller | Metodă | Linii | Structura construită |
|---|---|---|---|
| `AlertController` | `getUnread()` | 50–58 | `['id', 'plant_id', 'type', 'message', 'created_at']` din `Alert` entity |
| `DetailsPlantController` | `getPowerPlantsList()` | 62–76 | `['id', 'name', 'country', 'latitude', 'longitude', 'status', 'created_by', 'created_at', 'updated_at']` |
| `DetailsPlantController` | `getPendingApprovalsList()` | 88–106 | Aceeași structură ca mai sus |
| `DetailsPlantController` | `getPlantsByStatus()` | 137–146 | `['id', 'name', 'status', 'created_by', 'created_at', 'updated_at']` |
| `DetailsPlantController` | `getPowerPlantsMapData()` | 164–183 | Aceleași câmpuri + `has_coordinates`, `coordinates_label`, `popup_title`, `popup_subtitle`, `edit_url` |
| `DetailsPlantController` | `previewCoordinates()` | 406–433 | Răspuns mixed cu coordonate + câmpuri geologice |
| `LogController` | `getLogs()` | 24–37 | `['id', 'level', 'message', 'context', 'user_id', 'plant_id', 'reactor_id', 'source', 'request_uri', 'ip_address', 'created_at']` din `Log` entity |
| `UserController` | `getUserStatus()` | 225–234 | `['id', 'username', 'email', 'role', 'first_name', 'last_name']` din array DB |
| `FeasibilityController` | `generate()` | 14 | Returnează `$response` (array) direct din service |
| `FeasibilityController` | `getLastByPlantId()` | 26 | Returnează `$response` (array) direct din service |

### 3.2 Ser vicii — returnează array-uri în loc de DTO-uri

| Service | Metodă | Problema |
|---|---|---|
| `UserService` | `authenticateUser()` | Returnează array DB direct (`$user`) |
| `UserService` | `getUserById()` | Returnează `?array` din repository |
| `UserService` | `getAllUsersForAdmin()` | Returnează array de array-uri |
| `NotificationService` | `getAggregatedNotifications()` | Construiește array-uri de notificări manual (2 structuri diferite: alert vs approval) |
| `GeologicalPlantService` | `runAutoGeolocation()` | Returnează array cu `['soilType', 'waterSourceType', ...]` |
| `DetailsPlantService` | `getAllPowerPlants()` | Returnează raw array-uri din repository (folosit de 4 controllere) |
| `DetailsPlantService` | `getPlantsByStatus()` | Returnează raw array-uri |
| `PlantServiceFacade` | `getAllPowerPlants()` | Proxy către DetailsPlantService, același raw array |
| `PlantServiceFacade` | `getPendingApprovalsList()` | Filtrare pe raw array-uri |
| `PlantServiceFacade` | `getPlantsByStatus()` | Proxy către DetailsPlantService |
| `PlantServiceFacade` | `previewGeologicalLocation()` | Proxy către `runAutoGeolocation()` |
| `FeasibilityService` | `generateAndSaveReport()` | Returnează `['success' => bool, 'message' => string, 'data' => ?DTO]` |
| `FeasibilityService` | `getFeasibilityReport()` | Aceeași structură response envelope |

### 3.3 Repository-uri — sursa primară de array-uri brute

| Repository | Metode care returnează array-uri |
|---|---|
| `UserRepository` | `findByEmail()`, `findById()`, `findAllForAdmin()` |
| `DetailsPlantRepository` | `getAllPowerPlants()`, `getPlantsByStatus()` |
| `PlantRepositoryFacade` | `getAllPowerPlants()`, `getPlantsByStatus()`, `getPendingApprovalsList()` |

---

## 4. Propunere: ierarhie DTO

### 4.1 DTO generic de bază

```php
// src/Dto/BaseDTO.php
class BaseDTO implements JsonSerializable {
    public function jsonSerialize(): array {
        $vars = get_object_vars($this);
        return $vars;
    }
}
```

Acesta asigură:
- Serializare implicită prin `get_object_vars()` — toate propritățile publice
- Poate fi suprascris în DTO-uri care necesită nume diferite de câmpuri (`FeasibilityReportDTO` deja face asta)
- `readonly` nu e necesar la bază — fiecare DTO decide

### 4.2 DTO-uri de răspuns (Response Envelope)

```php
// src/Dto/ApiResponseDTO.php
class ApiResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,   // "success" | "error"
        public readonly mixed $data = null,
        public readonly ?string $message = null,
    ) {}
}
```

Acesta înlocuiește pattern-ul `["status" => "success", "data" => $xxx, "message" => $xxx]` peste tot.

### 4.3 DTO-uri pentru date noi (create)

`CreateDataResponseDTO` există deja — se poate extinde pentru a include `message`:

```php
class CreateDataResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $dataId,
        public readonly string $message = 'Creat cu succes.',
    ) {}
}
```

---

## 5. Plan de implementare pe pași

### Pasul 0 — Fundația (pregătire)

- [ ] Creează `src/Dto/BaseDTO.php` — clasă abstractă cu `jsonSerialize()` și eventual `fromEntity()` ca metodă abstractă
- [ ] Creează `src/Dto/ApiResponseDTO.php` — response envelope generic
- [ ] Migrează DTO-urile existente să **extindă** `BaseDTO`
- [ ] Standardizează stilul:
  - **Decizie**: constructor promotion cu `readonly` (ca la `BasicPlantDataDTO`) vs public props (ca la `ReactorDetailsDTO`)
  - **Recomandare**: constructor promotion `readonly` + named arguments — mai curat, imutabil, lizibil
- [ ] Refactor `require_once` în DTO-uri să ceară doar entitatea necesară (deja se face)

### Pasul 1 — DTO-uri de listare (Plant list, Alert list, Log list, User list)

Plants:
- [ ] Creează `PlantListDTO extends BaseDTO` — conține `id`, `name`, `country`, `latitude`, `longitude`, `status`, `created_by`, `created_at`, `updated_at` + factory `fromDbArray(array $row)`
- [ ] Creează `PlantMapDTO extends PlantListDTO` — adaugă `has_coordinates`, `coordinates_label`, `popup_title`, `popup_subtitle`, `edit_url`
- [ ] Creează `PlantStatusListDTO extends BaseDTO` — variantă simplă: `id`, `name`, `status`, `created_by`, `created_at`, `updated_at`

Alerts:
- [ ] Creează `AlertListDTO extends BaseDTO` — `id`, `plant_id`, `type`, `message`, `created_at` + `fromEntity(Alert $a)`

Logs:
- [ ] Creează `LogListDTO extends BaseDTO` — toate câmpurile din maparea actuală + `fromEntity(Log $log)`

Users:
- [ ] Creează `UserDTO extends BaseDTO` — `id`, `username`, `email`, `role`, `first_name`, `last_name`
- [ ] Creează `UserAuthDTO extends UserDTO` — include și `password_hash` (pentru authentificare internă, nu se expune)

### Pasul 2 — DTO-uri compozite

- [ ] Creează `NotificationDTO extends BaseDTO` — structura unei notificări: `id`, `type`, `severity`, `title`, `message`, `date`, `target_role`, `target_email`
- [ ] Creează `GeoLocationPreviewDTO extends BaseDTO` — toate câmpurile geologice din `runAutoGeolocation()`
- [ ] Creează `CoordinatesPreviewResponseDTO extends BaseDTO` — răspunsul complet de la `previewCoordinates()`

### Pasul 3 — Refactor controllere

Pentru fiecare controller, înlocuiește maparea inline cu DTO:

- [ ] **AlertController::getUnread()** — înlocuiește `array_map` cu `array_map(fn($a) => AlertListDTO::fromEntity($a), $alerts)`
- [ ] **DetailsPlantController::getPowerPlantsList()** — înlocuiește construcția manuală cu `PlantListDTO::fromDbArray()`
- [ ] **DetailsPlantController::getPendingApprovalsList()** — același DTO
- [ ] **DetailsPlantController::getPlantsByStatus()** — `PlantStatusListDTO::fromDbArray()`
- [ ] **DetailsPlantController::getPowerPlantsMapData()** — `PlantMapDTO::fromDbArray()`
- [ ] **DetailsPlantController::previewCoordinates()** — `CoordinatesPreviewResponseDTO`
- [ ] **LogController::getLogs()** — `LogListDTO::fromEntity()`
- [ ] **UserController::getUserStatus()** — `UserDTO::fromDbArray()`

### Pasul 4 — Refactor ser vicii

- [ ] **UserService** — încarcă `User` entity în loc de array DB; returnează `UserDTO` din `authenticateUser()` și `getUserById()`
- [ ] **NotificationService** — folosește `AlertListDTO` și `PlantListDTO` în loc de array-uri manuale
- [ ] **GeologicalPlantService** — returnează `GeoLocationPreviewDTO` din `runAutoGeolocation()`
- [ ] **FeasibilityService** — returnează `ApiResponseDTO` în loc de array-uri `['success' => ..., 'message' => ..., 'data' => ...]`
- [ ] **DetailsPlantService** — returnează array de `PlantListDTO` din `getAllPowerPlants()` și `getPlantsByStatus()`

### Pasul 5 — Refactor repository-uri (opțional, benefic)

- [ ] `DetailsPlantRepository::getAllPowerPlants()` / `getPlantsByStatus()` — returnează array-uri de obiecte (poate rămâne raw array dacă logica de transformare e la nivel de service)
- [ ] `UserRepository::findByEmail()` / `findById()` — returnează entity `User` în loc de array

### Pasul 6 — Standardizare response envelope peste tot

- [ ] În toate controllerele, înlocuiește:
  ```php
  echo json_encode(["status" => "success", "data" => $data]);
  ```
  cu:
  ```php
  echo json_encode(new ApiResponseDTO('success', data: $data));
  ```

### Pasul 7 — Curățenie

- [ ] Elimină DTO-urile care devin redundante (verifică `PlantDetailsDTO` vs `PlantDTO`)
- [ ] Unifică `PlantDTO` și `PlantDetailsDTO` — practic același lucru
- [ ] Asigură-te că toate endpoint-urile returnează aceleași nume de câmpuri (frontend să nu se strice):
  - Folosește `#[JsonSerialize('snake_case')]` sau suprascrie `jsonSerialize()` acolo unde FE așteaptă `plant_id` vs `plantId`

### Pasul 8 — Testare

- [ ] Verifică fiecare endpoint cu răspunsul vechi vs nou (compară JSON)
- [ Rulează testele existente / scripturile de test
- [ ] Verifică manual flow-urile principale: login, create plant, add data, aprobare, stream senzori

---

## 6. Matrice DTO-uri noi de creat

| DTO Nou | Extinde | Câmpuri principale | Factory method | Folosit în |
|---|---|---|---|---|
| `BaseDTO` | — | `jsonSerialize()` | — | Toate DTO-urile |
| `ApiResponseDTO` | `BaseDTO` | `status`, `data`, `message` | Constructor | Toate controllerele |
| `PlantListDTO` | `BaseDTO` | `id`, `name`, `country`, `latitude`, `longitude`, `status`, `created_by`, `created_at`, `updated_at` | `fromDbArray()` | `getPowerPlantsList()`, `getPendingApprovalsList()` |
| `PlantMapDTO` | `PlantListDTO` | + `has_coordinates`, `coordinates_label`, `popup_title`, `popup_subtitle`, `edit_url` | `fromDbArray()` | `getPowerPlantsMapData()` |
| `PlantStatusListDTO` | `BaseDTO` | `id`, `name`, `status`, `created_by`, `created_at`, `updated_at` | `fromDbArray()` | `getPlantsByStatus()` |
| `AlertListDTO` | `BaseDTO` | `id`, `plant_id`, `type`, `message`, `created_at` | `fromEntity()` | `getUnread()` |
| `LogListDTO` | `BaseDTO` | `id`, `level`, `message`, `context`, `user_id`, `plant_id`, `reactor_id`, `source`, `request_uri`, `ip_address`, `created_at` | `fromEntity()` | `getLogs()` |
| `UserDTO` | `BaseDTO` | `id`, `username`, `email`, `role`, `first_name`, `last_name` | `fromEntity()` / `fromDbArray()` | `getUserStatus()`, `adminGetUser()`, `adminListUsers()` |
| `NotificationDTO` | `BaseDTO` | `id`, `type`, `severity`, `title`, `message`, `date`, `target_role`, `target_email` | Constructor | `getAggregatedNotifications()` |
| `GeoLocationPreviewDTO` | `BaseDTO` | `soilType`, `waterSourceType`, `seismicStability`, `floodRisk`, `groundwaterLevel`, `waterProximity`, `waterFlowRate`, `populationDensity`, `transportInfrastructureScore` | `fromArray()` | `runAutoGeolocation()` |
| `CoordinatesPreviewResponseDTO` | `BaseDTO` | `latitude`, `longitude`, `coordinates_label`, `country` + câmpurile geologice | `fromGeoPreview()` | `previewCoordinates()` |

---

## 7. Riscuri și considerații

| Risc | Impact | Mitigare |
|---|---|---|
| Frontend depinde de numele exacte ale câmpurilor | Mare | Compară JSON-ul vechi cu cel nou; dacă FE așteaptă `plant_id` dar DTO are `plantId`, suprascrie `jsonSerialize()` |
| DTO-urile cu `readonly` nu pot fi modificate după creare | Mediu | Folosește constructor promotion; pentru cazuri complexe, folosește named arguments |
| Performanță — DTO-uri în plus = mai multe obiecte | Scăzut | Diferența e neglijabilă; beneficiul de tipizare și mentenanță e mult mai mare |
| `BaseDTO::jsonSerialize()` expune toate propritățile publice | Mediu | Asigură-te că proprietățile interne sunt `private`/`protected`; DTO-urile trebuie să aibă doar câmpurile publice pe care vrem să le expunem |
| Refactor masiv → risc de regresii | Mare | Fă fiecare pas pe rând; testează după fiecare DTO introdus; compară răspunsurile JSON vechi vs noi |

---

## 8. Ordinea recomandată de implementare

```
Pas 0 (Fundație)
  └── BaseDTO + ApiResponseDTO + standardizare stil

Pas 1 (DTO-uri simple)
  ├── PlantListDTO
  ├── AlertListDTO
  ├── LogListDTO
  └── UserDTO

Pas 2 (DTO-uri compozite)
  ├── PlantMapDTO
  ├── NotificationDTO
  ├── GeoLocationPreviewDTO
  └── CoordinatesPreviewResponseDTO

Pas 3 (Refactor controllere)
  ├── AlertController
  ├── DetailsPlantController (4 metode)
  ├── LogController
  ├── UserController
  └── FeasibilityController

Pas 4 (Refactor ser vicii)
  ├── UserService
  ├── NotificationService
  ├── GeologicalPlantService
  ├── FeasibilityService
  └── DetailsPlantService / PlantServiceFacade

Pas 5 (Refactor repository-uri)
  ├── UserRepository
  └── DetailsPlantRepository

Pas 6 (Response envelope standardization)
  └── Toate controllerele

Pas 7 (Curățenie + unificare)
  └── Eliminare DTO-uri redundante + ensuring backward compat

Pas 8 (Testare)
  └── Verificare endpoint-uri + comparație JSON
```
