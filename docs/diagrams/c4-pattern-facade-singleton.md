# Diagrama C4 — Pattern: Facade + Singleton

## Fațada serviciilor de centrale

```mermaid
classDiagram
  class PlantServiceFacade {
    - DetailsPlantService detailsService
    - BasicPlantService basicService
    - GeologicalPlantService geologicalService
    - TechnicalPlantService technicalService
    + savePlantDetails(array data) CreateDataResponseDTO
    + getCompletePlantProfile(string plantId) array
    + submitForReview(string plantId, string userId) bool
    + reopenDraft(string plantId, string userId) bool
    + getAllPowerPlants() array
    + getMyPowerPlants(string userId) array
    + getPendingApprovalsList() array
    + getPlantsByStatus(array data) array
    + getCountries() array
    + previewCoordinates(float lat, float lon) CoordinatesPreviewResponseDTO
    + saveBasicData(array data, string plantId) CreateDataResponseDTO
    + getGeologicalDataByPlantId(string plantId) GeologicalPlantDataDTO
    + previewGeologicalLocation(float lat, float lon) GeoLocationPreviewDTO
    + getTechnicalDataByPlantId(string plantId) TechnicalPlantDataDTO
    + updatePlantDetails(array data, string plantId) void
    + updatePlantStatus(string plantId, string status) void
  }

  class DetailsPlantService {
    + savePlantDetails(array data) CreateDataResponseDTO
    + getAllPowerPlants() array
    + getMyPowerPlants(string userId) array
    + findById(string plantId) Plant
  }

  class BasicPlantService {
    + findByPlantId(string plantId) BasicPlantData
    + save(array data, string plantId) void
    + update(array data, string plantId) void
  }

  class GeologicalPlantService {
    + findByPlantId(string plantId) GeologicalPlantData
    + runAutoGeolocation(float lat, float lon) GeoLocationPreviewDTO
    + save(array data, string plantId) void
    + update(array data, string plantId) void
  }

  class TechnicalPlantService {
    + findByPlantId(string plantId) TechnicalPlantData
    + save(array data, string plantId) void
    + update(array data, string plantId) void
  }

  PlantServiceFacade o--> DetailsPlantService
  PlantServiceFacade o--> BasicPlantService
  PlantServiceFacade o--> GeologicalPlantService
  PlantServiceFacade o--> TechnicalPlantService
```

## Fațada repository-urilor

```mermaid
classDiagram
  class PlantRepositoryFacade {
    - DetailsPlantRepository detailsRepo
    - BasicPlantRepository basicRepo
    - GeologicalPlantRepository geologicalRepo
    - TechnicalPlantRepository technicalRepo
    + findAll() array
    + findById(string plantId) Plant
    + findByUser(string userId) array
    + getPlantsByStatus(array data) array
    + findBasicByPlantId(string plantId) BasicPlantData
    + findGeologicalByPlantId(string plantId) GeologicalPlantData
    + findTechnicalByPlantId(string plantId) TechnicalPlantData
    + savePlant(Plant plant) void
    + saveBasic(BasicPlantData data) void
    + saveGeological(GeologicalPlantData data) bool
    + saveTechnical(TechnicalPlantData data) void
    + getPlantData(string plantId) array
  }

  class DetailsPlantRepository {
    + findAll() array
    + findById(string plantId) Plant
    + findByUser(string userId) array
    + save(Plant plant) void
    + updateStatus(array data, string plantId) void
  }

  class BasicPlantRepository {
    + findByPlantId(string plantId) BasicPlantData
    + save(BasicPlantData data) void
    + update(BasicPlantData data) void
  }

  class GeologicalPlantRepository {
    + findByPlantId(string plantId) GeologicalPlantData
    + save(GeologicalPlantData data) bool
    + update(GeologicalPlantData data) void
  }

  class TechnicalPlantRepository {
    + findByPlantId(string plantId) TechnicalPlantData
    + save(TechnicalPlantData data) void
    + update(TechnicalPlantData data) void
    + getSchemasByTechnicalDataId(string id) array
  }

  PlantRepositoryFacade o--> DetailsPlantRepository
  PlantRepositoryFacade o--> BasicPlantRepository
  PlantRepositoryFacade o--> GeologicalPlantRepository
  PlantRepositoryFacade o--> TechnicalPlantRepository
```

## Singleton — LogService și Database

```mermaid
classDiagram
  class LogService {
    <<singleton>>
    - static LogService instance
    - LogRepository repository
    - float lastCleanup
    + static init(PDO pdo) void
    + static instance() LogService
    + log(string level, string message, array context, ...) void
    + info(string message, array context) void
    + warning(string message, array context) void
    + error(string message, array context) void
    + critical(string message, array context) void
    + debug(string message, array context) void
    + logFromFrontend(string level, string message, array context, string userId) void
    - maybeCleanup() void
  }

  class Database {
    <<singleton>>
    - static ?PDO instance
    + static getConnection(array config) PDO
  }
```

## Cum funcționează fațadele

```
  Controller
      │
      ▼
  PlantServiceFacade (un singur punct de acces)
      │
      ├──→ DetailsPlantService (nume, status)
      ├──→ BasicPlantService (capacitate, durată)
      ├──→ GeologicalPlantService (coordonate, sol, seismic)
      └──→ TechnicalPlantService (eficiență, configurații reactoare)
              │
              ▼
  PlantRepositoryFacade (un singur punct de acces date)
      │
      ├──→ DetailsPlantRepository (tabela power_plants)
      ├──→ BasicPlantRepository (tabela basic_data)
      ├──→ GeologicalPlantRepository (tabela geological_data)
      └──→ TechnicalPlantRepository (tabela technical_data + reactor_plant_data)
              │
              ▼
  PostgreSQL (18 tabele)
```

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
