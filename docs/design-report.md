# Raport de Design — Nuclear Power Plant Web Manager

> Versiune: 1.0 — Data: 13 Iunie 2026
> Autori: Mogos Paul, Moraru Ioan Alexandru

---

## Cuprins

1. [Arhitectură Generală](#1-arhitectură-generală)
2. [Baza de Date](#2-baza-de-date)
3. [Entități (Domain Model)](#3-entități-domain-model)
4. [Enum-uri](#4-enum-uri)
5. [DTO-uri (Data Transfer Objects)](#5-dto-uri-data-transfer-objects)
6. [Repository-uri (Data Access Layer)](#6-repository-uri-data-access-layer)
7. [Servicii (Business Logic Layer)](#7-servicii-business-logic-layer)
8. [Controllere (HTTP Layer)](#8-controllere-http-layer)
9. [Frontend](#9-frontend)
10. [Daemon-uri CLI](#10-daemon-uri-cli)
11. [Securitate](#11-securitate)
12. [Pattern-uri de Design](#12-pattern-uri-de-design)
13. [Decizii Tehnice](#13-decizii-tehnice)
14. [Metrici](#14-metrici)
15. [Licență](#15-licență)

---

## 1. Arhitectură Generală

### 1.1 Stil arhitectural — Web Services / Layered Monolith

Backendul este organizat pe trei straturi distincte:

```
Controller (HTTP) → Service (Business Logic) → Repository (Data Access)
                         ↕
                     Entity (Domain Model)
```

- **Controllers**: procesează cereri HTTP, parsează input JSON, răspund cu JSON/HTML/SSE/XLM
- **Services**: conțin logica de business — validări, calcule, fluxuri de aprobare, orchestrare
- **Repositories**: interacționează cu baza de date prin PDO prepared statements
- **Entities**: modele de domeniu — clase PHP simple cu proprietăți și getter/setter manuale

### 1.2 Frontend — Single-Page Application (SPA) vanilla

- Fără framework-uri JavaScript (React, Vue, Angular)
- Arhitectură bazată pe module ES6 (`type="module"`) cu încărcare dinamică
- Ruter custom în `modules/core/router.js` care parsează `window.location` și încarcă paginile
- Comunicare cu backend prin REST API (`fetch()`) și SSE (`EventSource`)
- Templating prin manipulare directă DOM (fără motor de template-uri)

### 1.3 Organizare directoare

```
backend/
├── config/               # Configurare aplicație, rute, scripturi
│   ├── routes.php        # Definire rute API
│   ├── scripts.php       # Configurare daemoni (intervale)
│   └── feasibility-params.json   # Parametrii fezabilitate per tip reactor
├── database/
│   ├── init.sql          # Script de inițializare complet (rulează toate .sql)
│   ├── users/            # Schema users
│   ├── plants/           # Schema centrale
│   ├── reactors/         # Schema reactoare
│   ├── sensors/          # Schema senzori
│   ├── alerts/           # Schema alerte
│   └── logs/             # Schema log-uri
├── src/
│   ├── Controllers/      # 12 controllere (7 + 5 subdirector)
│   ├── Services/         # 17 servicii (10 principale + 5 subservicii + 2 fabrici)
│   ├── Repositories/     # 14 repository-uri
│   ├── Entities/         # 12 entități
│   ├── Dto/              # 26 DTO-uri
│   ├── Enums/            # 8 enum-uri PHP (backed by string)
│   ├── Middleware/        # Autentificare, autorizare, CSRF
│   ├── Traits/           # Trăsături reutilizabile
│   └── Utils/            # Validare, Session, Helpers
├── views/                # Template-uri HTML (sign-in, layout-uri)
├── bin/                  # Scripturi CLI (daemoni)
└── public/index.php      # Front Controller

frontend/
├── pages/                # HTML-uri individuale per pagină
├── modules/
│   ├── core/             # Router, Auth, API, CSRF, FormHandler, Validator
│   ├── pages/            # Câte un folder per pagină (view + controller logic)
│   └── styles/           # CSS modular
└── assets/               # Statice (logo, favicon)
```

### 1.4 Flux de request

```
1. Apache (.htaccess) → public/index.php
2. Router.php → parsează ruta, extrage controller/method
3. Middleware check → autentificare, rol, CSRF
4. Controller → extrage date (GET/POST/PUT/DELETE), validează
5. Service → orchestră business logic, apelează Repository
6. Repository → PDO prepared statement → PostgreSQL
7. Controller → setează antete HTTP, răspunde cu JSON
```

---

## 2. Baza de Date

### 2.1 Tehnologii

- **PostgreSQL 15** cu extensiile `uuid-ossp`, `pgcrypto`
- **Conexiune**: PDO PHP 8.4
- **Total**: 15 tabele, 8 enum-uri PostgreSQL, 7 indecși, constrângeri unice și FK

### 2.2 Lista completă a tabelelor

| Tabel | Scop | Cheie primară | FK-uri | Constrângeri unice |
|---|---|---|---|---|
| `users` | Conturi utilizatori | `id UUID` | — | `username`, `email` |
| `power_plants` | Înregistrare centrale | `id UUID` | `created_by → users(id)` | — |
| `basic_data` | Date de bază centrală | `id UUID` | `power_plant_id → power_plants(id)` | `power_plant_id` |
| `geological_data` | Date geologice | `id UUID` | `power_plant_id → power_plants(id)` | `power_plant_id` |
| `technical_data` | Date tehnice | `id UUID` | `power_plant_id → power_plants(id)` | `power_plant_id` |
| `feasibility_reports` | Rapoarte fezabilitate NSVI | `id UUID` | `power_plant_id → power_plants(id)` | — |
| `reactor` | Unități reactor | `id UUID` | `power_plant_id → power_plants(id)` | `(power_plant_id, reactor_code)` |
| `reactor_schema` | Catalog tipuri reactor+ răcire | `id UUID` | — | `(reactor_type, cooling_type)` |
| `reactor_plant_data` | Legătură M:N technical_data ↔ reactor_schema | `(technical_data_id, reactor_schema_id)` | Ambele coloane FK | PK compusă |
| `control_rods` | Bare de control | `id UUID` | `reactor_id → reactor(id)` | `(reactor_id, rod_group, rod_number)` |
| `reactor_sensors` | Senzori per reactor | `id UUID` | `reactor_id → reactor(id)` | `(reactor_id, sensor_code)` |
| `sensor_readings` | Citiri individuale senzori | `id UUID` | `sensor_id → reactor_sensors(id)` | — |
| `measurements` | Măsurători compuse per reactor | `id UUID` | `reactor_id → reactor(id)` | — |
| `measurements_hourly` | Măsurători agregate orar | `(reactor_id, hour)` | `reactor_id → reactor(id)` | PK compusă |
| `sensor_templates` | Template-uri predefinite senzori | `id UUID` | — | `(reactor_type, sensor_code)` |
| `reactor_alerts` | Alerte per reactor | `id UUID` | `reactor_id → reactor(id)`, `plant_id → power_plants(id)` | — |
| `alerts` | Alerte generale per centrală | `id UUID` | `plant_id → power_plants(id)` | — |
| `logs` | Log-uri aplicație | `id UUID` | — | — |

### 2.3 Descrierea detaliată a fiecărui tabel

#### `users`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` | Identificator unic |
| `username` | `VARCHAR(30)` | NOT NULL | Nume utilizator |
| `first_name` | `VARCHAR(50)` | NOT NULL | Prenume |
| `last_name` | `VARCHAR(50)` | NOT NULL | Nume |
| `email` | `VARCHAR(100)` | NOT NULL, UNIQUE | Email |
| `password_hash` | `VARCHAR(255)` | NOT NULL | Hash bcrypt |
| `role` | `user_roles` | NOT NULL | Rol: ADMIN, ENGINEER, OPERATOR |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` | Data creării |

#### `power_plants`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` | Identificator unic |
| `name` | `VARCHAR(255)` | NOT NULL | Numele centralei |
| `status` | `power_plant_status` | NOT NULL | Status: DRAFT, REVIEW, APPROVED, REJECTED |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| `created_by` | `UUID` | FK → `users(id) ON DELETE SET NULL` | Utilizatorul care a creat centrala |

#### `basic_data`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `power_plant_id` | `UUID` | NOT NULL, UNIQUE, FK → `power_plants(id) ON DELETE CASCADE` |
| `capacity_mw` | `DECIMAL` | — | Capacitate instalată (MW) |
| `construction_duration_years` | `INT` | — | Durată construcție (ani) |
| `description` | `TEXT` | — | Descriere text |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `geological_data`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `power_plant_id` | `UUID` | NOT NULL, UNIQUE, FK → `power_plants(id) ON DELETE CASCADE` |
| `country` | `VARCHAR(100)` | — | Țara |
| `latitude` | `DECIMAL(9,6)` | — | Latitudine |
| `longitude` | `DECIMAL(9,6)` | — | Longitudine |
| `soil_type` | `soil_types` | — | Tip sol (enum) |
| `water_source_type` | `water_source_types` | — | Tip sursă apă (enum) |
| `seismic_stability` | `DECIMAL` | — | Stabilitate seismică (scor) |
| `flood_risk` | `DECIMAL` | — | Risc inundații (scor) |
| `groundwater_level` | `DECIMAL` | — | Nivel panză freatică (m) |
| `water_proximity` | `DECIMAL` | — | Proximitate apă (km) |
| `water_flow_rate` | `DECIMAL` | — | Debit apă (m³/s) |
| `population_density` | `DECIMAL` | — | Densitate populație (loc/km²) |
| `transport_infrastructure_score` | `DECIMAL` | — | Scor infrastructură transport |
| `geological_risk_score` | `DECIMAL` | — | Scor risc geologic general |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `technical_data`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `power_plant_id` | `UUID` | NOT NULL, UNIQUE, FK → `power_plants(id) ON DELETE CASCADE` |
| `number_of_reactors` | `INT` | — | Număr reactoare |
| `estimated_efficiency` | `DECIMAL` | — | Eficiență estimată (%) |
| `operational_risk_level` | `DECIMAL` | — | Nivel risc operațional |
| `safety_systems` | `JSONB` | — | Sisteme de siguranță (listă) |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `feasibility_reports`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `power_plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` |
| `deficiencies` | `JSONB` | — | Listă deficiențe (scor sub prag) |
| `errors` | `JSONB` | — | Listă erori critice |
| `status` | `power_plant_status` | NOT NULL | Status recomandat |
| `nsvi_score` | `DECIMAL(5,2)` | — | Scor NSVI (Nuclear Site Viability Index) |
| `message` | `TEXT` | — | Mesaj raport |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `reactor`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `power_plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` |
| `reactor_code` | `VARCHAR(100)` | NOT NULL | Cod unic reactor |
| `reactor_type` | `reactor_types` | NOT NULL | Tip reactor: PWR, BWR, PHWR, FBR |
| `cooling_type` | `cooling_types` | NOT NULL | Tip răcire |
| `operational_status` | `reactor_operational_status` | NOT NULL, DEFAULT 'SHUTDOWN' | Status operațional |
| `thermal_power_mw` | `DECIMAL(10,2)` | — | Putere termică (MW) |
| `electrical_power_mw` | `DECIMAL(10,2)` | — | Putere electrică (MW) |
| `fuel_cycle_days` | `INT` | DEFAULT 365 | Durată ciclu combustibil (zile) |
| `current_cycle_day` | `INT` | DEFAULT 0 | Ziua curentă din ciclu |
| `wear_index` | `DECIMAL(5,4)` | DEFAULT 0.0000, CHECK (0-1) | Indice uzură |
| `design_lifetime_yr` | `INT` | DEFAULT 40 | Durată de viață proiectată (ani) |
| `commissioning_date` | `DATE` | — | Data punerii în funcțiune |
| `first_criticality` | `DATE` | — | Data primei criticități |
| `last_inspection_at` | `TIMESTAMP` | — | Ultima inspecție |
| `next_planned_outage` | `TIMESTAMP` | — | Următoarea oprire planificată |
| `description` | `TEXT` | — | Descriere |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `reactor_schema`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `reactor_type` | `reactor_types` | NOT NULL | Tip reactor |
| `cooling_type` | `cooling_types` | — | Tip răcire |

Conține toate cele 28 combinații posibile (4 tipuri reactor × 7 tipuri răcire).

#### `reactor_plant_data`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `technical_data_id` | `UUID` | PK, FK → `technical_data(id) ON DELETE CASCADE` |
| `reactor_schema_id` | `UUID` | PK, FK → `reactor_schema(id) ON DELETE RESTRICT` |
| `number_of_reactors` | `INT` | NOT NULL | Câte reactoare de acest tip |

#### `control_rods`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| `rod_group` | `VARCHAR(10)` | NOT NULL | Grupă bară |
| `rod_number` | `INT` | NOT NULL | Număr bară |
| `material` | `VARCHAR(50)` | DEFAULT 'Ag-In-Cd' | Material |
| `position_mm` | `DECIMAL(8,2)` | — | Poziție (mm) |
| `position_percent` | `DECIMAL(5,2)` | — | Poziție (%) |
| `is_inserted` | `BOOLEAN` | NOT NULL, DEFAULT TRUE | Introdusă |
| `status` | `VARCHAR(30)` | DEFAULT 'OPERATIONAL' | Status |
| `last_inspection` | `TIMESTAMP` | — | Ultima inspecție |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

UNIQUE: `(reactor_id, rod_group, rod_number)`

#### `reactor_sensors`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| `sensor_code` | `VARCHAR(30)` | NOT NULL | Cod senzor |
| `sensor_type` | `sensor_types` | NOT NULL | Tip senzor (12 tipuri) |
| `description` | `VARCHAR(255)` | — | Descriere |
| `location_zone` | `VARCHAR(100)` | — | Zonă amplasare |
| `unit_of_measure` | `VARCHAR(20)` | — | Unitate de măsură |
| `measurement_field` | `VARCHAR(40)` | — | Corespondență coloană în `measurements` |
| `normal_min` | `DECIMAL(20,4)` | — | Minim normal |
| `normal_max` | `DECIMAL(20,4)` | — | Maxim normal |
| `alarm_low` | `DECIMAL(20,4)` | — | Prag alarmă inferior |
| `alarm_high` | `DECIMAL(20,4)` | — | Prag alarmă superior |
| `alert_low` | `DECIMAL(20,4)` | — | Prag alertă inferior |
| `alert_high` | `DECIMAL(20,4)` | — | Prag alertă superior |
| `scram_low` | `DECIMAL(20,4)` | — | Prag SCRAM inferior |
| `scram_high` | `DECIMAL(20,4)` | — | Prag SCRAM superior |
| `status` | `sensor_quality` | NOT NULL, DEFAULT 'GOOD' | Calitate senzor |
| `is_active` | `BOOLEAN` | NOT NULL, DEFAULT TRUE | Activ |
| `last_calibration` | `TIMESTAMP` | — | Ultima calibrare |
| `calibration_due` | `TIMESTAMP` | — | Următoarea calibrare |
| `current_value` | `DECIMAL(20,4)` | — | Valoarea curentă |
| `last_reading_at` | `TIMESTAMP` | — | Ultima citire |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

UNIQUE: `(reactor_id, sensor_code)`

#### `sensor_readings`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `sensor_id` | `UUID` | NOT NULL, FK → `reactor_sensors(id) ON DELETE CASCADE` |
| `timestamp` | `TIMESTAMP` | NOT NULL, DEFAULT `CURRENT_TIMESTAMP` |
| `value` | `DECIMAL(20,4)` | NOT NULL | Valoare citită |
| `quality` | `sensor_quality` | NOT NULL, DEFAULT 'GOOD' | Calitate citire |
| `raw_value` | `DECIMAL(20,4)` | — | Valoare brută |

#### `measurements`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| `timestamp` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| `power_percent` | `DECIMAL(6,3)` | — | Putere (%) |
| `neutron_flux` | `DECIMAL(20,4)` | — | Flux neutroni |
| `reactivity_pcm` | `DECIMAL(10,4)` | — | Reactivitate (pcm) |
| `reactor_period_sec` | `DECIMAL(10,2)` | — | Perioadă reactor (sec) |
| `temp_fuel_center` | `DECIMAL(8,2)` | — | Temperatură combustibil (°C) |
| `temp_coolant_in` | `DECIMAL(8,2)` | — | Temperatură lichid răcire intrare (°C) |
| `temp_coolant_out` | `DECIMAL(8,2)` | — | Temperatură lichid răcire ieșire (°C) |
| `temp_moderator` | `DECIMAL(8,2)` | — | Temperatură moderator (°C) |
| `pressure` | `DECIMAL(8,3)` | — | Presiune sistem (bar) |
| `flow_rate_primary` | `DECIMAL(12,2)` | — | Debit circuit primar (kg/s) |
| `flow_rate_secondary` | `DECIMAL(12,2)` | — | Debit circuit secundar (kg/s) |
| `steam_pressure` | `DECIMAL(8,3)` | — | Presiune abur (bar) |
| `steam_flow_rate` | `DECIMAL(12,2)` | — | Debit abur (kg/s) |
| `feedwater_temp` | `DECIMAL(8,2)` | — | Temperatură apă alimentare (°C) |
| `radiation` | `DECIMAL(15,4)` | — | Radiație (μSv/h) |
| `activity_primary` | `DECIMAL(15,4)` | — | Activitate circuit primar (Bq/m³) |
| `dose_rate_control_room` | `DECIMAL(10,4)` | — | Doză sala comandă (μSv/h) |
| `dose_rate_reactor_bldg` | `DECIMAL(10,4)` | — | Doză clădire reactor (μSv/h) |
| `airborne_activity` | `DECIMAL(15,4)` | — | Activitate aeriană (Bq/m³) |
| `fuel_burnup_mwd_t` | `DECIMAL(10,2)` | — | Ardere combustibil (MWd/t) |
| `efficiency` | `DECIMAL(6,4)` | — | Eficiență termică |
| `wear_delta` | `DECIMAL(8,6)` | — | Delta uzură |
| `level_reactor_vessel` | `DECIMAL(8,2)` | — | Nivel vas reactor (m) |
| `vibration` | `DECIMAL(8,2)` | — | Vibrații (mm/s) |

#### `measurements_hourly`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `reactor_id` | `UUID` | PK, FK → `reactor(id) ON DELETE CASCADE` |
| `hour` | `TIMESTAMP` | PK | Ora de agregare |
| `samples_count` | `INT` | NOT NULL | Număr de eșantioane |
| `power_percent_avg` | `DECIMAL(6,3)` | — | Medie putere |
| `power_percent_min` | `DECIMAL(6,3)` | — | Minim putere |
| `power_percent_max` | `DECIMAL(6,3)` | — | Maxim putere |
| `neutron_flux_avg` | `DECIMAL(20,4)` | — | Medie flux neutroni |
| `temp_fuel_center_avg` | `DECIMAL(8,2)` | — | Medie temperatură combustibil |
| `temp_coolant_in_avg` | `DECIMAL(8,2)` | — | Medie temperatură intrare |
| `temp_coolant_out_avg` | `DECIMAL(8,2)` | — | Medie temperatură ieșire |
| `temp_moderator_avg` | `DECIMAL(8,2)` | — | Medie temperatură moderator |
| `pressure_avg` | `DECIMAL(8,3)` | — | Medie presiune |
| `flow_rate_primary_avg` | `DECIMAL(12,2)` | — | Medie debit primar |
| `radiation_avg` | `DECIMAL(15,4)` | — | Medie radiație |
| `efficiency_avg` | `DECIMAL(6,4)` | — | Medie eficiență |
| `wear_delta_sum` | `DECIMAL(12,6)` | — | Sumă uzură |

#### `sensor_templates`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `reactor_type` | `reactor_types` | NOT NULL | Tip reactor |
| `sensor_code` | `VARCHAR(30)` | NOT NULL | Cod senzor |
| `sensor_type` | `sensor_types` | NOT NULL | Tip senzor |
| `description` | `VARCHAR(255)` | NOT NULL | Descriere |
| `location_zone` | `VARCHAR(100)` | — | Zonă |
| `unit_of_measure` | `VARCHAR(20)` | — | Unitate |
| `measurement_field` | `VARCHAR(40)` | — | Corespondență coloană |
| `normal_min` | `DECIMAL(20,4)` | — | Minim normal |
| `normal_max` | `DECIMAL(20,4)` | — | Maxim normal |
| `alarm_low` | `DECIMAL(20,4)` | — | Alarmă jos |
| `alarm_high` | `DECIMAL(20,4)` | — | Alarmă sus |
| `alert_low` | `DECIMAL(20,4)` | — | Alertă jos |
| `alert_high` | `DECIMAL(20,4)` | — | Alertă sus |
| `scram_low` | `DECIMAL(20,4)` | — | SCRAM jos |
| `scram_high` | `DECIMAL(20,4)` | — | SCRAM sus |

UNIQUE: `(reactor_type, sensor_code)`

#### `reactor_alerts`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| `plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` |
| `type` | `VARCHAR(20)` | NOT NULL | Tip: ALERT, ALARM, SCRAM |
| `severity` | `VARCHAR(20)` | NOT NULL | Severitate: LOW, MEDIUM, HIGH, EMERGENCY |
| `sensor_type` | `VARCHAR(50)` | — | Tip senzor care a declanșat |
| `value` | `DECIMAL(12,4)` | — | Valoarea care a declanșat |
| `threshold` | `DECIMAL(12,4)` | — | Prag atins |
| `message` | `TEXT` | NOT NULL | Mesaj |
| `is_read` | `SMALLINT` | DEFAULT 0 | Citit |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `alerts`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` |
| `alert_type` | `VARCHAR(20)` | NOT NULL | Tip: WARNING, CRITICAL, PLANT_STATUS_CHANGE, etc. |
| `message` | `TEXT` | NOT NULL |
| `is_read` | `SMALLINT` | DEFAULT 0 |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

#### `logs`

| Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|
| `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| `level` | `VARCHAR(20)` | NOT NULL | DEBUG, INFO, WARNING, ERROR, CRITICAL |
| `message` | `TEXT` | NOT NULL |
| `context` | `JSONB` | — | Context JSON |
| `user_id` | `UUID` | — |
| `plant_id` | `UUID` | — |
| `reactor_id` | `UUID` | — |
| `source` | `VARCHAR(20)` | DEFAULT 'backend' | backend / frontend |
| `request_uri` | `VARCHAR(255)` | — |
| `ip_address` | `VARCHAR(45)` | — |
| `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

Indexuri:
- `idx_logs_created_at ON logs (created_at DESC)`
- `idx_logs_level ON logs (level)`
- `idx_logs_user_id ON logs (user_id)`

### 2.4 Indexuri suplimentare

```sql
-- Măsurători: căutare rapidă per reactor + interval timp
CREATE INDEX idx_measurements_reactor_ts ON measurements (reactor_id, timestamp DESC);
CREATE INDEX idx_measurements_ts ON measurements(timestamp);

-- Citiri senzori: căutare rapidă per senzor
CREATE INDEX idx_sensor_readings_sensor_ts ON sensor_readings(sensor_id, timestamp DESC);

-- Măsurători orare: ordonare rapidă
CREATE INDEX idx_measurements_hourly_ts ON measurements_hourly (hour DESC);
```

### 2.5 Relații cheie

- `users` 1→N `power_plants` (un utilizator poate crea multiple centrale)
- `power_plants` 1→1 `basic_data`, `geological_data`, `technical_data` (date extensibile)
- `power_plants` 1→N `feasibility_reports` (multiple rapoarte)
- `power_plants` 1→N `reactor` (o centrală poate avea multiple reactoare)
- `technical_data` N→M `reactor_schema` prin `reactor_plant_data` (o centrală poate avea multiple configurații reactor)
- `reactor` 1→N `reactor_sensors` (un reactor are 7-10+ senzori)
- `reactor_sensors` 1→N `sensor_readings` (multiple citiri în timp)
- `reactor` 1→N `measurements` (măsurători compuse la fiecare tick)
- `reactor` 1→N `control_rods` (bare de control)

---

## 3. Entități (Domain Model)

### 3.1 Lista entităților

Toate entitățile sunt clase PHP simple (POCO) fără anotări ORM, localizate în `backend/src/Entities/`:

| Entitate | Proprietăți | Descriere |
|---|---|---|
| `Alert` | 6 | Alertă generală |
| `BasicPlantData` | 7 | Date de bază centrală |
| `GeologicalPlantData` | 18 | Date geologice |
| `Log` | 11 | Log aplicație |
| `Measurement` | 28 | Măsurătoare compusă reactor |
| `Plant` | 6 | Centrală nucleară |
| `Reactor` | 20 | Reactor nuclear |
| `ReactorSchema` | 3 | Combinație tip+răcire |
| `ReactorSensor` | 24 | Senzor per reactor |
| `SensorTemplate` | 16 | Template senzor predefinit |
| `TechnicalPlantData` | 8 | Date tehnice centrală |
| `User` | 7 | Utilizator |

Fiecare entitate are gettere și settere manuale, fără a folosi proprietăți promovate sau constructori automagici.

---

## 4. Enum-uri

Toate enum-urile PHP sunt `string` backed enums, localizate în `backend/src/Enums/`.

### 4.1 `CoolingType` — 7 valori
`ONCE_THROUGH_FRESH`, `ONCE_THROUGH_SALT`, `NATURAL_DRAFT_WET`, `MECHANICAL_DRAFT_WET`, `DRY_COOLING`, `HYBRID`, `COOLING_POND`

### 4.2 `PlantStatus` — 4 valori
`DRAFT` → `REVIEW` → `APPROVED` | `REJECTED`

### 4.3 `ReactorOperationalStatus` — 10 valori
`SHUTDOWN`, `COLD_STANDBY`, `HOT_STANDBY`, `STARTUP`, `POWER_ASCENT`, `FULL_POWER`, `PARTIAL_POWER`, `PLANNED_OUTAGE`, `UNPLANNED_OUTAGE`, `EMERGENCY_SHUTDOWN`

### 4.4 `ReactorType` — 4 valori
`PWR` (Pressurized Water Reactor), `BWR` (Boiling Water Reactor), `PHWR` (Pressurized Heavy Water Reactor), `FBR` (Fast Breeder Reactor)

### 4.5 `SensorQuality` — 5 valori
`GOOD`, `SUSPECT`, `BAD`, `MAINTENANCE`, `SIMULATED`

### 4.6 `SensorType` — 12 valori
`THERMOCOUPLE`, `PRESSURE_TRANSDUCER`, `NEUTRON_DETECTOR`, `FLOW_METER`, `RADIATION_MONITOR`, `VIBRATION_SENSOR`, `LEVEL_SENSOR`, `ACTIVITY_MONITOR`, `SEISMIC_SENSOR`, `HYDROGEN_DETECTOR`, `VALVE_POSITION`, `PUMP_SPEED`

### 4.7 `SoilType` — 12 valori
`BEDROCK`, `STIFF_CLAY`, `DENSE_SAND`, `GRAVEL`, `SHALE`, `LIMESTONE`, `SANDSTONE`, `SOFT_CLAY`, `LOOSE_SAND`, `SILT`, `LOAM`, `PEAT`

### 4.8 `WaterSourceType` — 3 valori
`FRESH_WATER`, `SALT_WATER`, `BRACKISH_WATER`

---

## 5. DTO-uri (Data Transfer Objects)

Toate DTO-urile extind `BaseDTO` care implementează `JsonSerializable` (serializare prin `get_object_vars`). Localizate în `backend/src/Dto/`.

### Lista completă (26 DTO-uri)

| DTO | Proprietăți | Folosit de |
|---|---|---|
| `AlertListDTO` | id, plantId, type, message, createdAt | AlertController |
| `ApiResponseDTO` | status, data (mixed), message | Toate controllerele |
| `BasicPlantDataDTO` | id, powerPlantId, capacity, constructionDurationYears, description, createdAt, updatedAt | BasicPlantController |
| `CoordinatesPreviewResponseDTO` | latitude, longitude, coordinatesLabel, country, soilType, waterSourceType, seismicStability, floodRisk, groundwaterLevel, waterProximity, waterFlowRate, populationDensity, transportInfrastructureScore, message | DetailsPlantController |
| `CreateDataResponseDTO` | dataId, plantId, message | PlantServiceFacade |
| `FeasibilityReportDTO` | reportId, status, nsviScore, deficiencies, errors, message, createdAt | FeasibilityController |
| `GeoLocationPreviewDTO` | country, soilType, waterSourceType, seismicStability, floodRisk, groundwaterLevel, waterProximity, waterFlowRate, populationDensity, transportInfrastructureScore | GeologicalPlantService |
| `GeologicalPlantDataDTO` | id, powerPlantId, country, latitude, longitude, soilType, waterSourceType, seismicStability, floodRisk, groundwaterLevel, waterProximity, waterFlowRate, populationDensity, transportInfrastructureScore, geologicalRiskScore, createdAt, updatedAt | GeologicalPlantController |
| `GetPlantDTO` | details (PlantDTO), basic (?BasicPlantDataDTO), geological (?GeologicalPlantDataDTO), technical (?TechnicalPlantDataDTO) | DetailsPlantController |
| `LogListDTO` | id, level, message, context, userId, plantId, reactorId, source, requestUri, ipAddress, createdAt | LogController |
| `NotificationDTO` | id, type, severity, title, message, date, targetRole, targetEmail | NotificationController |
| `PlantDTO` | id, name, status, createdBy, createdAt, updatedAt | DetailsPlantController |
| `PlantDetailsDTO` | id, name, createdBy, createdAt, updatedAt | DetailsPlantController |
| `PlantListDTO` | id, name, country, latitude, longitude, status, createdBy, createdAt, updatedAt | DetailsPlantController |
| `PlantMapDTO` | extends PlantListDTO + hasCoordinates, coordinatesLabel, popupTitle, popupSubtitle, editUrl | DetailsPlantController |
| `PlantStatusListDTO` | id, name, status, createdBy, createdAt, updatedAt | DetailsPlantController |
| `ReactorDetailsDTO` | id, powerPlantId, reactorCode, reactorType, coolingType, operationalStatus, thermalPowerMw, electricalPowerMw, fuelCycleDays, currentCycleDay, wearIndex, designLifetimeYr, commissioningDate, firstCriticality, lastInspectionAt, nextPlannedOutage, description, createdAt | ReactorController |
| `ReactorListDTO` | id, reactorCode, reactorType, coolingType, operationalStatus, thermalPowerMw, electricalPowerMw | ReactorController |
| `ReactorStreamDTO` | timestamp, reactorId, sensors (StreamSensorDTO[]) | SensorController (SSE) |
| `SensorDetailsDTO` | id, reactorId, sensorCode, sensorType, description, locationZone, unitOfMeasure, normalMin/Max, alarmLow/High, alertLow/High, scramLow/High, status, isActive, lastCalibration, calibrationDue, currentValue, lastReadingAt, createdAt | SensorController |
| `SensorListDTO` | id, reactorId, sensorCode, sensorType, status, currentValue, unitOfMeasure, isActive, lastReadingAt | SensorController |
| `StreamSensorDTO` | id, code, type, description, location, unit, value, normalMin/Max, alarmLow/High, alertLow/High, scramLow/High, status | SensorController (SSE) |
| `TechnicalPlantDataDTO` | id, powerPlantId, numberOfReactors, estimatedEfficiency, operationalRiskLevel, safetySystems, reactorConfigurations, createdAt, updatedAt | TechnicalPlantController |
| `UserDTO` | id, username, email, role, firstName, lastName | UserController |
| `UserAuthDTO` | extends UserDTO + passwordHash | UserService (autentificare) |

---

## 6. Repository-uri (Data Access Layer)

Fiecare repository primește o conexiune PDO în constructor și execută prepared statements direct. Nu există ORM, query builder sau lazy loading.

### 6.1 `LogRepository`
- `save(Log $log): void` — INSERT în logs
- `findRecent(int $limit, ?string $level, int $offset): array` — SELECT cu filtrare nivel + paginare offset
- `countByLevel(?string $level): int` — COUNT cu filtrare
- `purgeOlderThan(int $days): int` — DELETE logs mai vechi de N zile
- `findAfter(string $afterId, int $limit, ?string $level): array` — Cursor-based pagination

### 6.2 `UserRepository`
- `save(User $user): void` — INSERT
- `findAll(): array` — SELECT * ORDER BY id DESC
- `findByEmail(string $email): ?User` — SELECT pentru login
- `findById(string $id): ?User` — SELECT fără password_hash
- `findByUsername(string $username): ?User` — SELECT cu password_hash
- `findAllForAdmin(): array` — SELECT cu created_at
- `updateRole(string $id, string $role): void` — UPDATE role
- `delete(string $id): void` — DELETE
- `countByRole(string $role): int` — COUNT

### 6.3 `PlantRepositoryFacade`
Fațadă peste 4 repository-uri subiacente (`DetailsPlantRepository`, `BasicPlantRepository`, `GeologicalPlantRepository`, `TechnicalPlantRepository`). Delegă toate apelurile către repository-ul corespunzător.

Metodă specială:
- `getPlantData(string $plantId): ?array` — Asamblează date complete (basic + geological + technical + reactor schemas) pentru raportul de fezabilitate

### 6.4 `DetailsPlantRepository`
- `findAll(): array` — LEFT JOIN cu `geological_data` pentru listare cu țară/coordonate
- `findByUser(string $userId): array` — Filtrat după created_by
- `getPlantsByStatus(array $data): array` — Filtrat după status
- `findById(string $plantId): ?Plant` — Cu JOIN
- `save(Plant $plant): void` — INSERT
- `updateStatus(array $data, string $plantId): void` — UPDATE status
- `update(Plant $plant): void` — UPDATE name + status

### 6.5 `BasicPlantRepository`
- `findByPlantId(string $plantId): ?BasicPlantData`
- `save(BasicPlantData $data): void`
- `update(BasicPlantData $data): void`

### 6.6 `GeologicalPlantRepository`
- `findByPlantId(string $plantId): ?GeologicalPlantData`
- `save(GeologicalPlantData $data): bool` — ON CONFLICT DO NOTHING
- `update(GeologicalPlantData $data): void` — Update toate câmpurile

### 6.7 `TechnicalPlantRepository`
- `findByPlantId(string $plantId): ?TechnicalPlantData` — 2 queries: 1 pentru technical_data, 1 pentru reactor_schema + reactor_plant_data
- `save(TechnicalPlantData $data): void` — Multiple queries în tranzacție: INSERT technical_data + INSERT reactor_plant_data + INSERT reactor (auto-generare reactoare)
- `update(TechnicalPlantData $data): void` — UPDATE technical_data + DELETE reactor_plant_data + re-INSERT + DELETE auto-reactoare + re-INSERT
- `getReactorSchemaByDetails(string $reactorType, string $coolingType): ReactorSchema` — Caută combinația în catalog
- `getSchemasByTechnicalDataId(string $technicalDataId): array` — JOIN reactor_plant_data + reactor_schema

### 6.8 `FeasibilityRepository`
- `saveReport(string $plantId, array $reportResult): bool` — INSERT cu JSONB
- `getLatestReportByPlantId(string $plantId): ?array` — Ultimul raport

### 6.9 `ReactorRepository`
- `findById(string $id): ?Reactor`
- `findByPlantId(string $plantId): array`
- `findAll(): array`
- `findAllFromApprovedPlants(): array` — JOIN cu power_plants WHERE status=APPROVED
- `save(Reactor $r): void` — INSERT 18 coloane
- `update(Reactor $r): void` — UPDATE
- `delete(string $id): void` — DELETE

### 6.10 `SensorRepository`
- `insertBulk(string $reactorId, array $templates): void` — Bulk INSERT pentru generare senzori din template-uri
- `findById(string $id): ?ReactorSensor`
- `findByReactorId(string $reactorId): array`
- `updateCurrentValue(string $sensorId, float $value)` — UPDATE current_value + last_reading_at
- `update(ReactorSensor $s)` — Full update
- `save(ReactorSensor $s)` — INSERT
- `delete(string $id)` — DELETE
- `deleteByReactorAndCodes(string $reactorId, array $codes)` — DELETE în lot după coduri

### 6.11 `SensorTemplateRepository`
- `findByReactorType(ReactorType $type): array` — SELECT * FROM sensor_templates WHERE reactor_type = ?

### 6.12 `MeasurementsRepository`
- `save(Measurement $m): void` — INSERT 26 coloane
- `findLatestByReactorId(string $reactorId): ?Measurement`
- `findByReactorIdSince(string $reactorId, string $since): array`
- `deleteOlderThan(string $since): int` — Cleanup
- `aggregateHourly(?string $from, ?string $to, ?int $intervalSeconds): array` — INSERT INTO measurements_hourly ... SELECT ... GROUP BY ... ON CONFLICT DO UPDATE

### 6.13 `AlertRepository`
- `save(Alert $alert): void` — INSERT + logare
- `getUnreadAlertsForUser(?string $userId): array`
- `markAsRead(string $id): void`
- `markAllAlertsAsRead(): void` — Exclude PLANT_STATUS_CHANGE
- `markAllPlantEventsAsRead(): void` — Doar PLANT_STATUS_CHANGE
- `saveReactorAlert(array $data): void` — INSERT reactor_alerts + logare
- `savePlantEvent(string $plantId, string $type, string $message): void`
- `getUnreadReactorAlerts(int $limit): array`
- `getPlantEvents(): array`
- `dismissApproval(string $plantId): void` — Creează eveniment DISMISSED_APPROVAL
- `getDismissedApprovalPlantIds(): array` — Filtrare notificări aprobare
- `getPlantOwnerEmail(string $plantId): ?string` — JOIN users + power_plants

### 6.14 `ApprovalRepository`
- `updatePlantStatus(string $plantId, string $newStatus): void`
- `findPlantStatusById(string $plantId): ?string`

---

## 7. Servicii (Business Logic Layer)

### 7.1 `SensorService`
**Dependencies**: `SensorRepository`, `SensorTemplateRepository`, `ReactorRepository`

| Metodă | Descriere |
|---|---|
| `populateSensorsForReactor(string $reactorId, string $reactorTypeString)` | Șterge senzorii existenți după cod, apoi generează senzori noi din template-urile corespunzătoare tipului de reactor. Fiecare senzor primește setări implicite de praguri. |
| `getSensor(string $id): ?SensorDetailsDTO` | Returnează detalii complete ale unui senzor |
| `getSensorsByReactor(string $reactorId): array` | Returnează toți senzorii unui reactor (format simplify: SensorListDTO) |
| `createSensor(array $data): string` | Validează datele (uuid, cod, tip), creează entitate, salvează |
| `updateSensor(string $id, array $data): void` | Actualizează câmpurile editabile (cod, tip, status, descriere, praguri, calibrare) |
| `deleteSensor(string $id): void` | Șterge senzor după ID |
| `getStreamData(string $reactorId): ReactorStreamDTO` | Pentru SSE — returnează datele curente ale reactorului și senzorilor săi |

### 7.2 `ReactorService`
**Dependencies**: `ReactorRepository`, `PlantRepositoryFacade`

| Metodă | Descriere |
|---|---|
| `getReactor(string $id): ?ReactorDetailsDTO` | Detalii complete reactor |
| `getReactorsByPlant(string $plantId): array` | Listă reactoare per centrală |
| `getAllReactors(): array` | Toate reactoarele |
| `createReactor(array $data): string` | Validează datele (plantId UUID, cod, tip, răcire), verifică planta să nu fie APPROVED, creează reactorul, returnează ID. |
| `updateReactor(string $id, array $data): void` | Actualizează câmpurile reactorului. Fără verificare APPROVED — editarea este permisă pe orice status. |
| `deleteReactor(string $id): void` | Verifică planta să nu fie APPROVED, apoi șterge. |

### 7.3 `PlantServiceFacade`
**Dependencies**: `PlantRepositoryFacade`

Fațadă principală care orchestrează toate operațiile asupra centralelor. Creează intern 4 servicii specializate: `DetailsPlantService`, `BasicPlantService`, `GeologicalPlantService`, `TechnicalPlantService`.

| Metodă | Descriere |
|---|---|
| `updatePlantStatus(string $plantId, string $status)` | Schimbă statusul unei centrale |
| `submitForReview(string $plantId, string $userId): bool` | Trimite o centrală DRAFT în REVIEW. Verifică: există, e DRAFT, aparține utilizatorului, are profil complet. |
| `reopenDraft(string $plantId, string $userId): bool` | Redeschide o centrală REJECTED/REVIEW în DRAFT |
| `getAllPowerPlants(): array` | Toate centralele |
| `getMyPowerPlants(string $userId): array` | Centralele utilizatorului curent |
| `getPendingApprovalsList(): array` | Centrale în REVIEW |
| `getPlantsByStatus(array $data): array` | Filtrare după status |
| `getCountries(): array` | Lista țărilor din constantă |
| `plantPreviewCoordinates(float $lat, float $lon): CoordinatesPreviewResponseDTO` | Geolocație automată (BigDataCloud, USGS, Open-Meteo, SoilGrids) |
| `getPlantDetailsById(string $plantId): ?Plant` | Detalii centrală |
| `savePlantDetails(array $data): CreateDataResponseDTO` | Creează centrală nouă (DRAFT) |
| `updateStatus(array $data, string $plantId)` | Validează și actualizează status |
| `updatePlantDetails(array $data, string $plantId)` | Actualizează nume centrală (doar DRAFT) |
| `getBasicDataByPlantId`, `saveBasicData`, `updateBasicData` | Delegă către BasicPlantService |
| `previewGeologicalLocation(float $lat, float $lon): GeoLocationPreviewDTO` | Geolocație avansată |
| `getGeologicalDataByPlantId`, `saveGeologicalData`, `updateGeologicalData` | Delegă către GeologicalPlantService |
| `getTechnicalDataByPlantId`, `saveTechnicalData`, `updateTechnicalData` | Delegă către TechnicalPlantService |
| `getCompletePlantProfile(string $plantId): array` | Asamblează toate datele (details + basic + geological + technical) |

### 7.4 `DetailsPlantService`
**Dependencies**: `PlantRepositoryFacade`

| Metodă | Descriere |
|---|---|
| `savePlantDetails(array $data)` | Creează Plant cu UUID, status DRAFT, createdBy din sesiune |
| `updateStatus(array $data, string $plantId)` | Update status prin fațadă |
| `updatePlantDetails(array $data, string $id)` | Update nume (doar DRAFT) |
| `getAllPowerPlants()`, `getMyPowerPlants()`, `getPlantsByStatus()`, `getCountries()`, `findById()` | Delegări |

### 7.5 `BasicPlantService`
**Dependencies**: `PlantRepositoryFacade`

- `findByPlantId(string $plantId)` — Interogare date de bază
- `save(array $data, string $plantId)` — Salvare date de bază (capacity_mw, construction_duration_years, description)
- `update(array $data, string $plantId)` — Actualizare date de bază

### 7.6 `GeologicalPlantService`
**Dependencies**: `PlantRepositoryFacade`

| Metodă | Descriere |
|---|---|
| `findByPlantId(string $plantId)` | Interogare date geologice |
| `getGeologicalData(string $plantId): ?GeologicalPlantDataDTO` | Returnează DTO |
| `runAutoGeolocation(float $lat, float $lon): GeoLocationPreviewDTO` | Apeluri API externe: BigDataCloud (reverse geocode + country), USGS (seismic), Open-Meteo (flood risk, groundwater), SoilGrids (soil type). Completează toate câmpurile geografice automat. |
| `save(array $data, string $plantId)` | Salvare cu validare (lat/lon, enums). Dacă lat/lon prezente, rulează auto-geolocație să completeze câmpurile lipsă. |
| `update(array $data, string $plantId)` | Actualizare câmpuri + validare |

### 7.7 `TechnicalPlantService`
**Dependencies**: `PlantRepositoryFacade`

- `findByPlantId(string $plantId)` — Interogare date tehnice
- `save(array $data, string $plantId)` — Salvare (estimatedEfficiency, operationalRiskLevel, reactorConfigurations). Creează ReactorSchema pentru fiecare configurație.
- `update(array $data, string $plantId)` — Actualizare

### 7.8 `AlertService`
**Dependencies**: `AlertRepository`, `EmailService`

| Metodă | Descriere |
|---|---|
| `processSensorData(array $payload)` | Procesează date senzor: dacă tipul e NORMAL, ignore; altfel creează Alert, salvează, trimite email proprietarului centralei. Tipuri: ALARM, ALERT, SCRAM. |
| `getActivePopups(?string $userId): array` | Alerte necitite |
| `dismissAlert(string $alertId)` | Marchează alertă ca citită |
| `dismissAllAlerts()` | Marchează toate alertele ca citite |
| `dismissAllPlantEvents()` | Marchează toate evenimentele centralei ca citite |
| `savePlantEvent(string $plantId, string $type, string $message)` | Salvează eveniment centrală |
| `dismissApproval(string $plantId)` | Respinge notificarea de aprobare |

### 7.9 `NotificationService`
**Dependencies**: `PlantServiceFacade`, `AlertService`, `AlertRepository`

| Metodă | Descriere |
|---|---|
| `getAlertNotifications(): array` | Agregă alertele active (exclude PLANT_STATUS_CHANGE) + reactor_alerts necitite. Deducplicație pe (plantă+tip+mesaj+minut). Sortează descrescător. |
| `getPlantNotifications(string $userRole): array` | Evenimente centrală. Pentru ADMIN, include cereri de aprobare nedimisionate. |
| `getAggregatedNotifications(string $userRole, string $userEmail): array` | Unifică alertele și notificările centralei |

### 7.10 `EmailService`
**Dependencies**: PHPMailer, variabile de mediu

| Metodă | Descriere |
|---|---|
| `sendAlert(array $data): bool` | Trimite email HTML prin SMTP (Mailtrap). Citește MAIL_HOST/PORT/USER/PASS din env. From: system@nuclear-plant.local. |

### 7.11 `LogService`
Singleton. **Dependencies**: `LogRepository` (creat prin `init(PDO)`)

| Metodă | Descriere |
|---|---|
| `init(PDO $pdo)` | Inițializează instanța singleton |
| `instance(): self` | Returnează instanța |
| `log(string $level, string $message, ?array $context, ?string $plantId, ?string $reactorId)` | Metoda principală. Creează Log cu userId din sesiune, source='backend', request URI, IP. Salvează prin repository. Scrie și în error_log. Trigger cleanup automat. |
| `debug/info/warning/error/critical(...)` | Metode conveniență |
| `logFromFrontend(string $level, string $message, ?array $context, ?string $userId)` | Log din frontend (source='frontend') |
| `maybeCleanup()` | La interval de 1 oră, șterge logs mai vechi de 30 zile |

### 7.12 `UserService`
**Dependencies**: `UserRepository`

| Metodă | Descriere |
|---|---|
| `registerUser(array $data)` | Validare: câmpuri negoale, email valid, lungimi, parolă ≥6 char, unique email/username. Hash BCRYPT. Rol: ADMIN dacă email @admin.ro, altfel OPERATOR. Salvare. |
| `getAllUsers(): array` | Toți utilizatorii |
| `authenticateUser(string $email, string $password): ?UserAuthDTO` | Găsește user după email, verifică parola, returnează DTO sau null |
| `getUserById(string $id): ?UserAuthDTO` | Utilizator după ID |
| `getAllUsersForAdmin(): array` | Toți utilizatorii pentru admin (cu created_at) |
| `updateUserRole(string $id, string $role)` | Validare rol, prevenire auto-schimbare rol, prevenire eliminare ultim ADMIN |
| `deleteUser(string $id)` | Prevenire auto-ștergere, prevenire ștergere ultim ADMIN |

### 7.13 `ApprovalService`
**Dependencies**: `ApprovalRepository`

| Metodă | Descriere |
|---|---|
| `approve(string $plantId)` | Validează planta e REVIEW, setează APPROVED |
| `reject(string $plantId)` | Validează planta e REVIEW, setează REJECTED |

### 7.14 `StatsService`
**Dependencies**: PDO direct

| Metodă | Descriere |
|---|---|
| `getAll(): array` | Statistici combinate: plante, reactoare, senzori, alerte |
| `plantStats(): array` | Total plante, grupări după status/țară/lună, medii eficiență/risc |
| `reactorStats(): array` | Total reactoare, grupări după tip/răcire/status, medii putere/uzură |
| `sensorStats(): array` | Total senzori, grupări după tip/status, senzori activi |
| `alertStats(): array` | Total alerte, grupări după severitate/tip, ultimele 30 zile |
| `getMeasurements(?string $reactorId, int $hours): array` | Măsurători orare pentru un reactor (sau toate) |

### 7.15 `RssService`
**Dependencies**: PDO direct + 5 repository-uri create intern

| Metodă | Descriere |
|---|---|
| `generatePlantsRssFeed(): string` | Generează RSS 2.0 XML cu toate centralele APPROVED/OPERATIONAL/REVIEW. Pentru fiecare reactor, creează un `<item>` cu nume, cod, tip, răcire, putere, coordonate, capacitate, descriere. |
| `getLatestUpdate(): ?string` | Cea mai recentă updated_at din toate tabelele centralei |

### 7.16 `FeasibilityService`
**Dependencies**: `PlantRepositoryFacade`, `AbstractFeasibilityChecker` (lanț Chain of Responsibility), `FeasibilityRepository`, `TransactionManager`

| Metodă | Descriere |
|---|---|
| `generateAndSaveReport(string $powerPlantId): ApiResponseDTO` | Preia datele plantei, rulează lanțul de verificare (geological → technical → scoring), salvează raportul |
| `getFeasibilityReport(string $powerPlantId): ApiResponseDTO` | Returnează ultimul raport |

### 7.17 `FeasibilityServiceFactory`
Creează lanțul complet de verificare:
```
GeologicalCriticalChecker → TechnicalCriticalChecker → ScoringChecker
```
Fiecare checker implementează Chain of Responsibility.

### 7.18 Lanțul de verificare fezabilitate

#### `GeologicalCriticalChecker`
Verifică erori critice geologice. Respinge (REJECTED) dacă:
- Lipsește date geologice
- Tip sol instabil: PEAT, SOFT_CLAY, LOOSE_SAND, SILT
- Incompatibilitate răcire-apă (once-through salt vs fresh, wet towers cu sare, cooling pond cu sare)
- Stabilitate seismică < 4.0
- Densitate populație > 500 loc/km²
- Scor transport < 3.0
- Debit apă < 20 m³/s
- Risc inundații > 8.0
- Nivel freatic < 2.0m

#### `TechnicalCriticalChecker`
Verifică erori critice tehnice. Respinge (REJECTED) dacă:
- Lipsește date tehnice
- Eficiență estimată > 45% (depășește limita Rankine) sau < 15% (neeconomic)
- Număr reactoare < 1 sau > 8 (limită siguranță)

#### `ScoringChecker`
Calculează NSVI (Nuclear Site Viability Index) — scor 0-100. Pentru fiecare tip de reactor prezent, instanțiază strategia de scor specifică (PWR/BWR/PHWR/FBR). Calculează media ponderată.
- NSVI ≥ 75 → APPROVED
- NSVI ≥ 50 → REVIEW (necesită revizuire)
- NSVI < 50 → REJECTED

### 7.19 Strategii de scor (Strategy Pattern)

#### `PwrStrategy`
Scor PWR. Penalizări pentru: eficiență scăzută, proximitate mare de apă, debit apă mic, durată construcție lungă, risc geologic mare.

#### `BwrStrategy`
Scor BWR. Penalizări pentru: stabilitate seismică sub prag, densitate populație peste prag, infrastructură transport slabă, eficiență scăzută, durată construcție lungă.

#### `PhwrStrategy`
Scor PHWR. Penalizări pentru: nivel freatic scăzut (risc tritiu), durată construcție lungă, eficiență scăzută, stabilitate seismică scăzută (vulnerabilitate Calandria).

#### `FbrStrategy`
Scor FBR. Penalizări pentru: risc inundații mare, stabilitate seismică scăzută, eficiență scăzută, durată construcție lungă.

Toate strategiile sunt configurate prin `config/feasibility-params.json` (praguri, ponderi, factori).

### 7.20 `SimulatorService`
**Dependencies**: `SensorRepository`, `MeasurementsRepository`, `ReactorRepository`

Rulează în buclă infinită ca daemon CLI. Pentru fiecare reactor din centrale APPROVED (exceptând reactoarele în SHUTDOWN/COLD_STANDBY/PLANNED_OUTAGE/EMERGENCY_SHUTDOWN), creează un simulator specific tipului de reactor și apelează `tick()` la fiecare 3 secunde.

| Metodă | Descriere |
|---|---|
| `attachObserver(ObserverInterface $observer)` | Înregistrează observator (AlertObserver, NotificationObserver, ScramObserver) |
| `run()` | Bucla principală — iterează reactoare, tick(), sleep |
| `shouldSimulate(Reactor $reactor): bool` | Filtrează reactoare neoperaționale |
| `getSimulator(Reactor $reactor): AbstractReactorSimulator` | Factory: returnează/crează simulatorul corespunzător tipului reactor |

### 7.21 `AbstractReactorSimulator` (Template Method Pattern)
**Dependencies**: `SensorRepository`, `MeasurementsRepository`, `ReactorRepository`

Fluxul principal `tick(string $reactorId)`:
1. Încarcă reactorul și senzorii din DB
2. `generateValues()` — aplică strategii de generare per tip senzor
3. `applyPhysicalCorrelation()` — abstract, implementat per tip reactor (PWR/BWR/PHWR/FBR)
4. `ThresholdChecker::checkAll()` — verifică praguri pentru fiecare senzor
5. Notifică observatorii despre violări
6. `MeasurementBuilder::build()` — construiește măsurătoarea compusă
7. Dacă EMERGENCY → setează reactor pe EMERGENCY_SHUTDOWN
8. Persistă noile valori ale senzorilor și măsurătoarea
9. Aplică uzură (`ReactorWearCalculator`)
10. Loghează tick-ul

### 7.22 Simulatoare specifice (4 tipuri)

#### `PwrSimulator`
Corelații fizice PWR: temperatura ieșire coolant urmează puterea; temperatura intrare coolant = ieșire - ~30°C; presiunea primară corelată cu temperatura; debit corectat cu densitatea; temperatura combustibil urmează puterea; activitate primară crescută când coolant > 310°C; presiunea aburului urmează temperatura primară.

#### `BwrSimulator`
Corelații fizice BWR: puterea urmează debitul de recirculare (^0.8); temperatura ieșire miez urmează puterea; presiunea de saturație urmează temperatura; temperatura combustibil urmează puterea; debitul abur urmează puterea; nivel apă vas scade la putere mare (efect void); activitate crescută la temperatură mare.

#### `PhwrSimulator`
Corelații fizice PHWR: temperatura coolant urmează puterea; temperatura moderator constantă (~70°C); presiunea primară corelată cu temperatura; activitate tritiu în moderator crește cu puterea; debit coolant urmează presiunea (sqrt).

#### `FbrSimulator`
Corelații fizice FBR: temperatura ieșire sodium urmează puterea; temperatura intrare sodium = ieșire - ~150°C; presiunea sodium primar aproape constantă; activare Na-24 proporțională cu puterea + decay; feedback Doppler (coeficient temperatură negativ reduce puterea la combustibil > 800°C); temperatura combustibil urmează puterea; debit sodium urmează puterea; vibrații pompă urmează debit^2.

### 7.23 Strategii de generare valori senzori (12 strategii)

| Strategie | Comportament |
|---|---|
| `ThermocoupleStrategy` | Temperatură — pas Gaussian mic (0.3% din range), forță de tracțiune spre centru. Minim -273.15°C. |
| `NeutronDetectorStrategy` | Flux neutroni — zgomot statistic + drift + spike-uri ocazionale de 8% (2% probabilitate) + tracțiune la normal. Minim 0. |
| `PressureTransducerStrategy` | Presiune — pas Gaussian + drop-uri ocazionale 2% (0.5% probabilitate) + tracțiune. Minim 0. |
| `FlowMeterStrategy` | Debit — pas Gaussian + drop-uri 15% (0.2% probabilitate pompare) + tracțiune. Minim 0. |
| `RadiationMonitorStrategy` | Radiație — pas Gaussian + spike-uri 12% (0.8% probabilitate) + decay la bază (5% deasupra minim). Minim 0. |
| `VibrationSensorStrategy` | Vibrații — zgomot de fond + evenimente mecanice 25% (1% probabilitate) + amortizare la bază (10% deasupra minim). Minim 0. |
| `LevelSensorStrategy` | Nivel — pas Gaussian mic + scurgeri 0.5% (0.3% probabilitate) + forță puternică de control la setpoint (mijloc). Minim 0. |
| `ActivityMonitorStrategy` | Activitate — pas Gaussian + spike-uri 20% (0.4% probabilitate) + decay la bază (8% deasupra minim). Minim 0. |
| `SeismicSensorStrategy` | Seismic — zgomot foarte mic + evenimente seismice extrem de rare (0.05%) de 40% + afterșocuri. Minim 0. |
| `HydrogenDetectorStrategy` | Hidrogen — zgomot mic + acumulări 1.5% (0.6% probabilitate) + recombinare decay (5% deasupra minim). Minim 0. |
| `ValvePositionStrategy` | Valvă — 5% șansă mișcare per tick. 1-3 pași direcție. Dacă departe de setpoint (>10), tracțiune spre el. Minim 0. |
| `PumpSpeedStrategy` | Pompă — pas Gaussian + trip-uri 20% (0.2% probabilitate). Dacă viteză < 30% nominal, boost; altfel tracțiune la nominal. Minim 0. |

### 7.24 Observatori (Observer Pattern)

#### `ViolationEvent`
Valoare obiect care capturează: severitatea (WARNING/ALERT/EMERGENCY), valoarea senzorului, senzorul, reactorId, plantId, pragul atins, timestamp.

#### `AlertObserver`
Debounce 60s. Salvează alerte în `reactor_alerts` și `alerts`.

#### `NotificationObserver`
Debounce 300s pentru EMERGENCY. Salvează alerta + loghează. Pentru EMERGENCY, trimite email proprietarului centralei.

#### `ScramObserver`
Debounce 60s. Pentru EMERGENCY: setează reactor EMERGENCY_SHUTDOWN. Pentru ALERT: setează UNPLANNED_OUTAGE. Loghează.

---

## 8. Controllere (HTTP Layer)

### 8.1 Lista completă (16 controllere)

| Controller | Rute | Metode |
|---|---|---|
| `AlertController` | 3 | receiveAlert, getUnread, markRead |
| `ApprovalController` | 1 | updateStatus (ADMIN) |
| `EmailController` | 1 | handleSendEmail |
| `FeasibilityController` | 2 | generate, getLastByPlantId |
| `LogController` | 2 | getLogs (ADMIN), receiveFrontendLog |
| `NotificationController` | 1 | getNotifications |
| `ReactorController` | 6 | getAllReactors, getReactor, getReactorsByPlant, createReactor, updateReactor, deleteReactor |
| `RssController` | 1 | handleGetPlantsFeed |
| `SensorController` | 7 | stream (SSE), getSensorsByReactor, getSensor, createSensor, updateSensor, deleteSensor, populateSensors |
| `StatsController` | 2 | getAll, getMeasurements |
| `UserController` | 9 | handleRegister, handleLogin, handleLogout, getUserStatus, (5 x admin) |
| `BasicPlantController` | 3 | getBasicPlantData, createBasicPlantData, updateBasicPlantData |
| `DetailsPlantController` | 13 | getCountries, getPlant, getPlantDetails, getMyPowerPlants, getPowerPlantsList, getPendingApprovalsList, getPlantsByStatus, getPowerPlantsMapData, handleSavePlantDetails, updateStatus, submitForReview, reopenDraft, handleUpdatePlantDetails, previewCoordinates |
| `GeologicalPlantController` | 3 | getGeologicalPlantData, createGeologicalPlantData, updateGeologicalPlantData |
| `ImportExportController` | 6 | exportSingle/Multiple (JSON), exportSingleCsv/MultipleCsv, importSingle/Multiple |
| `TechnicalPlantController` | 3 | getTechnicalPlantData, createTechnicalPlantData, updateTechnicalPlantData |

**Total: 63 de metode publice.**

### 8.2 Detalii controllere principale

#### `AlertController`
- `POST /api/alerts/receive` — primește date senzor, procesează alerte (autentificat)
- `GET /api/alerts/unread` — alerte necitite pentru utilizator (autentificat)
- `PUT /api/alerts/{id}/read` — marchează citit (acceptă 'all', 'plant-all', 'approval_{plantId}', sau ID concret)

#### `ApprovalController`
- `PATCH /api/power-plants/{id}/admin-status` — ADMIN: aprobă/respinge o centrală

#### `FeasibilityController`
- `POST /api/power-plants/{id}/feasibility` — generează raport fezabilitate
- `GET /api/power-plants/{id}/feasibility` — ultimul raport

#### `ReactorController`
- `GET /api/reactors` — toate reactoarele
- `GET /api/reactors/{id}` — un reactor
- `GET /api/power-plants/{plantId}/reactors` — reactoare per centrală
- `POST /api/reactors` — creează reactor (generează senzori din template)
- `PUT /api/reactors/{id}` — actualizează
- `DELETE /api/reactors/{id}` — șterge

#### `SensorController`
- `GET /api/reactors/{id}/stream` — SSE endpoint (stream continuu)
- `GET /api/reactors/{reactorId}/sensors` — senzorii unui reactor
- `GET /api/sensors/{id}` — un senzor
- `POST /api/sensors` — creează senzor
- `PUT /api/sensors/{id}` — actualizează
- `DELETE /api/sensors/{id}` — șterge
- `POST /api/reactors/{reactorId}/sensors/populate` — generează senzori din template

#### `UserController`
- `GET /register` / `POST /register` — înregistrare
- `GET /login` / `POST /login` — autentificare
- `GET /logout` — deconectare
- `GET /api/user/status` — status utilizator curent
- `GET /api/admin/users` — listă utilizatori (ADMIN)
- `GET /api/admin/users/{id}` — detalii utilizator (ADMIN)
- `PATCH /api/admin/users/{id}/role` — actualizare rol (ADMIN)
- `DELETE /api/admin/users/{id}` — ștergere utilizator (ADMIN)

#### `DetailsPlantController` (cel mai complex, 13 metode)
- `GET /api/countries` — listă țări (public)
- `GET /api/power-plants/{id}` — profil complet centrală (public)
- `GET /api/power-plants` / `list` — listă centrale (public)
- `GET /api/power-plants/map-data` — date pentru hartă (public)
- `POST /api/power-plants/coordinates-preview` — previzualizare geolocație (public)
- `GET /api/power-plants/my` — centralele mele (autentificat)
- `GET /api/power-plants/pending-approvals` — aprobări pendinte (autentificat)
- `GET /api/power-plants/filter` — filtrare după status (public cu restricție)
- `POST /api/power-plants` — creează centrală
- `PATCH /api/power-plants/{id}/status` — actualizare status
- `PATCH /api/power-plants/{id}/submit-review` — trimite în REVIEW
- `PATCH /api/power-plants/{id}/reopen` — redeschide în DRAFT
- `PUT /api/power-plants/{id}/details` — actualizare detalii

#### `ImportExportController`
- `GET /api/power-plants/{id}/export` — export JSON (o centrală)
- `GET /api/power-plants/export` — export JSON (multiple)
- `GET /api/power-plants/{id}/export/csv` — export CSV/ZIP (o centrală)
- `GET /api/power-plants/export/csv` — export CSV/ZIP (multiple)
- `POST /api/power-plants/import` — import JSON (o centrală)
- `POST /api/power-plants/import/batch` — import JSON (multiple, tranzacțional)

---

## 9. Frontend

### 9.1 Organizare

```
frontend/
├── pages/                    # Pagini HTML statice
│   ├── index.html            # Pagina principală (hartă)
│   ├── login.html
│   ├── register.html
│   ├── power-plants/         # Wizard creare/editare centrală
│   │   ├── create.html
│   │   ├── details.html
│   │   ├── basics.html
│   │   ├── geological.html
│   │   ├── technical.html
│   │   └── finish.html
│   ├── my-plants.html        # Centralele mele
│   ├── reactors/             # Management reactoare
│   │   ├── list.html
│   │   ├── create.html
│   │   └── edit.html
│   └── ...
├── modules/
│   ├── core/                 # Nucleu reutilizabil
│   │   ├── router.js         # Ruter SPA custom
│   │   ├── auth.js           # Gestiune sesiune/token
│   │   ├── api.js            # Helper fetch cu CSRF
│   │   ├── csrf.js           # Citire token CSRF
│   │   ├── form-handler.js   # Validare + submit formulare
│   │   ├── validator.js      # Reguli validare
│   │   └── loader.js         # Loader animat
│   ├── pages/                # Logică per pagină
│   │   ├── power-plants/
│   │   │   ├── my-plants.js
│   │   │   ├── create.js
│   │   │   ├── details.js
│   │   │   ├── basics.js
│   │   │   ├── geological.js
│   │   │   ├── technical.js
│   │   │   └── finish.js
│   │   ├── reactors/
│   │   │   ├── list.js
│   │   │   ├── create.js
│   │   │   └── edit.js
│   │   └── ...
│   └── styles/               # CSS modular
│       └── main.css
```

### 9.2 Componente reutilizabile

| Modul | Funcționalitate |
|---|---|
| `router.js` | Parsează URL, încarcă pagini lazy (dynamic import), gestionează istoric |
| `api.js` | Fetch wrapper cu setare automată antete JSON, CSRF, erori |
| `auth.js` | Gestionează sesiunea, verifică timeout, redirecționează la login |
| `csrf.js` | Citește token CSRF din meta tag DOM |
| `form-handler.js` | Validare client-side, submit cu API, afișare erori |
| `validator.js` | Reguli de validare configurabile (required, email, minLength, etc.) |
| `loader.js` | Loader animat (CSS) pentru operații asincrone |

### 9.3 Comunicații

- **GET/POST/PUT/DELETE** → `fetch()` cu JSON payload + CSRF token
- **SSE** → `EventSource` pentru date în timp real (senzori)
- **Upload fișiere** → `FormData` cu multipart
- **Autentificare** → sesiune PHP, CSRF token în antet `X-CSRF-Token`

---

## 10. Daemon-uri CLI

### 10.1 `SensorSimulator` (Simulator)
- **Ciclu**: fiecare `SIMULATOR_TICK_INTERVAL = 3` secunde
- **Descriere**: Itterează toate reactoarele din centrale APPROVED (exceptând reactoare neoperaționale). Pentru fiecare reactor activ, creează simulatorul adecvat (PWR/BWR/PHWR/FBR) și execută un tick de simulare.
- **Generare valori**: 12 strategii (Thermocouple, NeutronDetector, PressureTransducer etc.)
- **Verificare praguri**: SCRAM (EMERGENCY), ALARM (ALERT), ALERT (WARNING)
- **Notificări**: Observer Pattern → AlertObserver, NotificationObserver, ScramObserver
- **Persistență**: Salvează valorile senzorilor + măsurătorile compuse + alertele
- **Uzură**: Calculează wear_delta pe baza puterii și duratei de viață
- **Semnale**: Handler SIGINT/SIGTERM pentru oprire controlată

### 10.2 `Aggregator`
- **Ciclu**: `AGGREGATOR_INTERVAL = 60` secunde
- **Descriere**: Agregă măsurătorile brute din `measurements` în `measurements_hourly`. Calculează medii, minime, maxime, sume per reactor per oră.
- **Upsert**: `ON CONFLICT (reactor_id, hour) DO UPDATE`

### 10.3 `Cleanup`
- **Ciclu**: `CLEANUP_INTERVAL = 3600` secunde (1 oră)
- **Descriere**: Șterge măsurători mai vechi de 1 oră din `measurements`. Păstrează datele agregate în `measurements_hourly`.

---

## 11. Securitate

| Măsură | Implementare |
|---|---|
| **SQL Injection** | Toate query-urile folosesc PDO prepared statements cu placeholders |
| **XSS** | Output escapé cu `htmlspecialchars()` în toate răspunsurile HTML; JSON serializat nativ |
| **CSRF** | Token generat per sesiune, expus în `<meta name="csrf-token">`, verificat pe fiecare mutație API (except `/api/logs/frontend`) |
| **Autentificare** | Sesiuni PHP cu regenerare session ID la login |
| **Autorizare** | Middleware `requireRole('ADMIN')`, `requireAuth()` verificat pe fiecare rută protejată |
| **CORS** | Headere configurate restrictiv (doar originile permise) |
| **HSTS** | Header `Strict-Transport-Security` în Apache |
| **Secure Cookies** | Cookie-uri de sesiune cu flag-uri `HttpOnly`, `Secure`, `SameSite` |
| **Parole** | Hash BCRYPT (cost implicit 10) |
| **Logger securitate** | Toate acțiunile ADMIN sunt logate cu nivel INFO, context (acțiune, user, IP) |
| **UUID** | Chei primare UUID în loc de auto-increment (previne enumerarea) |

---

## 12. Pattern-uri de Design

| Pattern | Locație | Scop |
|---|---|---|
| **Front Controller** | `index.php` + Router | Toate requesturile HTTP printr-un singur entry point |
| **MVC-like** | Frontend (View), Controller, Module | Separarea logicii de prezentare și business |
| **Repository** | `*Repository.php` (14 clase) | Abstracción peste PDO queries |
| **Service Layer** | `*Service.php` (17 clase) | Logica de business complexă |
| **Facade** | `PlantServiceFacade`, `PlantRepositoryFacade` | Simplifică interacțiunea cu subsisteme complexe |
| **Observer** | `AlertObserver`, `NotificationObserver`, `ScramObserver` | Notificări la evenimente din simulare |
| **Chain of Responsibility** | `GeologicalCriticalChecker → TechnicalCriticalChecker → ScoringChecker` | Lanț de verificări pentru fezabilitate |
| **Strategy** | `PwrStrategy`, `BwrStrategy`, `PhwrStrategy`, `FbrStrategy` | Algoritmi de scor specifici per tip reactor |
| **Strategy (Generare)** | `ThermocoupleStrategy`, `NeutronDetectorStrategy` etc. (12 clase) | Algoritmi de generare valori senzori |
| **Template Method** | `AbstractReactorSimulator::tick()` | Șablon fix de simulare, corelații fizice implementate în subclase |
| **Singleton** | `LogService`, `Database` | Conexiune unică per request |
| **DTO** | 26 DTO-uri în `backend/src/Dto/` | Transfer de date între straturi |
| **Event Stream** | SSE (`SensorController::stream()`) | Stream unidirecțional server→client |
| **Factory** | `SensorGeneratorFactory`, `FeasibilityServiceFactory` | Creare obiecte cu logică condițională |

---

## 13. Decizii Tehnice

### 13.1 Fără framework-uri (client sau server)
- **Motiv**: control total asupra arhitecturii, performanță, educațional
- **Impact**: cod mai explicit, fără overhead, dar necesită implementare manuală a rutării, validării, securității

### 13.2 PostgreSQL vs. MySQL
- **Motiv**: suport nativ UUID, JSONB, funcții avansate (date_trunc, generate_series), integritate referențială puternică
- **Impact**: mai potrivit pentru date complexe și relaționale

### 13.3 UUID în loc de SERIAL (auto-increment)
- **Motiv**: securitate (imposibil de enumerat ID-uri), distribuție (generare offline)
- **Impact**: 16 bytes vs 4 bytes, indexare ușor mai lentă

### 13.4 SSE vs. WebSocket
- **Motiv**: SSE suficient pentru stream unidirecțional (senzori → browser); mai simplu, reconnects automate
- **Impact**: nu suportă comunicație bidirecțională, dar nu e necesară

### 13.5 Tabele 1:1 separate vs. tabel monolithic
- **Motiv**: date extensibile gestionate în pași separați (wizard); separare clară responsabilități
- **Impact**: interogări cu JOIN-uri, dar flexibilitate

### 13.6 Enum-uri PostgreSQL vs. constrângeri CHECK
- **Motiv**: enum-urile reutilizabile și extensibile
- **Impact**: schimbarea necesită ALTER TYPE ... ADD VALUE

### 13.7 Observer Pattern pentru alerte
- **Motiv**: notificări decuplate de logica principală de simulare
- **Impact**: adăugarea unui nou observer (SMS, webhook) fără modificarea SimulatorService

### 13.8 Lanțul de verificare vs. if-uri lungi
- **Motiv**: extensibilitate — adăugarea unui nou checker (e.g., EnvironmentalChecker) fără modificarea codului existent
- **Impact**: ușor overhead de obiecte, dar mult mai mentenabil

### 13.9 Session write close în SSE
- **Motiv**: SSE endpoint ține conexiunea deschisă; fără `session_write_close()`, toate celelalte requesturi de la același utilizator sunt blocate (PHP session lock)
- **Impact**: rezolvă concurența, dar sesiunea devine read-only în timpul stream-ului

### 13.10 Auto-generare reactoare + senzori
- **Motiv**: la crearea configurației tehnice a unei centrale, reactoarele și senzorii sunt generați automat pe baza template-urilor; reduce erorile manuale
- **Impact**: complexitate în TechnicalPlantRepository::save() — tranzacție cu multiple INSERT-uri

### 13.11 Feasibility params externalizate în JSON
- **Motiv**: configurarea pragurilor de fezabilitate fără modificare cod
- **Impact**: un singur fișier `config/feasibility-params.json` cu toți parametrii per tip reactor

---

## 14. Metrici

| Categorie | Elemente | Total |
|---|---|---|
| **Rute API** | Definite în `config/routes.php` | 67 |
| **Controllere** | `backend/src/Controllers/` | 16 |
| **Servicii** | `backend/src/Services/` | 17 |
| **Repository-uri** | `backend/src/Repositories/` | 14 |
| **Entități** | `backend/src/Entities/` | 12 |
| **Enum-uri PHP** | `backend/src/Enums/` | 8 |
| **DTO-uri** | `backend/src/Dto/` | 26 |
| **Tabele DB** | PostgreSQL | 18 (15 date + 3 enums) |
| **Enum-uri PostgreSQL** | CREATE TYPE | 8 |
| **Linii cod backend** | Estimat | ~7000 |
| **Linii cod frontend** | Estimat | ~9000 |
| **SIMULATOR_TICK_INTERVAL** | Configurabil `config/scripts.php` | 3s |
| **AGGREGATOR_INTERVAL** | Configurabil | 60s |
| **CLEANUP_INTERVAL** | Configurabil | 3600s |

---

## 15. Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
