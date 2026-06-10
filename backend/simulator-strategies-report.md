# Raport funcțional — Strategii de generare valori

## Arhitectura

```
SensorGeneratorStrategy (interfață)
    └── generate(float $currentValue, ReactorSensor $sensor): float

AbstractSensorGeneratorStrategy (clasă abstractă)
    └── protected gaussianRandom(mean, stdDev) — distribuție normală
            ├── ThermocoupleStrategy
            ├── PumpSpeedStrategy
            ├── NeutronDetectorStrategy
            ├── PressureTransducerStrategy
            ├── FlowMeterStrategy
            ├── RadiationMonitorStrategy
            ├── VibrationSensorStrategy
            ├── LevelSensorStrategy
            ├── ActivityMonitorStrategy
            ├── SeismicSensorStrategy
            ├── HydrogenDetectorStrategy
            ├── ValvePositionStrategy
```

Toate urmează același șablon:
1. Citesc `normalMin` / `normalMax` din DB (fără fallback)
2. Calculează `$range` și `$maxStep` ca procent din range
3. Aplică zgomot gaussian + evenimente rare + forță de readucere
4. Returnează clampat fizic (`max(0, ...)` sau `max(-273.15, ...)`)

---

### 1. ThermocoupleStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | **0.003** (foarte mic) |
| Eveniment rar | — |
| Forță readucere | 0.002 (slabă, derivează ușor) |
| Limită fizică | `max(-273.15, $newValue)` |
| Comportament | Temperatura are inerție mare. Zgomot mic, derive lentă spre centrul intervalului. Nu sare niciodată brusc. |

**Simulează:** termocuplu în combustibil / coolant — variație lentă, stabilă.

---

### 2. PumpSpeedStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | 0.004 |
| Eveniment rar | **Trip** 0.2% → pierde 20% din RPM |
| Forță readucere | **Runup** +15%/tick dacă < 30% nominal (pornire); **Pull** 0.006 dacă > 30% (reglaj) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Trei regimuri distincte: start rapid (runup), funcționare cu reglaj fin, trip rar cu cădere bruscă. |

**Simulează:** pompă principală cu sistem de control și defecte ocazionale.

---

### 3. NeutronDetectorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | **0.015** (mare) |
| Eveniment rar | **Spike** 2% × 1.08 |
| Zgomot | 60% noise + 40% drift (două componente gaussiene) |
| Forță readucere | 0.003 |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Variație rapidă, zgomot statistic compus (Poisson-like), spike-uri frecvente. Cel mai "zgomotos" senzor. |

**Simulează:** flux de neutroni în nucleu — fluctuații rapide naturale.

---

### 4. PressureTransducerStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | 0.004 |
| Eveniment rar | **Drop** 0.5% × 2% din range |
| Forță readucere | 0.005 |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Variație moderată cu drop-uri rare de presiune. Stabil, readucere lină. |

**Simulează:** presiune circuit primar / abur.

---

### 5. FlowMeterStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | 0.005 |
| Eveniment rar | **Pump trip** 0.2% → -15% din range |
| Forță readucere | 0.008 (mai puternică) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Similar cu PressureTransducer, dar cu readucere mai agresivă. Debitele revin repede la normal după un drop. |

**Simulează:** debitmetru circuit primar / secundar.

---

### 6. RadiationMonitorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | 0.008 |
| Eveniment rar | **Spike** 0.8% × 12% din range |
| Forță readucere | 0.015 (decay rapid) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Baseline la 5% din range, spike-uri rare cu decay rapid. Revine repede la fond după un eveniment. |

**Simulează:** monitor radiații — fond constant + spike la eveniment.

---

### 7. VibrationSensorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | **0.02** (cel mai mare) |
| Eveniment rar | **Mecanic** 1% × 25% din range |
| Forță readucere | **0.08** (foarte puternică — amortizare) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Background noise mare, evenimente mecanice frecvente (1%), amortizare rapidă. Revine la 10% din range după un spike. |

**Simulează:** vibrații pompă/turbină — zgomot continuu + șocuri mecanice.

---

### 8. LevelSensorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | **0.002** (cel mai mic) |
| Eveniment rar | **Leak** 0.3% × 0.5% din range |
| Forță readucere | **0.01** (cea mai puternică — control strict) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Mișcare extrem de lentă, dar sistemul de control reacționează puternic la abateri. Nivelul e menținut strict la setpoint. |

**Simulează:** nivel apă presurizor / aburitor — controlat automat, cu pierderi mici rare.

---

### 9. ActivityMonitorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | 0.005 |
| Eveniment rar | **Spike** 0.4% × 20% din range |
| Forță readucere | 0.008 (decay) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Baseline la 8% din range, spike-uri mai rare ca la radiație dar mai mari ca magnitudine. Decay lent. |

**Simulează:** activitate coolant — similar radiației, dar mai stabilă.

---

### 10. SeismicSensorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | **0.001** (aproape mort) |
| Eveniment rar | **Seismic event** **0.05%** × 40% din range (extrem de rar) |
| Forță readucere | **0.12** (cea mai puternică — aftershock decay) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Aproape 0 tot timpul. Un eveniment o dată la ~2000 tick-uri (~3 ore) sare brusc, apoi decade rapid. Folosește `mt_rand(1, 1000000)` pentru granularitatea fine a probabilității. |

**Simulează:** seism — tăcut 99.95% din timp, spike masiv la cutremur.

---

### 11. HydrogenDetectorStrategy

| Proprietate | Valoare |
|------------|---------|
| MAX_STEP_PERCENT | 0.003 |
| Eveniment rar | **Accumulation** 0.6% × 1.5% din range |
| Forță readucere | **Recombination** 0.005 (scădere spre baseline) |
| Limită fizică | `max(0, $newValue)` |
| Comportament | Baseline la 5% din range. Crește lent la acumulare, scade lent prin recombinare. Nu are spike-uri bruște — doar creep. |

**Simulează:** detector hidrogen în incintă — acumulare lentă în condiții anormale, recombinare naturală.

---

### 12. ValvePositionStrategy

| Proprietate | Valoare |
|------------|---------|
| STEP_SIZE | **1%** |
| MOVE_PROBABILITY | **5%** per tick |
| MAX_MOVE_STEPS | **3** pași (max 3% per tick) |
| Eveniment rar | — |
| Forță readucere | Setpoint dacă abaterea > 10% |
| Limită fizică | `max(0, $newValue)` |
| Comportament | **Singura strategie discretă.** 95% din timp valva stă pe loc. Când se mișcă, face pași de 1-3%. Dacă e departe de setpoint (>10%), sistemul de control o forțează în direcția corectă. Nu folosește `gaussianRandom()` — nu e zgomot analog. |

**Simulează:** poziție valvă — comandă discretă de la sistemul de control.

---

### 13. PumpSpeedStrategy vs FlowMeterStrategy

Ambele au trip de 0.2%, dar efecte diferite:
| | PumpSpeedStrategy | FlowMeterStrategy |
|---|---|---|
| Eveniment | Pierde **20% din RPM** | Pierde **15% din range** (debit) |
| Readucere | Runup rapid + pull lent | Pull mai puternic (0.008) |
| Logică | Trip pe `$currentValue` | Trip pe `$range` |

Corect — pompa pierde turație, debitmetrul vede scădere de debit.

---

## Clasificare pe tipuri de comportament

| Tip | Strategii |
|-----|-----------|
| **Inerție + derive** | Thermocouple |
| **Zgomot mare + spike-uri** | NeutronDetector, VibrationSensor |
| **Stabil + evenimente rare** | PressureTransducer, FlowMeter |
| **Fond + spike + decay** | RadiationMonitor, ActivityMonitor |
| **Control automat strict** | LevelSensor |
| **Aproape mort** | SeismicSensor |
| **Creep lent** | HydrogenDetector |
| **Discret** | ValvePosition |
| **Multi-regim** | PumpSpeed |
