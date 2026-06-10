# Plan de Implementare – Sistem Senzori Centrală Nucleară

## 1. Arhitectura generală (stadiu actual)

```
[DB] → [Repository] → [Service] → [Controller] → [Router/HTTP]
  ✅                    ✅            ❌             ❌
```

**Realizat:** tabele DB, entități, enums, repos, service, DTO-uri.
**Lipsă:** controller, rute, integrare cu reactor creation, citiri individuale, măsurători compozite.

---

## 2. Etape de implementare

### Etapa 1 – Controller și rute API

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 1.1 | `src/Controllers/SensorController.php` | Controler nou cu metode: `getReactorSensors`, `getSensor`, `createSensor`, `updateSensor`, `deleteSensor`, `getSensorReadings`, `createSensorReading` |
| 1.2 | `public/index.php` | Wireuirea `SensorService` + `SensorRepository` + `SensorTemplateRepository` în container |
| 1.3 | `public/index.php` | Adăugare rute REST: |
| | | `GET    /api/reactors/{id}/sensors` → listă senzori reactor |
| | | `GET    /api/sensors/{id}` → detalii senzor |
| | | `POST   /api/reactors/{id}/sensors` → adăugare senzor manual |
| | | `PUT    /api/sensors/{id}` → actualizare senzor |
| | | `DELETE /api/sensors/{id}` → ștergere senzor |
| | | `GET    /api/sensors/{id}/readings` → istoric citiri |
| | | `POST   /api/sensors/{id}/readings` → înregistrare citire |

### Etapa 2 – Auto-populare senzori la creare reactor

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 2.1 | `src/Services/ReactorService.php` | În `createReactor()`, după salvarea reactorului, apel `SensorService::populateSensorsForReactor($reactorId, $reactorType)` |
| 2.2 | `public/index.php` | Injectare `SensorService` în `ReactorService` |

### Etapa 3 – Repository și Service pentru SensorReading

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 3.1 | `src/Repositories/SensorReadingRepository.php` | CRUD pentru `sensor_readings`: `findBySensorId`, `findLastBySensorId`, `insert`, `findPaginated` |
| 3.2 | `src/Services/SensorReadingService.php` | Logică business: înregistrare citire cu validare calitate, actualizare `current_value` pe `reactor_sensors` |

### Etapa 4 – Repository și Service pentru Measurements

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 4.1 | `src/Repositories/MeasurementRepository.php` | CRUD pentru `measurements`: `findByReactorId`, `findLatestByReactor`, `insert` |
| 4.2 | `src/Services/MeasurementService.php` | Logică business: agregare citiri senzori într-un snapshot compozit la interval configurabil |

### Etapa 5 – Validare praguri și alertare automată

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 5.1 | `src/Services/SensorThresholdService.php` | Serviciu nou care compară `current_value` cu `normal_min/max`, `alarm_low/high`, `alert_low/high`, `scram_low/high` |
| 5.2 | `src/Services/SensorReadingService.php` | La fiecare citire nouă, apel `SensorThresholdService` și trigger alertă prin `AlertService::processSensorData()` dacă pragul e depășit |

### Etapa 6 – Seed date pentru BWR, PHWR, FBR

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 6.1 | `database/seek/sensors_seek_bwr.sql` | Template-uri senzori pentru reactor BWR (~10–12 senzori) |
| 6.2 | `database/seek/sensors_seek_phwr.sql` | Template-uri senzori pentru reactor PHWR (~10–12 senzori) |
| 6.3 | `database/seek/sensors_seek_fbr.sql` | Template-uri senzori pentru reactor FBR (~10–12 senzori) |
| 6.4 | Script migrare | Integrare în scriptul de initializare DB |

### Etapa 7 – Simulare date test

| Task | Fișiere | Descriere |
|------|---------|-----------|
| 7.1 | `src/Services/SensorSimulatorService.php` | Generator valori aleatoare în intervalul normal pentru fiecare senzor; rulează la interval configurabil (cron job extern) |
| 7.2 | Script CLI | Comandă `php simulate.php` care invocă simulatorul pentru un reactor dat |

---

## 3. Dependențe între etape

```
Etapa 1 (Controller + Rute)
    ↓
Etapa 2 (Auto-populare) ← depinde de Etapa 1 ptr testare
    ↓
Etapa 3 (SensorReading) ← depinde de Etapa 1
    ↓
Etapa 4 (Measurement)   ← depinde de Etapa 1, 3
    ↓
Etapa 5 (Thresholds)    ← depinde de Etapa 3
    ↓
Etapa 6 (Seed data)     ← independentă, poate rula paralel cu 1–5
    ↓
Etapa 7 (Simulare)      ← depinde de Etapa 3
```

---

## 4. Schema finală a endpoint-urilor

| Metodă | Cale | Descriere |
|--------|------|-----------|
| GET | `/api/reactors/{id}/sensors` | Lista senzori ai unui reactor |
| GET | `/api/sensors/{id}` | Detalii complete senzor |
| POST | `/api/reactors/{id}/sensors` | Adaugă senzor manual pe reactor |
| PUT | `/api/sensors/{id}` | Actualizează câmpuri senzor |
| DELETE | `/api/sensors/{id}` | Șterge senzor |
| GET | `/api/sensors/{id}/readings` | Istoric citiri (cu paginare) |
| POST | `/api/sensors/{id}/readings` | Înregistrează citire nouă |
| POST | `/api/alerts/receive` | Endpoint existent – alertă din senzor |

---

## 5. Legendă stadiu

| Simbol | Înseamnă |
|--------|----------|
| ❌ | Nerealizat |
| ✅ | Realizat |
| 🔧 | În lucru |
