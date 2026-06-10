# Plan de Implementare — Simulator Senzori

## 1. Arhitectura generală

```
┌─────────────────────────────────────────────────────────────────┐
│                    PROCES CLI SEPARAT                           │
│  docker-compose → serviciu "simulator" → php simulator.php     │
│                                                                 │
│  while(true) {                                                  │
│      foreach (reactor activ) {                                  │
│          AbstractReactorSimulator::tick()  (Template Method)    │
│              → citește senzorii                                   │
│              → GeneratorFactory::get(type)→Strategy::generate()   │
│              → applyPhysicalCorrelations() (abstract)             │
│              → verifica praguri → notifica Observeri              │
│              → scrie current_value + sensor_readings              │
│      }                                                          │
│      sleep(3–5 sec)                                             │
│  }                                                              │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                    SERVERUL PRINCIPAL (index.php)               │
│                                                                 │
│  GET /api/reactors/{id}/stream  (SSE endpoint)                 │
│      → conexiune persistenta                                     │
│      → la fiecare ~3s citeste current_value din DB               │
│      → trimite JSON la browser                                   │
│      → browser primeste EventSource fara polling manual          │
└─────────────────────────────────────────────────────────────────┘
```

**Principiu:** Simularea și afișarea sunt complet independente.
- Procesul CLI generează valori și le scrie în DB.
- SSE endpoint-ul doar citește DB-ul și trimite date la browser.

---

## 2. Design Patterns

| Pattern | Rol | Fișiere |
|---------|-----|---------|
| **Strategy** | Generare valori per tip senzor | `Generators/SensorGeneratorStrategy` (interfață) + implementări (ThermocoupleStrategy, NeutronDetectorStrategy, etc.) + `SensorGeneratorFactory` |
| **Observer** | Reacție la depășire praguri | `Observers/ThresholdObserver` (interfață) + AlarmObserver, EmailObserver, RssObserver |
| **Template Method** | Ciclu simulare per reactor | `Reactors/AbstractReactorSimulator::tick()` + subclase PwrSimulator, BwrSimulator, PhwrSimulator, FbrSimulator |

---

## 3. Etape de implementare

### Etapa 1 — Strategy: Generarea valorilor per tip senzor

| Task | Fișier | Descriere |
|------|--------|-----------|
| 1.1 | `src/Services/Simulator/Generators/SensorGeneratorStrategy.php` | Interfață cu `generate(float $currentValue, ReactorSensor $sensor): float` |
| 1.2 | `src/Services/Simulator/Generators/ThermocoupleStrategy.php` | Inerție mare, variație lentă, fluctuații mici |
| 1.3 | `src/Services/Simulator/Generators/NeutronDetectorStrategy.php` | Variație rapidă, zgomot statistic, spike-uri posibile |
| 1.4 | `src/Services/Simulator/Generators/PressureTransducerStrategy.php` | Medie, corelată cu temperatură |
| 1.5 | `src/Services/Simulator/Generators/FlowMeterStrategy.php` | Proporțională cu diferența de presiune |
| 1.6 | `src/Services/Simulator/Generators/RadiationMonitorStrategy.php` | Valori mici de bază cu spike-uri rare |
| 1.7 | `src/Services/Simulator/Generators/VibrationSensorStrategy.php` | Zgomot de fond + spike-uri la evenimente |
| 1.8 | `src/Services/Simulator/Generators/LevelSensorStrategy.php` | Lentă, integrează debite |
| 1.9 | `src/Services/Simulator/Generators/ActivityMonitorStrategy.php` | Similar cu radiația, dar mai stabilă |
| 1.10 | `src/Services/Simulator/Generators/SeismicSensorStrategy.php` | Aproape constant, spike-uri foarte rare |
| 1.11 | `src/Services/Simulator/Generators/HydrogenDetectorStrategy.php` | Creștere lentă în condiții anormale |
| 1.12 | `src/Services/Simulator/Generators/ValvePositionStrategy.php` | Valorizare discretă (procente 0–100) |
| 1.13 | `src/Services/Simulator/Generators/PumpSpeedStrategy.php` | RPM, variație controlată |
| 1.14 | `src/Services/Simulator/Generators/SensorGeneratorFactory.php` | Mapare `SensorType → Strategy`, returnează strategia corectă |

### Etapa 2 — Template Method: Ciclul de simulare per reactor

| Task | Fișier | Descriere |
|------|--------|-----------|
| 2.1 | `src/Services/Simulator/Reactors/AbstractReactorSimulator.php` | Clasă abstractă cu `tick()` care orchestrează: citește senzori activi → generează valori → applyPhysicalCorrelations() → verifică praguri → scrie în DB. Metoda abstractă: `applyPhysicalCorrelations(array &$values)` |
| 2.2 | `src/Services/Simulator/Reactors/PwrSimulator.php` | Corelații fizice specifice PWR: presiune primară legată de temperatură, putere termică etc. |
| 2.3 | `src/Services/Simulator/Reactors/BwrSimulator.php` | Corelații specifice BWR |
| 2.4 | `src/Services/Simulator/Reactors/PhwrSimulator.php` | Corelații specifice PHWR (CANDU) |
| 2.5 | `src/Services/Simulator/Reactors/FbrSimulator.php` | Corelații specifice FBR (sodiu, temperaturi înalte) |

### Etapa 3 — Observer: Reacția la depășirea pragurilor

| Task | Fișier | Descriere |
|------|--------|-----------|
| 3.1 | `src/Services/Simulator/Observers/ThresholdObserver.php` | Interfață cu `onThresholdBreach(string $sensorId, string $level, float $value, float $threshold)` |
| 3.2 | `src/Services/Simulator/Observers/AlarmObserver.php` | Scrie alertă în tabela `alerts` |
| 3.3 | `src/Services/Simulator/Observers/EmailObserver.php` | Trimite email prin `AlertService`/PHPMailer |
| 3.4 | `src/Services/Simulator/Observers/RssObserver.php` | Actualizează feed RSS (dacă există) |
| 3.5 | Integrare în `AbstractReactorSimulator` | La verificarea pragurilor, notifică toți observerii înregistrați |

### Etapa 4 — SimulatorService: Orchesterarea principală

| Task | Fișier | Descriere |
|------|--------|-----------|
| 4.1 | `src/Services/Simulator/SimulatorService.php` | Pornire/oprire ciclu: `run()` cu `while(true)`, iterare reactoare active, apel `AbstractReactorSimulator::tick()` per reactor, `sleep(3–5)` |
| 4.2 | `simulator.php` (rădăcină backend) | Script CLI entry point: `php simulator.php`. Inițializează DB connection, instanțiază `SimulatorService`, apelează `run()`. Loop infinit până la `SIGINT`/`SIGTERM` |

### Etapa 5 — SSE endpoint pentru frontend

| Task | Fișier | Descriere |
|------|--------|-----------|
| 5.1 | `src/Controllers/ReactorStreamController.php` | Controler nou cu metodă `stream(string $reactorId)`: setează headers SSE (`Content-Type: text/event-stream`, `Cache-Control: no-cache`), loop while(true) citind senzorii reactorului din DB și trimițând `data: {...}\n\n`, verifică `connection_aborted()` la fiecare iterație, `sleep(3)` |
| 5.2 | `public/index.php` | Rută `GET /api/reactors/{id}/stream` → `ReactorStreamController::stream()` |

### Etapa 6 — Integrare Docker

| Task | Fișier | Descriere |
|------|--------|-----------|
| 6.1 | `docker-compose.yml` | Adăugare serviciu `simulator`: aceeași imagine PHP, command: `php simulator.php`, depends_on: db, restart: unless-stopped |
| 6.2 | Testare | Verificare că procesul rulează continuu, scrie valori în DB, nu crapă la timeout |

---

## 4. Dependențe între etape

```
Etapa 1 (Strategy — generatoare)
    ↓
Etapa 2 (Template Method — reactor sim) ← depinde de Etapa 1
    ↓
Etapa 3 (Observer — alerare)           ← independentă, poate rula paralel cu 1–2
    ↓
Etapa 4 (SimulatorService — orchestrator) ← depinde de Etapa 1, 2, 3
    ↓
Etapa 5 (SSE endpoint)                 ← independentă de 1–4, poate rula paralel
    ↓
Etapa 6 (Docker integrare)             ← depinde de Etapa 4
```

---

## 5. Cum se leagă componentele

```
                    simulator.php (CLI)
                          │
                          ▼
              SimulatorService::run()
                          │
                          ├─ reactor activ 1 ──→ PwrSimulator::tick()
                          │                          │
                          │                          ├─ SensorGeneratorFactory::get(type)
                          │                          │    └─ Strategy::generate(value, sensor)
                          │                          │
                          │                          ├─ applyPhysicalCorrelations()
                          │                          │
                          │                          ├─ verificare praguri
                          │                          │    └─ ThresholdObserver::onThresholdBreach()
                          │                          │         ├─ AlarmObserver
                          │                          │         ├─ EmailObserver
                          │                          │         └─ RssObserver
                          │                          │
                          │                          └─ scriere DB (current_value, sensor_readings)
                          │
                          ├─ reactor activ 2 ──→ BwrSimulator::tick()
                          └─ ...

                    Browser (frontend)
                          │
                          ├── EventSource("/api/reactor/{id}/stream")
                          │         │
                          │         ▼
                          │   ReactorStreamController::stream()
                          │         │
                          │         └─ while(true) {
                          │               citeste current_value din DB
                          │               echo "data: " . json . "\n\n"
                          │               ob_flush(); flush();
                          │               sleep(3);
                          │               if (connection_aborted()) break;
                          │         }
                          │
                          └── primește JSON cu toți senzorii reactorului
```

---

## 6. Comportamentul simulării per tip de senzor

| Tip senzor | Comportament |
|-----------|--------------|
| THERMOCOUPLE | Inerție mare, variație lentă ±1–2°C per pas, tendință controlată |
| PRESSURE_TRANSDUCER | Urmărește temperatura, variație moderată |
| NEUTRON_DETECTOR | Zgomot Poisson, variație rapidă, spike-uri statistice |
| FLOW_METER | Proporțional cu ΔP, stabil |
| RADIATION_MONITOR | Fond constant + spike-uri rare (distribuție exponențială) |
| VIBRATION_SENSOR | Zgomot Gaussian ±mic, spike-uri la pornire/oprire pompă |
| LEVEL_SENSOR | Schimbări lente, integrează inflow - outflow |
| ACTIVITY_MONITOR | Similar radiație, mai stabil |
| SEISMIC_SENSOR | Aproape 0, spike doar la eveniment extern simulat |
| HYDROGEN_DETECTOR | 0 în regim normal, creștere lentă la anomalie |
| VALVE_POSITION | Discret, trepte de 5–10% |
| PUMP_SPEED | RPM ± toleranță, controlat de setpoint |

---

## 7. Structura finală de fișiere

```
src/Services/Simulator/
├── SimulatorService.php
├── Reactors/
│   ├── AbstractReactorSimulator.php
│   ├── PwrSimulator.php
│   ├── BwrSimulator.php
│   ├── PhwrSimulator.php
│   └── FbrSimulator.php
├── Generators/
│   ├── SensorGeneratorStrategy.php
│   ├── SensorGeneratorFactory.php
│   ├── ThermocoupleStrategy.php
│   ├── NeutronDetectorStrategy.php
│   ├── PressureTransducerStrategy.php
│   ├── FlowMeterStrategy.php
│   ├── RadiationMonitorStrategy.php
│   ├── VibrationSensorStrategy.php
│   ├── LevelSensorStrategy.php
│   ├── ActivityMonitorStrategy.php
│   ├── SeismicSensorStrategy.php
│   ├── HydrogenDetectorStrategy.php
│   ├── ValvePositionStrategy.php
│   └── PumpSpeedStrategy.php
└── Observers/
    ├── ThresholdObserver.php
    ├── AlarmObserver.php
    ├── EmailObserver.php
    └── RssObserver.php
simulator.php
```

---

## 8. Legendă stadiu

| Simbol | Înseamnă |
|--------|----------|
| ❌ | Nerealizat |
| ✅ | Realizat |
| 🔧 | În lucru |
