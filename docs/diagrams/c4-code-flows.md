# Diagrama C4 — Cod (Fluxuri de Date)

## 1. Creare centrală — Wizard DRAFT

```mermaid
sequenceDiagram
  participant B as Browser (SPA)
  participant R as Router
  participant A as API (fetch + CSRF)
  participant C as DetailsPlantController
  participant F as PlantServiceFacade
  participant S as DetailsPlantService
  participant Repo as DetailsPlantRepository
  participant DB as PostgreSQL

  B->>R: click "Crează centrală"
  R->>B: Încarcă create.html + create.js
  B->>B: Formular: nume centrală

  B->>A: POST /api/power-plants\n{ name: "Centrala X" }
  A->>C: JSON request + CSRF token
  C->>C: Validare input\n(name required, max 255)
  C->>F: savePlantDetails(data)
  F->>S: savePlantDetails(data)
  S->>Repo: INSERT power_plants\n(name, status=DRAFT,\ncreated_by=userId)
  Repo->>DB: PDO prepared statement
  DB-->>Repo: RETURNING id
  Repo-->>S: Plant entity with UUID
  S-->>F: CreateDataResponseDTO
  F-->>C: { dataId, plantId, message }
  C-->>A: 201 JSON { status: "success", plantId }
  A-->>B: Response JSON
  B->>B: Redirect wizard pas 2:\n/power-plants/{id}/basics
```

### Pașii următori (wizard):

```
Pas 2: Basic Data → POST /api/power-plants/{id}/basic
  → BasicPlantController → BasicPlantService → BasicPlantRepository
  → INSERT basic_data (capacity_mw, construction_duration_years, description)

Pas 3: Geological Data → POST /api/power-plants/{id}/geological
  → GeologicalPlantController → GeologicalPlantService
  → Opțional: runAutoGeolocation(lat, lon) → 4 API-uri externe
  → INSERT geological_data (country, lat, lon, soil, seismic, flood, etc.)

Pas 4: Technical Data → POST /api/power-plants/{id}/technical
  → TechnicalPlantController → TechnicalPlantService → TechnicalPlantRepository
  → INSERT technical_data + reactor_plant_data
  → Auto-generare reactoare + senzori din template-uri (tranzacție)
```

## 2. Studiu de fezabilitate — Algoritm NSVI

```mermaid
sequenceDiagram
  participant B as Browser
  participant C as FeasibilityController
  participant FS as FeasibilityService
  participant PR as PlantRepositoryFacade
  participant CoR as Chain of Responsibility
  participant FR as FeasibilityRepository
  participant DB as PostgreSQL

  B->>C: POST /api/power-plants/{id}/feasibility
  C->>FS: generateAndSaveReport(plantId)
  FS->>PR: getPlantData(plantId)
  PR->>DB: SELECT power_plants +\nJOIN geological_data +\nJOIN technical_data +\nJOIN reactor_plant_data +\nJOIN reactor_schema
  DB-->>PR: Complete plant profile
  PR-->>FS: array $plantData

  FS->>CoR: check(plantData, reactorTypes)
  
  Note over CoR: Lanț Chain of Responsibility
  
  CoR->>GeologicalCriticalChecker: check()
  GeologicalCriticalChecker->>GeologicalCriticalChecker: Verifică:\n- date geologice existente?\n- soilType în PEAT/SOFT_CLAY/LOOSE_SAND/SILT?\n- seismicStability < 4.0?\n- populationDensity > 500?\n- transportScore < 3.0?\n- waterFlowRate < 20?\n- floodRisk > 8.0?\n- groundwaterLevel < 2.0m?

  alt Erori critice găsite
    GeologicalCriticalChecker-->>CoR: REJECTED + erori
  else Fără erori
    GeologicalCriticalChecker->>TechnicalCriticalChecker: next.check()
    TechnicalCriticalChecker->>TechnicalCriticalChecker: Verifică:\n- date tehnice existente?\n- efficiency > 45% sau < 15%?\n- numberOfReactors < 1 sau > 8?
    
    alt Erori critice găsite
      TechnicalCriticalChecker-->>CoR: REJECTED + erori
    else Fără erori
      TechnicalCriticalChecker->>ScoringChecker: next.check()
      ScoringChecker->>ScoringChecker: Pentru fiecare tip reactor:\n  - Selectează ScoringStrategy (PWR/BWR/PHWR/FBR)\n  - Calculează scor NSVI (0-100)\n  - Medie ponderată
      
      Note over ScoringChecker: NSVI ≥ 75 → APPROVED\nNSVI ≥ 50 → REVIEW\nNSVI < 50 → REJECTED
      
      ScoringChecker-->>CoR: FeasibilityResult
    end
  end

  CoR-->>FS: FeasibilityResult
  FS->>FR: saveReport(plantId, result)
  FR->>DB: INSERT feasibility_reports\n(status, nsvi_score,\ndeficiencies JSONB,\nerrors JSONB, message)
  FR-->>FS: bool
  FS-->>C: ApiResponseDTO (status, data)
  C-->>B: JSON { reportId, status, nsviScore, ... }
```

## 3. Monitorizare SSE — Stream senzori în timp real

```mermaid
sequenceDiagram
  participant B as Browser
  participant R as SensorController
  participant Srv as SensorService
  participant Sim as SimulatorService
  participant Alg as AbstractReactorSimulator
  participant Thresh as ThresholdChecker
  participant Obs as Observers
  participant Repo as SensorRepo + MeasRepo
  participant DB as PostgreSQL

  Note over B,DB: Inițializare sesiune monitorizare
  B->>B: new EventSource(\n  "/api/reactors/{id}/stream")

  Note over Sim,DB: Daemon loop — fiecare 3 secunde
  loop TICK (la fiecare 3s)
    Sim->>Sim: getSimulator(reactor)
    Sim->>Alg: tick(reactorId)
    
    Alg->>Repo: Load reactor + sensors from DB
    Repo->>DB: SELECT reactor + reactor_sensors
    DB-->>Repo: entities
    Repo-->>Alg: Reactor + ReactorSensor[]
    
    Alg->>Alg: generateValues()
    Note over Alg: Pentru fiecare senzor:\n  - Aplică strategia specifică\n  (Thermocouple, NeutronDetector,\n   PressureTransducer, etc.)
    
    Alg->>Alg: applyPhysicalCorrelation()
    Note over Alg: Corelații fizice per tip:\n  PWR: T_out ~ power, pressure ~ T\n  BWR: power ~ recirculation^0.8\n  PHWR: moderator ~ 70°C constant\n  FBR: sodium ~ power, Doppler feedback
    
    Alg->>Thresh: checkAll(sensors, values)
    Thresh->>Thresh: Pentru fiecare senzor:\n  value > scram_high → EMERGENCY\n  value > alarm_high → ALERT\n  value > alert_high → WARNING
    Thresh-->>Alg: ViolationEvent[]
    
    alt Există violări
      Alg->>Obs: notifyObservers(events)
      par Observer 1
        Obs->>AlertObserver: update(event)
        AlertObserver->>AlertObserver: Debounce 60s
        AlertObserver->>Repo: Save reactor_alert
      and Observer 2
        Obs->>NotificationObserver: update(event)
        NotificationObserver->>NotificationObserver: Debounce 300s (EMERGENCY)
        NotificationObserver->>Repo: Save alert + log
        NotificationObserver->>EmailService: sendEmail(owner)
      and Observer 3
        Obs->>ScramObserver: update(event)
        ScramObserver->>ScramObserver: EMERGENCY →\nSHUTDOWN reactor
        ScramObserver->>Repo: UPDATE reactor\noperational_status\n= EMERGENCY_SHUTDOWN
      end
    end
    
    Alg->>Alg: buildMeasurement()
    Alg->>Alg: applyWear()\n(wear_delta = f(power, lifetime))
    Alg->>Repo: saveSensorValues(sensors)
    Alg->>Repo: saveMeasurement(measurement)
    Repo->>DB: INSERT sensor_readings\nINSERT measurements
  end

  Note over B,R: SSE Connection (persistentă)
  loop SSE Stream
    B->>R: EventSource conectat
    R->>R: session_write_close()\n(eliberează lock sesiune)
    R->>Srv: getStreamData(reactorId)
    Srv->>Repo: SELECT latest measurements\n+ sensors current_value
    Repo-->>Srv: ReactorStreamDTO
    Srv-->>R: JSON payload
    
    R-->>B: SSE event: "data"\n{ timestamp, reactorId,\n  sensors: [{ id, code, type,\n    value, status, unit }, ...] }
    
    B->>B: Update DOM:\n- Tabel valori senzori\n- Gauge-uri colorate\n- (dacă alertă → toast)
    
    Note over B,R: SSE reconnect automată\nla cădere conexiune
  end
```

## 4. Alertă SCRAM — Oprire de urgență

```mermaid
sequenceDiagram
  participant Sim as Simulator\ntick()
  participant Thresh as ThresholdChecker
  participant Obs as ScramObserver
  participant Obs2 as NotificationObserver
  participant Obs3 as AlertObserver
  participant Repo as AlertRepository
  participant Email as EmailService
  participant DB as PostgreSQL

  Sim->>Thresh: checkAll(sensors)
  
  Note over Thresh: Senzor "neutron_flux-PWR-001"\nvaloare=8.5e14 > scram_high=7.2e14
  Thresh->>Thresh: Severity = EMERGENCY
  
  Thresh-->>Sim: ViolationEvent(\n  severity: EMERGENCY,\n  sensorType: NEUTRON_DETECTOR,\n  value: 8.5e14,\n  threshold: 7.2e14,\n  reactorId: ...,\n  plantId: ...)
  
  par Notificare paralelă
    Sim->>Obs: update(event)
    Obs->>Obs: Debounce 60s
    Obs->>Repo: saveReactorAlert({\n  type: 'SCRAM',\n  severity: 'EMERGENCY',\n  message: 'Oprire de urgență! Flux neutroni...'})
    Obs->>DB: UPDATE reactor SET\n  operational_status =\n  'EMERGENCY_SHUTDOWN'\n  WHERE id = ?
  and
    Sim->>Obs2: update(event)
    Obs2->>Obs2: Debounce 300s\n(prima dată -> instant)
    Obs2->>Repo: getPlantOwnerEmail(plantId)
    Obs2->>Email: sendAlert({\n  to: ownerEmail,\n  subject: 'SCRAM - Oprire de urgență',\n  body: 'Reactorul X a fost oprit...'})
    Email->>SMTP: PHPMailer send
    Obs2->>Repo: saveAlert({\n  type: 'CRITICAL',\n  message: 'SCRAM declanșat...'})
  and
    Sim->>Obs3: update(event)
    Obs3->>Obs3: Debounce 60s
    Obs3->>Repo: saveReactorAlert({\n  type: 'ALARM',\n  severity: 'HIGH',\n  ...})
  end

  Repo->>DB: Toate INSERT-urile +\nUPDATE-urile
  DB-->>Repo: OK

  Note over Sim,DB: Rezultat final:\n  - Reactor oprit (EMERGENCY_SHUTDOWN)\n  - 2 alerte salvate (SCRAM + ALARM)\n  - Email trimis proprietarului\n  - Notificare in-app pentru operatori
```

## 5. Agregare măsurători orare

```mermaid
sequenceDiagram
  participant Agg as Aggregator\n(daemon 60s)
  participant MR as MeasurementsRepository
  participant DB as PostgreSQL

  loop La fiecare 60s
    Agg->>MR: aggregateHourly(from, to, 3600)
    MR->>DB: SELECT reactor_id,\n  COUNT(*) as samples,\n  AVG(power_percent),\n  MIN(power_percent),\n  MAX(power_percent),\n  AVG(neutron_flux),\n  AVG(temp_fuel_center),\n  AVG(temp_coolant_in),\n  AVG(temp_coolant_out),\n  AVG(temp_moderator),\n  AVG(pressure),\n  AVG(flow_rate_primary),\n  AVG(radiation),\n  AVG(efficiency),\n  SUM(wear_delta)\nFROM measurements\nWHERE timestamp >= $from\n  AND timestamp < $to\nGROUP BY reactor_id,\n  date_trunc('hour', timestamp)
    
    MR->>DB: INSERT INTO measurements_hourly\n  ... VALUES ...\n  ON CONFLICT (reactor_id, hour)\n  DO UPDATE SET ...\n  (upsert)
    DB-->>MR: OK
    
    MR-->>Agg: rows affected
    Agg->>Agg: Log aggregare: "Agregare orară: N rânduri"
  end
```

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
