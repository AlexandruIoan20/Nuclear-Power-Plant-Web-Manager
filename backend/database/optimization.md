# Optimizare Bază de Date — Measurements & Sensor Readings

## 1. Situația Actuală

### Tabele cu probleme

| Tabel | Rânduri/zi | Index util | Index prezent |
|---|---|---|---|
| `measurements` | ~57.000 | `(reactor_id, timestamp)` | ❌ niciunul |
| `sensor_readings` | depinde de nr. senzori | `(sensor_id, timestamp)` | ❌ niciunul |

### Consecințe

- Orice interogare pe `measurements` după `reactor_id` face **full table scan** (scanează tot tabelul)
- O lună de funcționare generează ~1.7 milioane de rânduri
- Fără ștergere periodică, tabelul crește nelimitat
- Query-urile de historice (`WHERE reactor_id = ? AND timestamp BETWEEN ? AND ?`) devin prohibitive

---

## 2. Indexare

### 2.1 Index primar — `measurements`

```sql
CREATE INDEX idx_measurements_reactor_ts
    ON measurements (reactor_id, timestamp DESC);
```

**Efect:** interogările după reactor + interval temporal folosesc indexul în loc de full scan.  
`DESC` ajută la `ORDER BY timestamp DESC LIMIT 1` (ultima citire).

### 2.2 Index pe `sensor_readings`

```sql
CREATE INDEX idx_sensor_readings_sensor_ts
    ON sensor_readings (sensor_id, timestamp DESC);
```

**Efect:** căutările după sensor + interval temporal folosesc indexul.

### 2.3 Index pe `measurements.timestamp` (dacă se face ștergere globală)

```sql
CREATE INDEX idx_measurements_ts ON measurements (timestamp);
```

Folositor doar dacă ștergem date mai vechi de o perioadă fără să filtrăm după reactor.

---

## 3. Arhitectură de Cleanup

### 3.1 Script de curățare lunară (recomandat)

```sql
DELETE FROM measurements
WHERE timestamp < NOW() - INTERVAL '3 months';
```

Rulează lunar via cron / task scheduler. Păstrează ultimele 3 luni de date brute.

### 3.2 Partitionare temporală (opțional, avansat)

```sql
CREATE TABLE measurements (
    id UUID,
    reactor_id UUID,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- ... celelalte coloane ...
) PARTITION BY RANGE (timestamp);

CREATE TABLE measurements_2026_q1 PARTITION OF measurements
    FOR VALUES FROM ('2026-01-01') TO ('2026-04-01');

CREATE TABLE measurements_2026_q2 PARTITION OF measurements
    FOR VALUES FROM ('2026-04-01') TO ('2026-07-01');
```

**Când e util:** când tabelul depășește 10M rânduri. Permite ștergere instantanee (`DROP TABLE` în loc de `DELETE`) și scanări doar pe partițiile relevante.

### 3.3 Funcție pg_cron (dacă extensia e disponibilă)

```sql
SELECT cron.schedule('cleanup-measurements', '0 3 1 * *',
    $$DELETE FROM measurements WHERE timestamp < NOW() - INTERVAL '3 months'$$
);
```

---

## 4. Agregare — Strat Istoric

### 4.1 Tabel de agregate orare

```sql
CREATE TABLE measurements_hourly (
    reactor_id UUID NOT NULL,
    hour TIMESTAMP NOT NULL,              -- începutul orei (trunchiat)
    samples_count INT NOT NULL,           -- câte citiri brute intră în această oră
    power_percent_avg DECIMAL(6,3),
    power_percent_min DECIMAL(6,3),
    power_percent_max DECIMAL(6,3),
    neutron_flux_avg DECIMAL(20,4),
    temp_coolant_out_avg DECIMAL(8,2),
    pressure_avg DECIMAL(8,3),
    efficiency_avg DECIMAL(6,4),
    wear_delta_sum DECIMAL(12,6),
    -- ... alte coloane după necesitate ...
    PRIMARY KEY (reactor_id, hour)
);
```

### 4.2 Job de agregare (rulează la fiecare oră)

```sql
INSERT INTO measurements_hourly (
    reactor_id, hour, samples_count,
    power_percent_avg, power_percent_min, power_percent_max,
    neutron_flux_avg, temp_coolant_out_avg, pressure_avg,
    efficiency_avg, wear_delta_sum
)
SELECT
    reactor_id,
    date_trunc('hour', timestamp) AS hour,
    COUNT(*) AS samples_count,
    AVG(power_percent),
    MIN(power_percent),
    MAX(power_percent),
    AVG(neutron_flux),
    AVG(temp_coolant_out),
    AVG(pressure),
    AVG(efficiency),
    SUM(wear_delta)
FROM measurements
WHERE timestamp >= date_trunc('hour', NOW()) - INTERVAL '1 hour'
  AND timestamp < date_trunc('hour', NOW())
GROUP BY reactor_id, date_trunc('hour', timestamp)
ON CONFLICT (reactor_id, hour) DO NOTHING;
```

**Efect:** dashboard-urile cu grafice pe ore / zile citesc din tabelul agregat (sute de rânduri) în loc de tabelul brut (milioane de rânduri).

### 4.3 Curățare date brute după agregare

După ce agregatele sunt calculate, datele brute mai vechi de N zile pot fi șterse:

```sql
DELETE FROM measurements
WHERE timestamp < date_trunc('hour', NOW()) - INTERVAL '7 days';
```

Păstrează ultimele 7 zile de date brute pentru diagnoză; tot ce e mai vechi e disponibil doar în format orar.

---

## 5. Plan de Implementare

| Pas | Acțiune | Timp | Impact |
|---|---|---|---|
| 1 | Adaugă `idx_measurements_reactor_ts` + `idx_sensor_readings_sensor_ts` | 5 min | Imediat — query-urile curente devin mult mai rapide |
| 2 | Creează `measurements_hourly` + script de populare | 30 min | Noua funcționalitate nu afectează nimic existent |
| 3 | Adaugă cron/script de agregare orară | 15 min | Datele istorice devin accesibile eficient |
| 4 | Adaugă script de ștergere date brute > 7 zile | 15 min | Tabelul `measurements` rămâne sub control |
| 5 | (Opțional) Partitionare pe trimestre | 1-2 ore | Necesar doar dacă tabelul depășește 10M rânduri |

---

## 6. Interogări Optimizate (după index)

```sql
-- Ultima măsurătoare pentru un reactor (dashboards)
SELECT * FROM measurements
WHERE reactor_id = :id
ORDER BY timestamp DESC
LIMIT 1;
-- → index pe (reactor_id, timestamp DESC) → 1 rând citit

-- Măsurători într-un interval (grafice)
SELECT timestamp, power_percent, temp_coolant_out
FROM measurements
WHERE reactor_id = :id
  AND timestamp BETWEEN :start AND :end
ORDER BY timestamp ASC;
-- → index pe (reactor_id, timestamp DESC) → căutare rapidă

-- Date agregate pe ore (rapoarte)
SELECT hour, power_percent_avg, efficiency_avg
FROM measurements_hourly
WHERE reactor_id = :id
  AND hour >= :start
ORDER BY hour ASC;
-- → PK pe (reactor_id, hour) → instant
```

---

## 7. Estimare Dimensiuni

| Perioadă | Rânduri brute | Rânduri agregate (orar) |
|---|---|---|
| 1 zi | ~57.000 | ~96 (4 reactoare × 24 ore) |
| 1 lună | ~1.710.000 | ~2.880 |
| 1 an | ~20.800.000 | ~35.040 |

Cu index pe `(reactor_id, timestamp)`, scanarea a 57.000 de rânduri (1 zi) devine o căutare indexată care citește doar câteva blocuri — timp de răspuns sub 5ms în loc de 50-200ms la full scan.
