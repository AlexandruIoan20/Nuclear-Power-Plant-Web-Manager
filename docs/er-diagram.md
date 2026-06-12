# Diagrama Entity-Relationship (ER) — Nuclear Power Plant Web Manager

> Versiune 1.0 — PostgreSQL 15
> 18 tabele, 8 enum-uri, 7 indecși

---

## Cuprins

1. [Diagrama ER (Mermaid)](#1-diagrama-er-mermaid)
2. [Tipuri Enum PostgreSQL](#2-tipuri-enum-postgresql)
3. [Tabela `users`](#3-tabela-users)
4. [Tabela `power_plants`](#4-tabela-power_plants)
5. [Tabela `basic_data`](#5-tabela-basic_data)
6. [Tabela `geological_data`](#6-tabela-geological_data)
7. [Tabela `technical_data`](#7-tabela-technical_data)
8. [Tabela `feasibility_reports`](#8-tabela-feasibility_reports)
9. [Tabela `reactor`](#9-tabela-reactor)
10. [Tabela `reactor_schema`](#10-tabela-reactor_schema)
11. [Tabela `reactor_plant_data`](#11-tabela-reactor_plant_data)
12. [Tabela `control_rods`](#12-tabela-control_rods)
13. [Tabela `reactor_sensors`](#13-tabela-reactor_sensors)
14. [Tabela `sensor_readings`](#14-tabela-sensor_readings)
15. [Tabela `sensor_templates`](#15-tabela-sensor_templates)
16. [Tabela `measurements`](#16-tabela-measurements)
17. [Tabela `measurements_hourly`](#17-tabela-measurements_hourly)
18. [Tabela `reactor_alerts`](#18-tabela-reactor_alerts)
19. [Tabela `alerts`](#19-tabela-alerts)
20. [Tabela `logs`](#20-tabela-logs)
21. [Indexuri](#21-indexuri)
22. [Relații Cheie](#22-relații-cheie)

---

## 1. Diagrama ER (Mermaid)

```mermaid
erDiagram
  users ||--o{ power_plants : "created_by"
  power_plants ||--o| basic_data : ""
  power_plants ||--o| geological_data : ""
  power_plants ||--o| technical_data : ""
  power_plants ||--o{ feasibility_reports : ""
  power_plants ||--o{ reactor : ""
  power_plants ||--o{ alerts : ""
  power_plants ||--o{ reactor_alerts : ""

  technical_data ||--o{ reactor_plant_data : ""
  reactor_schema ||--o{ reactor_plant_data : ""

  reactor ||--o{ reactor_sensors : ""
  reactor ||--o{ measurements : ""
  reactor ||--o{ measurements_hourly : ""
  reactor ||--o{ control_rods : ""
  reactor ||--o{ reactor_alerts : ""

  reactor_sensors ||--o{ sensor_readings : ""

  sensor_templates }o--|| reactor_type : ""

  users {
    uuid id PK
    varchar username "UNIQUE"
    varchar first_name
    varchar last_name
    varchar email "UNIQUE"
    varchar password_hash
    user_roles role
    timestamp created_at
  }

  power_plants {
    uuid id PK
    varchar name
    power_plant_status status
    timestamp created_at
    timestamp updated_at
    uuid created_by FK
  }

  basic_data {
    uuid id PK
    uuid power_plant_id FK "UNIQUE"
    decimal capacity_mw
    int construction_duration_years
    text description
    timestamp created_at
    timestamp updated_at
  }

  geological_data {
    uuid id PK
    uuid power_plant_id FK "UNIQUE"
    varchar country
    decimal latitude
    decimal longitude
    soil_types soil_type
    water_source_types water_source_type
    decimal seismic_stability
    decimal flood_risk
    decimal groundwater_level
    decimal water_proximity
    decimal water_flow_rate
    decimal population_density
    decimal transport_infrastructure_score
    decimal geological_risk_score
    timestamp created_at
    timestamp updated_at
  }

  technical_data {
    uuid id PK
    uuid power_plant_id FK "UNIQUE"
    int number_of_reactors
    decimal estimated_efficiency
    decimal operational_risk_level
    jsonb safety_systems
    timestamp created_at
    timestamp updated_at
  }

  reactor_schema {
    uuid id PK
    reactor_types reactor_type
    cooling_types cooling_type
  }

  reactor_plant_data {
    uuid technical_data_id PK, FK
    uuid reactor_schema_id PK, FK
    int number_of_reactors
  }

  feasibility_reports {
    uuid id PK
    uuid power_plant_id FK
    jsonb deficiencies
    jsonb errors
    power_plant_status status
    decimal nsvi_score
    text message
    timestamp created_at
  }

  reactor {
    uuid id PK
    uuid power_plant_id FK
    varchar reactor_code
    reactor_types reactor_type
    cooling_types cooling_type
    reactor_operational_status operational_status
    decimal thermal_power_mw
    decimal electrical_power_mw
    int fuel_cycle_days
    int current_cycle_day
    decimal wear_index
    int design_lifetime_yr
    date commissioning_date
    date first_criticality
    timestamp last_inspection_at
    timestamp next_planned_outage
    text description
    timestamp created_at
  }

  control_rods {
    uuid id PK
    uuid reactor_id FK
    varchar rod_group
    int rod_number
    varchar material
    decimal position_mm
    decimal position_percent
    boolean is_inserted
    varchar status
    timestamp last_inspection
    timestamp created_at
    UNIQUE reactor_id, rod_group, rod_number
  }

  reactor_sensors {
    uuid id PK
    uuid reactor_id FK
    varchar sensor_code
    sensor_types sensor_type
    varchar description
    varchar location_zone
    varchar unit_of_measure
    varchar measurement_field
    decimal normal_min
    decimal normal_max
    decimal alarm_low
    decimal alarm_high
    decimal alert_low
    decimal alert_high
    decimal scram_low
    decimal scram_high
    sensor_quality status
    boolean is_active
    timestamp last_calibration
    timestamp calibration_due
    decimal current_value
    timestamp last_reading_at
    timestamp created_at
    UNIQUE reactor_id, sensor_code
  }

  sensor_readings {
    uuid id PK
    uuid sensor_id FK
    timestamp timestamp
    decimal value
    sensor_quality quality
    decimal raw_value
  }

  sensor_templates {
    uuid id PK
    reactor_types reactor_type
    varchar sensor_code
    sensor_types sensor_type
    varchar description
    varchar location_zone
    varchar unit_of_measure
    varchar measurement_field
    decimal normal_min
    decimal normal_max
    decimal alarm_low
    decimal alarm_high
    decimal alert_low
    decimal alert_high
    decimal scram_low
    decimal scram_high
    UNIQUE reactor_type, sensor_code
  }

  measurements {
    uuid id PK
    uuid reactor_id FK
    timestamp timestamp
    decimal power_percent
    decimal neutron_flux
    decimal reactivity_pcm
    decimal reactor_period_sec
    decimal temp_fuel_center
    decimal temp_coolant_in
    decimal temp_coolant_out
    decimal temp_moderator
    decimal pressure
    decimal flow_rate_primary
    decimal flow_rate_secondary
    decimal steam_pressure
    decimal steam_flow_rate
    decimal feedwater_temp
    decimal radiation
    decimal activity_primary
    decimal dose_rate_control_room
    decimal dose_rate_reactor_bldg
    decimal airborne_activity
    decimal fuel_burnup_mwd_t
    decimal efficiency
    decimal wear_delta
    decimal level_reactor_vessel
    decimal vibration
  }

  measurements_hourly {
    uuid reactor_id PK, FK
    timestamp hour PK
    int samples_count
    decimal power_percent_avg
    decimal power_percent_min
    decimal power_percent_max
    decimal neutron_flux_avg
    decimal temp_fuel_center_avg
    decimal temp_coolant_in_avg
    decimal temp_coolant_out_avg
    decimal temp_moderator_avg
    decimal pressure_avg
    decimal flow_rate_primary_avg
    decimal radiation_avg
    decimal efficiency_avg
    decimal wear_delta_sum
  }

  reactor_alerts {
    uuid id PK
    uuid reactor_id FK
    uuid plant_id FK
    varchar type
    varchar severity
    varchar sensor_type
    decimal value
    decimal threshold
    text message
    smallint is_read
    timestamp created_at
  }

  alerts {
    uuid id PK
    uuid plant_id FK
    varchar alert_type
    text message
    smallint is_read
    timestamp created_at
  }

  logs {
    uuid id PK
    varchar level
    text message
    jsonb context
    uuid user_id
    uuid plant_id
    uuid reactor_id
    varchar source
    varchar request_uri
    varchar ip_address
    timestamp created_at
  }
```

---

## 2. Tipuri Enum PostgreSQL

### 2.1 `user_roles`
```sql
CREATE TYPE user_roles AS ENUM ('ADMIN', 'ENGINEER', 'OPERATOR');
```
Folosit în: `users.role`

### 2.2 `power_plant_status`
```sql
CREATE TYPE power_plant_status AS ENUM ('DRAFT', 'REVIEW', 'APPROVED', 'REJECTED');
```
Folosit în: `power_plants.status`, `feasibility_reports.status`

Ciclul de viață: `DRAFT → REVIEW → APPROVED` (final) sau `REVIEW → REJECTED → DRAFT` (reset)

### 2.3 `soil_types` — 12 valori
```sql
CREATE TYPE soil_types AS ENUM (
  'BEDROCK', 'STIFF_CLAY', 'DENSE_SAND', 'GRAVEL', 'SHALE',
  'LIMESTONE', 'SANDSTONE', 'SOFT_CLAY', 'LOOSE_SAND', 'SILT',
  'LOAM', 'PEAT'
);
```
Folosit în: `geological_data.soil_type`, `CoordinatesPreviewResponseDTO`, `GeoLocationPreviewDTO`

### 2.4 `water_source_types`
```sql
CREATE TYPE water_source_types AS ENUM ('FRESH_WATER', 'SALT_WATER', 'BRACKISH_WATER');
```
Folosit în: `geological_data.water_source_type`

### 2.5 `reactor_operational_status` — 10 valori
```sql
CREATE TYPE reactor_operational_status AS ENUM (
  'SHUTDOWN', 'COLD_STANDBY', 'HOT_STANDBY', 'STARTUP',
  'POWER_ASCENT', 'FULL_POWER', 'PARTIAL_POWER',
  'PLANNED_OUTAGE', 'UNPLANNED_OUTAGE', 'EMERGENCY_SHUTDOWN'
);
```
Folosit în: `reactor.operational_status`

### 2.6 `reactor_types`
```sql
CREATE TYPE reactor_types AS ENUM ('PWR', 'BWR', 'PHWR', 'FBR');
```
Folosit în: `reactor.reactor_type`, `reactor_schema.reactor_type`, `sensor_templates.reactor_type`

### 2.7 `cooling_types` — 7 valori
```sql
CREATE TYPE cooling_types AS ENUM (
  'ONCE_THROUGH_FRESH', 'ONCE_THROUGH_SALT', 'NATURAL_DRAFT_WET',
  'MECHANICAL_DRAFT_WET', 'DRY_COOLING', 'HYBRID', 'COOLING_POND'
);
```
Folosit în: `reactor.cooling_type`, `reactor_schema.cooling_type`

### 2.8 `sensor_types` — 12 valori
```sql
CREATE TYPE sensor_types AS ENUM (
  'THERMOCOUPLE', 'PRESSURE_TRANSDUCER', 'NEUTRON_DETECTOR',
  'FLOW_METER', 'RADIATION_MONITOR', 'VIBRATION_SENSOR',
  'LEVEL_SENSOR', 'ACTIVITY_MONITOR', 'SEISMIC_SENSOR',
  'HYDROGEN_DETECTOR', 'VALVE_POSITION', 'PUMP_SPEED'
);
```
Folosit în: `reactor_sensors.sensor_type`, `sensor_templates.sensor_type`

### 2.9 `sensor_quality`
```sql
CREATE TYPE sensor_quality AS ENUM ('GOOD', 'SUSPECT', 'BAD', 'MAINTENANCE', 'SIMULATED');
```
Folosit în: `reactor_sensors.status`, `sensor_readings.quality`

---

## 3. Tabela `users`

Conturi de utilizatori cu autentificare și roluri.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` | Identificator unic |
| 2 | `username` | `VARCHAR(30)` | NOT NULL | Nume utilizator unic |
| 3 | `first_name` | `VARCHAR(50)` | NOT NULL | Prenume |
| 4 | `last_name` | `VARCHAR(50)` | NOT NULL | Nume de familie |
| 5 | `email` | `VARCHAR(100)` | NOT NULL, UNIQUE | Email |
| 6 | `password_hash` | `VARCHAR(255)` | NOT NULL | Hash bcrypt al parolei |
| 7 | `role` | `user_roles` | NOT NULL | Rol: ADMIN, ENGINEER, OPERATOR |
| 8 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` | Data înregistrării |

**Constrângeri unice**: `(username)`, `(email)`

**Înregistrare inițială**: Admin default (`admin@nuclear.ro`, hash bcrypt pre-generat)

---

## 4. Tabela `power_plants`

Înregistrarea principală a unei centrale nucleare.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` | Identificator unic |
| 2 | `name` | `VARCHAR(255)` | NOT NULL | Numele centralei |
| 3 | `status` | `power_plant_status` | NOT NULL | Status curent (DRAFT/REVIEW/APPROVED/REJECTED) |
| 4 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` | Data creării |
| 5 | `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` | Data ultimei modificări |
| 6 | `created_by` | `UUID` | FK → `users(id) ON DELETE SET NULL` | Utilizatorul creator |

---

## 5. Tabela `basic_data`

Date de bază ale unei centrale (relație 1:1 cu `power_plants`).

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `power_plant_id` | `UUID` | NOT NULL, UNIQUE, FK → `power_plants(id) ON DELETE CASCADE` |
| 3 | `capacity_mw` | `DECIMAL` | — | Capacitate instalată totală (MW) |
| 4 | `construction_duration_years` | `INT` | — | Durata estimată de construcție (ani) |
| 5 | `description` | `TEXT` | — | Descriere generală |
| 6 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| 7 | `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

---

## 6. Tabela `geological_data`

Date geologice și de amplasament (relație 1:1 cu `power_plants`).

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `power_plant_id` | `UUID` | NOT NULL, UNIQUE, FK → `power_plants(id) ON DELETE CASCADE` |
| 3 | `country` | `VARCHAR(100)` | — | Țara de amplasament |
| 4 | `latitude` | `DECIMAL(9,6)` | — | Latitudine (grade zecimale) |
| 5 | `longitude` | `DECIMAL(9,6)` | — | Longitudine (grade zecimale) |
| 6 | `soil_type` | `soil_types` | — | Tip sol (12 tipuri posibile) |
| 7 | `water_source_type` | `water_source_types` | — | Tip sursă de apă |
| 8 | `seismic_stability` | `DECIMAL` | — | Stabilitate seismică (scor 0-10) |
| 9 | `flood_risk` | `DECIMAL` | — | Risc de inundații (scor 0-10) |
| 10 | `groundwater_level` | `DECIMAL` | — | Nivelul pânzei freatice (m) |
| 11 | `water_proximity` | `DECIMAL` | — | Proximitatea față de sursa de apă (km) |
| 12 | `water_flow_rate` | `DECIMAL` | — | Debitul sursei de apă (m³/s) |
| 13 | `population_density` | `DECIMAL` | — | Densitatea populației (locuitori/km²) |
| 14 | `transport_infrastructure_score` | `DECIMAL` | — | Scor infrastructură transport (0-10) |
| 15 | `geological_risk_score` | `DECIMAL` | — | Scor de risc geologic general |
| 16 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| 17 | `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

---

## 7. Tabela `technical_data`

Date tehnice ale unei centrale (relație 1:1 cu `power_plants`).

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `power_plant_id` | `UUID` | NOT NULL, UNIQUE, FK → `power_plants(id) ON DELETE CASCADE` |
| 3 | `number_of_reactors` | `INT` | — | Număr total de reactoare |
| 4 | `estimated_efficiency` | `DECIMAL` | — | Eficiență termică estimată (%) |
| 5 | `operational_risk_level` | `DECIMAL` | — | Nivel de risc operațional |
| 6 | `safety_systems` | `JSONB` | — | Listă sisteme de siguranță (array JSON) |
| 7 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |
| 8 | `updated_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

---

## 8. Tabela `feasibility_reports`

Rapoarte de fezabilitate generate pentru fiecare centrală.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `power_plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` | Centrala asociată |
| 3 | `deficiencies` | `JSONB` | — | Lista deficiențelor (scoruri sub prag) |
| 4 | `errors` | `JSONB` | — | Lista erorilor critice (respingere automată) |
| 5 | `status` | `power_plant_status` | NOT NULL | Status recomandat (APPROVED/REVIEW/REJECTED) |
| 6 | `nsvi_score` | `DECIMAL(5,2)` | — | Nuclear Site Viability Index (0-100) |
| 7 | `message` | `TEXT` | — | Mesaj sumar al raportului |
| 8 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

---

## 9. Tabela `reactor`

Unități reactor individuale.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` | Identificator unic |
| 2 | `power_plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` | Centrala aparținătoare |
| 3 | `reactor_code` | `VARCHAR(100)` | NOT NULL | Cod unic al reactorului (per centrală) |
| 4 | `reactor_type` | `reactor_types` | NOT NULL | Tip reactor (PWR/BWR/PHWR/FBR) |
| 5 | `cooling_type` | `cooling_types` | NOT NULL | Tip răcire |
| 6 | `operational_status` | `reactor_operational_status` | NOT NULL, DEFAULT 'SHUTDOWN' | Status operațional |
| 7 | `thermal_power_mw` | `DECIMAL(10,2)` | — | Putere termică (MWt) |
| 8 | `electrical_power_mw` | `DECIMAL(10,2)` | — | Putere electrică (MWe) |
| 9 | `fuel_cycle_days` | `INT` | DEFAULT 365 | Durata ciclului de combustibil (zile) |
| 10 | `current_cycle_day` | `INT` | DEFAULT 0 | Ziua curentă din ciclul de combustibil |
| 11 | `wear_index` | `DECIMAL(5,4)` | DEFAULT 0.0000, CHECK (0-1) | Indice de uzură (0 = nou, 1 = sfârșit viață) |
| 12 | `design_lifetime_yr` | `INT` | DEFAULT 40 | Durata de viață proiectată (ani) |
| 13 | `commissioning_date` | `DATE` | — | Data punerii în funcțiune |
| 14 | `first_criticality` | `DATE` | — | Data primei reacții nucleare în lanț |
| 15 | `last_inspection_at` | `TIMESTAMP` | — | Data ultimei inspecții |
| 16 | `next_planned_outage` | `TIMESTAMP` | — | Data următoarei opriri planificate |
| 17 | `description` | `TEXT` | — | Descriere / note |
| 18 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` | Data creării |

**Constrângeri unice**: `(power_plant_id, reactor_code)`

---

## 10. Tabela `reactor_schema`

Catalog al combinațiilor permise de tip reactor × tip răcire.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `reactor_type` | `reactor_types` | NOT NULL | Tip reactor |
| 3 | `cooling_type` | `cooling_types` | — | Tip răcire |

Conține 28 de înregistrări (4 tipuri reactor × 7 tipuri răcire), populate prin scriptul `reactors_seek.sql`.

---

## 11. Tabela `reactor_plant_data`

Legătură M:N între `technical_data` și `reactor_schema`. O centrală poate avea multiple configurații reactor.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `technical_data_id` | `UUID` | PK, FK → `technical_data(id) ON DELETE CASCADE` |
| 2 | `reactor_schema_id` | `UUID` | PK, FK → `reactor_schema(id) ON DELETE RESTRICT` |
| 3 | `number_of_reactors` | `INT` | NOT NULL | Numărul de reactoare de acest tip |

**Cheie primară compusă**: `(technical_data_id, reactor_schema_id)`
**ON DELETE RESTRICT** pentru reactor_schema_id — previne ștergerea unui tip de schema dacă este folosit.

---

## 12. Tabela `control_rods`

Bare de control pentru fiecare reactor.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| 3 | `rod_group` | `VARCHAR(10)` | NOT NULL | Grupa barei (ex: "A", "B", "SHIM") |
| 4 | `rod_number` | `INT` | NOT NULL | Numărul barei în grupă |
| 5 | `material` | `VARCHAR(50)` | DEFAULT 'Ag-In-Cd' | Material (Ag-In-Cd, B4C, etc.) |
| 6 | `position_mm` | `DECIMAL(8,2)` | — | Poziție fizică (mm) |
| 7 | `position_percent` | `DECIMAL(5,2)` | — | Poziție procentuală (0-100%) |
| 8 | `is_inserted` | `BOOLEAN` | NOT NULL, DEFAULT TRUE | Dacă bara este introdusă în miez |
| 9 | `status` | `VARCHAR(30)` | DEFAULT 'OPERATIONAL' | Status (OPERATIONAL, STUCK, WITHDRAWN) |
| 10 | `last_inspection` | `TIMESTAMP` | — | Data ultimei inspecții |
| 11 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

**Constrângeri unice**: `(reactor_id, rod_group, rod_number)`

---

## 13. Tabela `reactor_sensors`

Definiția fiecărui senzor instalat pe un reactor.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| 3 | `sensor_code` | `VARCHAR(30)` | NOT NULL | Cod unic senzor (per reactor) |
| 4 | `sensor_type` | `sensor_types` | NOT NULL | Tip senzor (12 tipuri) |
| 5 | `description` | `VARCHAR(255)` | — | Descriere |
| 6 | `location_zone` | `VARCHAR(100)` | — | Zonă de amplasare (ex: "Miez", "Circuit primar") |
| 7 | `unit_of_measure` | `VARCHAR(20)` | — | Unitate de măsură (°C, bar, m³/s, μSv/h) |
| 8 | `measurement_field` | `VARCHAR(40)` | — | Corespondență cu coloana din `measurements` |
| 9 | `normal_min` | `DECIMAL(20,4)` | — | Minimul intervalului normal |
| 10 | `normal_max` | `DECIMAL(20,4)` | — | Maximul intervalului normal |
| 11 | `alarm_low` | `DECIMAL(20,4)` | — | Prag alarmă inferior (ALERT level) |
| 12 | `alarm_high` | `DECIMAL(20,4)` | — | Prag alarmă superior (ALERT level) |
| 13 | `alert_low` | `DECIMAL(20,4)` | — | Prag alertă inferior (WARNING level) |
| 14 | `alert_high` | `DECIMAL(20,4)` | — | Prag alertă superior (WARNING level) |
| 15 | `scram_low` | `DECIMAL(20,4)` | — | Prag SCRAM inferior (EMERGENCY level) |
| 16 | `scram_high` | `DECIMAL(20,4)` | — | Prag SCRAM superior (EMERGENCY level) |
| 17 | `status` | `sensor_quality` | NOT NULL, DEFAULT 'GOOD' | Starea senzorului |
| 18 | `is_active` | `BOOLEAN` | NOT NULL, DEFAULT TRUE | Senzor activ/dezactivat |
| 19 | `last_calibration` | `TIMESTAMP` | — | Data ultimei calibrări |
| 20 | `calibration_due` | `TIMESTAMP` | — | Data scadentă pentru următoarea calibrare |
| 21 | `current_value` | `DECIMAL(20,4)` | — | Ultima valoare citită |
| 22 | `last_reading_at` | `TIMESTAMP` | — | Data ultimei citiri |
| 23 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

**Constrângeri unice**: `(reactor_id, sensor_code)`

---

## 14. Tabela `sensor_readings`

Citiri individuale ale senzorilor (istorice).

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `sensor_id` | `UUID` | NOT NULL, FK → `reactor_sensors(id) ON DELETE CASCADE` |
| 3 | `timestamp` | `TIMESTAMP` | NOT NULL, DEFAULT `CURRENT_TIMESTAMP` | Momentul citirii |
| 4 | `value` | `DECIMAL(20,4)` | NOT NULL | Valoarea măsurată |
| 5 | `quality` | `sensor_quality` | NOT NULL, DEFAULT 'GOOD' | Calitatea citirii |
| 6 | `raw_value` | `DECIMAL(20,4)` | — | Valoarea brută (nesmooțată) |

---

## 15. Tabela `sensor_templates`

Template-uri predefinite pentru generarea automată a senzorilor per tip reactor.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `reactor_type` | `reactor_types` | NOT NULL | Tipul de reactor pentru care se aplică |
| 3 | `sensor_code` | `VARCHAR(30)` | NOT NULL | Codul senzorului în template |
| 4 | `sensor_type` | `sensor_types` | NOT NULL | Tipul senzorului |
| 5 | `description` | `VARCHAR(255)` | NOT NULL | Descriere |
| 6 | `location_zone` | `VARCHAR(100)` | — | Zona de amplasare |
| 7 | `unit_of_measure` | `VARCHAR(20)` | — | Unitatea de măsură |
| 8 | `measurement_field` | `VARCHAR(40)` | — | Corespondența cu coloana din measurements |
| 9 | `normal_min` | `DECIMAL(20,4)` | — | Minim interval normal |
| 10 | `normal_max` | `DECIMAL(20,4)` | — | Maxim interval normal |
| 11 | `alarm_low` | `DECIMAL(20,4)` | — | Prag alarmă inferior |
| 12 | `alarm_high` | `DECIMAL(20,4)` | — | Prag alarmă superior |
| 13 | `alert_low` | `DECIMAL(20,4)` | — | Prag alertă inferior |
| 14 | `alert_high` | `DECIMAL(20,4)` | — | Prag alertă superior |
| 15 | `scram_low` | `DECIMAL(20,4)` | — | Prag SCRAM inferior |
| 16 | `scram_high` | `DECIMAL(20,4)` | — | Prag SCRAM superior |

**Constrângeri unice**: `(reactor_type, sensor_code)`

Populat cu 48 de înregistrări (12 senzori × 4 tipuri reactor) prin `sensors_seek.sql`.

---

## 16. Tabela `measurements`

Măsurători compuse per reactor, generate la fiecare tick de simulare.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| 3 | `timestamp` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` | Momentul măsurătorii |
| 4 | `power_percent` | `DECIMAL(6,3)` | — | Putere reactor (% din maxim) |
| 5 | `neutron_flux` | `DECIMAL(20,4)` | — | Flux de neutroni (n/cm²·s) |
| 6 | `reactivity_pcm` | `DECIMAL(10,4)` | — | Reactivitate (pcm = percent mille) |
| 7 | `reactor_period_sec` | `DECIMAL(10,2)` | — | Perioada reactorului (secunde) |
| 8 | `temp_fuel_center` | `DECIMAL(8,2)` | — | Temperatura centrului combustibilului (°C) |
| 9 | `temp_coolant_in` | `DECIMAL(8,2)` | — | Temperatura lichidului de răcire la intrare (°C) |
| 10 | `temp_coolant_out` | `DECIMAL(8,2)` | — | Temperatura lichidului de răcire la ieșire (°C) |
| 11 | `temp_moderator` | `DECIMAL(8,2)` | — | Temperatura moderatorului (°C) |
| 12 | `pressure` | `DECIMAL(8,3)` | — | Presiunea în vasul reactorului (bar) |
| 13 | `flow_rate_primary` | `DECIMAL(12,2)` | — | Debitul circuitului primar (kg/s) |
| 14 | `flow_rate_secondary` | `DECIMAL(12,2)` | — | Debitul circuitului secundar (kg/s) |
| 15 | `steam_pressure` | `DECIMAL(8,3)` | — | Presiunea aburului (bar) |
| 16 | `steam_flow_rate` | `DECIMAL(12,2)` | — | Debitul de abur (kg/s) |
| 17 | `feedwater_temp` | `DECIMAL(8,2)` | — | Temperatura apei de alimentare (°C) |
| 18 | `radiation` | `DECIMAL(15,4)` | — | Radiația ambientală (μSv/h) |
| 19 | `activity_primary` | `DECIMAL(15,4)` | — | Activitatea circuitului primar (Bq/m³) |
| 20 | `dose_rate_control_room` | `DECIMAL(10,4)` | — | Debitul de doză în sala de comandă (μSv/h) |
| 21 | `dose_rate_reactor_bldg` | `DECIMAL(10,4)` | — | Debitul de doză în clădirea reactorului (μSv/h) |
| 22 | `airborne_activity` | `DECIMAL(15,4)` | — | Activitatea aeriană (Bq/m³) |
| 23 | `fuel_burnup_mwd_t` | `DECIMAL(10,2)` | — | Arderea combustibilului (MWd/t) |
| 24 | `efficiency` | `DECIMAL(6,4)` | — | Eficiența termică (PutereElectrică/PutereTermică) |
| 25 | `wear_delta` | `DECIMAL(8,6)` | — | Delta uzurii în acest tick |
| 26 | `level_reactor_vessel` | `DECIMAL(8,2)` | — | Nivelul în vasul reactorului (m) |
| 27 | `vibration` | `DECIMAL(8,2)` | — | Nivel de vibrații (mm/s) |

**Index**: `(reactor_id, timestamp DESC)` — căutare rapidă a ultimelor măsurători per reactor

---

## 17. Tabela `measurements_hourly`

Măsurători agregate pe oră. Populată de daemonul Aggregator la fiecare 60s.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `reactor_id` | `UUID` | PK, FK → `reactor(id) ON DELETE CASCADE` | Reactorul |
| 2 | `hour` | `TIMESTAMP` | PK | Ora de agregare (trunchiată) |
| 3 | `samples_count` | `INT` | NOT NULL | Numărul de măsurători agregate |
| 4 | `power_percent_avg` | `DECIMAL(6,3)` | — | Medie putere |
| 5 | `power_percent_min` | `DECIMAL(6,3)` | — | Minim putere |
| 6 | `power_percent_max` | `DECIMAL(6,3)` | — | Maxim putere |
| 7 | `neutron_flux_avg` | `DECIMAL(20,4)` | — | Medie flux neutroni |
| 8 | `temp_fuel_center_avg` | `DECIMAL(8,2)` | — | Medie temperatură combustibil |
| 9 | `temp_coolant_in_avg` | `DECIMAL(8,2)` | — | Medie temperatură răcire intrare |
| 10 | `temp_coolant_out_avg` | `DECIMAL(8,2)` | — | Medie temperatură răcire ieșire |
| 11 | `temp_moderator_avg` | `DECIMAL(8,2)` | — | Medie temperatură moderator |
| 12 | `pressure_avg` | `DECIMAL(8,3)` | — | Medie presiune |
| 13 | `flow_rate_primary_avg` | `DECIMAL(12,2)` | — | Medie debit primar |
| 14 | `radiation_avg` | `DECIMAL(15,4)` | — | Medie radiație |
| 15 | `efficiency_avg` | `DECIMAL(6,4)` | — | Medie eficiență |
| 16 | `wear_delta_sum` | `DECIMAL(12,6)` | — | Suma deltelor de uzură |

**Cheie primară compusă**: `(reactor_id, hour)`
**Upsert**: `ON CONFLICT (reactor_id, hour) DO UPDATE`

---

## 18. Tabela `reactor_alerts`

Alerte generate de simulare la nivel de reactor.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `reactor_id` | `UUID` | NOT NULL, FK → `reactor(id) ON DELETE CASCADE` |
| 3 | `plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` |
| 4 | `type` | `VARCHAR(20)` | NOT NULL | Tip: ALERT, ALARM, SCRAM |
| 5 | `severity` | `VARCHAR(20)` | NOT NULL | Severitate: WARNING, HIGH, EMERGENCY |
| 6 | `sensor_type` | `VARCHAR(50)` | — | Tipul senzorului care a declanșat |
| 7 | `value` | `DECIMAL(12,4)` | — | Valoarea senzorului la momentul declanșării |
| 8 | `threshold` | `DECIMAL(12,4)` | — | Pragul atins (alarm_low, scram_high etc.) |
| 9 | `message` | `TEXT` | NOT NULL | Mesaj descriptiv |
| 10 | `is_read` | `SMALLINT` | DEFAULT 0 | 0 = necitit, 1 = citit |
| 11 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

---

## 19. Tabela `alerts`

Alerte generale și evenimente la nivel de centrală.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `plant_id` | `UUID` | NOT NULL, FK → `power_plants(id) ON DELETE CASCADE` |
| 3 | `alert_type` | `VARCHAR(20)` | NOT NULL | Tip: WARNING, CRITICAL, PLANT_STATUS_CHANGE, PLANT_APPROVED, PLANT_REJECTED, DISMISSED_APPROVAL |
| 4 | `message` | `TEXT` | NOT NULL | Mesajul alertei |
| 5 | `is_read` | `SMALLINT` | DEFAULT 0 | 0 = necitit, 1 = citit |
| 6 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

---

## 20. Tabela `logs`

Jurnalul de activitate al aplicației.

| # | Coloană | Tip | Constrângeri | Descriere |
|---|---|---|---|---|
| 1 | `id` | `UUID` | PK, DEFAULT `gen_random_uuid()` |
| 2 | `level` | `VARCHAR(20)` | NOT NULL | DEBUG, INFO, WARNING, ERROR, CRITICAL |
| 3 | `message` | `TEXT` | NOT NULL | Mesajul log-ului |
| 4 | `context` | `JSONB` | — | Date contextuale (request body parțial, erori, etc.) |
| 5 | `user_id` | `UUID` | — | Utilizatorul care a generat acțiunea |
| 6 | `plant_id` | `UUID` | — | Centrala implicată (dacă e cazul) |
| 7 | `reactor_id` | `UUID` | — | Reactorul implicat (dacă e cazul) |
| 8 | `source` | `VARCHAR(20)` | DEFAULT 'backend' | Sursa: backend / frontend / simulator |
| 9 | `request_uri` | `VARCHAR(255)` | — | URI-ul requestului HTTP |
| 10 | `ip_address` | `VARCHAR(45)` | — | Adresa IP a clientului |
| 11 | `created_at` | `TIMESTAMP` | DEFAULT `CURRENT_TIMESTAMP` |

**Auto-cleanup**: Logurile mai vechi de 30 de zile sunt șterse automat la fiecare oră de `LogService`.

---

## 21. Indexuri

```sql
-- Măsurători: căutare rapidă per reactor + sortare cronologică (folosit de SSE și statistici)
CREATE INDEX idx_measurements_reactor_ts ON measurements (reactor_id, timestamp DESC);

-- Citiri senzori: căutare rapidă per senzor + interval
CREATE INDEX idx_sensor_readings_sensor_ts ON sensor_readings(sensor_id, timestamp DESC);

-- Măsurători: căutare pe interval general (folosit de Aggregator)
CREATE INDEX idx_measurements_ts ON measurements(timestamp);

-- Măsurători orare: ordonare invers cronologică (folosit de statistici)
CREATE INDEX idx_measurements_hourly_ts ON measurements_hourly (hour DESC);

-- Logs: căutare cronologică
CREATE INDEX idx_logs_created_at ON logs (created_at DESC);

-- Logs: filtrare după nivel (folosit de interfața admin)
CREATE INDEX idx_logs_level ON logs (level);

-- Logs: filtrare după utilizator
CREATE INDEX idx_logs_user_id ON logs (user_id);
```

---

## 22. Relații Cheie

```
users (1) ──→ (N) power_plants         "Un utilizator creează multiple centrale"
power_plants (1) ──→ (1) basic_data    "O centrală are un set de date de bază"
power_plants (1) ──→ (1) geological_data  "O centrală are date geologice"
power_plants (1) ──→ (1) technical_data   "O centrală are configurații tehnice"
power_plants (1) ──→ (N) feasibility_reports  "O centrală are multiple rapoarte"
power_plants (1) ──→ (N) reactor        "O centrală conține multiple reactoare"
power_plants (1) ──→ (N) alerts         "O centrală generează alerte"
power_plants (1) ──→ (N) reactor_alerts "O centrală are alerte de reactor"

technical_data (1) ──→ (N) reactor_plant_data  "Date tehnice → configurații"
reactor_schema (1) ──→ (N) reactor_plant_data  "Catalog schema → configurații"

reactor (1) ──→ (N) reactor_sensors    "Un reactor are 7-10+ senzori"
reactor (1) ──→ (N) measurements       "Un reactor generează măsurători la fiecare tick"
reactor (1) ──→ (N) measurements_hourly "Un reactor are măsurători orare"
reactor (1) ──→ (N) control_rods       "Un reactor are bare de control"
reactor (1) ──→ (N) reactor_alerts     "Un reactor poate genera alerte"

reactor_sensors (1) ──→ (N) sensor_readings  "Un senzor are multiple citiri în timp"

sensor_templates (N) ──→ (1) reactor_type  "Template-urile sunt categorisite pe tip reactor"
```

---

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
