# Manual de Utilizare și Configurare – Senzori

---

## 1. Arhitectura generală

Fiecare reactor are o listă de senzori definiți prin **template-uri** (`sensor_templates`) pe tip de reactor (`reactor_type`). La popularea bazei, template-urile sunt copiate în `reactor_sensors` pentru fiecare reactor în parte.

Câmpul `measurement_field` leagă senzorul de o coloană din tabela `measurements`. Simulatorul scrie valorile simulate în `measurements` folosind acest câmp, iar corelațiile fizice (`applyPhysicalCorrelation`) leagă diferiți senzori între ei tot pe baza acestor nume.

---

## 2. Tipuri de reactoare

| Cod | Denumire | Descriere |
|-----|----------|-----------|
| **PWR** | Pressurized Water Reactor | Reactor cu apă ușoară sub presiune (cea mai comună tehnologie la nivel global) |
| **BWR** | Boiling Water Reactor | Reactor cu apă în fierbere (aburul merge direct la turbină) |
| **PHWR** | Pressurized Heavy Water Reactor | Reactor cu apă grea sub presiune (tehnologie CANDU) |
| **FBR** | Fast Breeder Reactor | Reactor cu neutroni rapizi, răcit cu sodiu lichid |

---

## 3. Tipuri de senzori

| Cod enum | Tip | Descriere |
|----------|-----|-----------|
| `THERMOCOUPLE` | Termocuplu | Măsoară temperatura |
| `PRESSURE_TRANSDUCER` | Traductor de presiune | Măsoară presiunea într-un circuit |
| `NEUTRON_DETECTOR` | Detector de neutroni | Măsoară fluxul neutronic / puterea reactorului |
| `FLOW_METER` | Debitmetru | Măsoară debitul unui fluid |
| `RADIATION_MONITOR` | Monitor de radiații | Măsoară doza de radiații ambientale |
| `VIBRATION_SENSOR` | Senzor de vibrații | Măsoară vibrațiile utilajelor |
| `LEVEL_SENSOR` | Senzor de nivel | Măsoară nivelul unui fluid într-un vas |
| `ACTIVITY_MONITOR` | Monitor de activitate | Măsoară activitatea specifică a unui fluid |
| `SEISMIC_SENSOR` | Senzor seismic | Detectează mișcări seismice |
| `HYDROGEN_DETECTOR` | Detector de hidrogen | Măsoară concentrația de H₂ |
| `VALVE_POSITION` | Poziție valvă | Măsoară deschiderea unei valve |
| `PUMP_SPEED` | Viteză pompă | Măsoară turația unei pompe |

---

## 4. Codurile senzorilor (sensor_code)

Senzorii urmează convenția `<prefix>-<număr>`:

| Prefix | Tip senzor |
|--------|-----------|
| **TI** | Temperatură (Thermocouple / Temperature Indicator) |
| **PR** | Presiune (Pressure) |
| **FM** | Debit (Flow Meter) |
| **NI** | Neutroni / Flux neutronic (Neutron Indicator) |
| **RM** | Radiații (Radiation Monitor) |
| **LI** | Nivel (Level Indicator) |
| **VI** | Vibrații (Vibration) |

---

## 5. Câmpurile de măsură (measurement_field)

Fiecare `measurement_field` corespunde unei coloane din tabela `measurements`. Acesta este modul prin care simulatorul știe în ce coloană să scrie valoarea generată de un senzor.

| measurement_field | Unitate | Descriere |
|------------------|---------|-----------|
| `temp_coolant_out` | °C | Temperatura agentului de răcire la ieșirea din reactor |
| `temp_coolant_in` | °C | Temperatura agentului de răcire la intrarea în reactor |
| `temp_fuel_center` | °C | Temperatura în centrul combustibilului nuclear |
| `temp_moderator` | °C | Temperatura moderatorului (PHWR) |
| `pressure` | MPa | Presiunea în circuitul primar |
| `steam_pressure` | MPa | Presiunea aburului în circuitul secundar |
| `flow_rate_primary` | m³/h | Debitul în circuitul primar |
| `flow_rate_secondary` | m³/h | Debitul în circuitul secundar (FBR) |
| `steam_flow_rate` | t/h | Debitul de abur către turbină (BWR) |
| `power_percent` | %Pn | Puterea reactorului (% din puterea nominală) |
| `neutron_flux` | n/cm²/s | Fluxul neutronic |
| `level_reactor_vessel` | % | Nivelul apei/sodiului în vasul reactorului |
| `activity_primary` | Bq/L | Activitatea specifică a agentului primar |
| `dose_rate_control_room` | mSv/h | Doza în sala de comandă (PWR) |
| `dose_rate_reactor_bldg` | mSv/h | Doza în clădirea reactorului (BWR, PHWR, FBR) |
| `vibration` | mm/s | Vibrații ale utilajelor (pompe, motoare) |

---

## 6. Configurarea senzorilor pe fiecare tip de reactor

### 6.1 PWR — Pressurized Water Reactor

PWR are 12 senzori, cu un circuit primar sub presiune și un generator de abur în secundar.

| Cod | Tip | measurement_field | Locație | Rol |
|-----|-----|------------------|---------|-----|
| **TI-001** | THERMOCOUPLE | `temp_coolant_out` | Bucla 1 / Ieșire reactor | Temperatura apei la ieșirea din vasul reactorului (intră în generatorul de abur) |
| **TI-002** | THERMOCOUPLE | `temp_coolant_in` | Bucla 1 / Intrare reactor | Temperatura apei la întoarcerea din generatorul de abur |
| **TI-003** | THERMOCOUPLE | `temp_fuel_center` | Miez activ / Zona centrală | Temperatura în centrul tijelor de combustibil |
| **PR-001** | PRESSURE_TRANSDUCER | `pressure` | Presurizor | Presiunea circuitului primar (menținută la ~15.5 MPa) |
| **PR-002** | PRESSURE_TRANSDUCER | `steam_pressure` | Generator aburi 1 / Secundar | Presiunea aburului produs în generator |
| **FM-001** | FLOW_METER | `flow_rate_primary` | Pompa P-1A | Debitul agentului primar prin circuit |
| **NI-001** | NEUTRON_DETECTOR | `power_percent` | Excore / Detector A | Flux neutronic — puterea curentă a reactorului |
| **NI-002** | NEUTRON_DETECTOR | `neutron_flux` | Excore / Detector B | Flux neutronic absolut (n/cm²/s) |
| **RM-001** | RADIATION_MONITOR | `activity_primary` | Bypass purificare | Activitatea specifică a apei din primar |
| **RM-002** | RADIATION_MONITOR | `dose_rate_control_room` | Hol reactor | Doza de radiații în zona controlată |
| **LI-001** | LEVEL_SENSOR | `level_reactor_vessel` | Presurizor | Nivelul apei în presurizor |
| **VI-001** | VIBRATION_SENSOR | `vibration` | Pompa P-1A / Lagăre | Vibrațiile pompei principale de circulație |

**Corelații fizice simulate:**
- `temp_coolant_out` urmează `power_percent`
- `temp_coolant_in` = `temp_coolant_out` − 30°C
- `pressure` crește cu `temp_coolant_out` (coeficient 0.04)
- `flow_rate_primary` se corectează cu densitatea (funcție de `temp_coolant_out`)
- `temp_fuel_center` urmează `power_percent`
- `activity_primary` crește când `temp_coolant_out` > 310°C

---

### 6.2 BWR — Boiling Water Reactor

BWR are 11 senzori. Apa fierbe direct în vasul reactorului, aburul merge la turbină.

| Cod | Tip | measurement_field | Locație | Rol |
|-----|-----|------------------|---------|-----|
| **NI-001** | NEUTRON_DETECTOR | `power_percent` | Incinta reactor | Puterea reactorului |
| **TI-001** | THERMOCOUPLE | `temp_coolant_out` | Miez activ / Ieșire | Temperatura apei la ieșirea din miez |
| **TI-002** | THERMOCOUPLE | `temp_fuel_center` | Miez / Zona centrală | Temperatura combustibilului |
| **PR-001** | PRESSURE_TRANSDUCER | `pressure` | Vas reactor | Presiunea în vas (~7 MPa) |
| **PR-002** | PRESSURE_TRANSDUCER | `steam_pressure` | Linie abur / Turbină | Presiunea aburului la turbină |
| **FM-001** | FLOW_METER | `flow_rate_primary` | Pompa RC-1A | Debitul de recirculare (controlează puterea) |
| **FM-002** | FLOW_METER | `steam_flow_rate` | Linie abur principal | Debitul de abur către turbină |
| **RM-001** | RADIATION_MONITOR | `activity_primary` | Linie abur | Activitatea aburului |
| **RM-002** | RADIATION_MONITOR | `dose_rate_reactor_bldg` | Clădire reactor | Doza în clădirea reactorului |
| **LI-001** | LEVEL_SENSOR | `level_reactor_vessel` | Vas reactor | Nivelul apei în vas |
| **VI-001** | VIBRATION_SENSOR | `vibration` | Pompa RC-1A / Lagăre | Vibrațiile pompei de recirculare |

**Corelații fizice simulate:**
- `power_percent` urmează `flow_rate_primary` (debitul de recirculare controlează puterea)
- `temp_coolant_out` și `temp_fuel_center` urmează `power_percent`
- `pressure` urmează `temp_coolant_out` (presiunea de saturație)
- `steam_flow_rate` și `level_reactor_vessel` urmează `power_percent`
- `activity_primary` crește când `temp_coolant_out` > 285°C

---

### 6.3 PHWR — Pressurized Heavy Water Reactor (CANDU)

PHWR are 11 senzori. Folosește D₂O (apă grea) ca moderator și agent de răcire, canale de combustibil separate.

| Cod | Tip | measurement_field | Locație | Rol |
|-----|-----|------------------|---------|-----|
| **NI-001** | NEUTRON_DETECTOR | `power_percent` | Miez activ / Detector A | Puterea reactorului |
| **TI-001** | THERMOCOUPLE | `temp_coolant_out` | Canale combustibil / Ieșire | Temperatura D₂O la ieșirea din canale |
| **TI-002** | THERMOCOUPLE | `temp_coolant_in` | Canale combustibil / Intrare | Temperatura D₂O la intrarea în canale |
| **TI-003** | THERMOCOUPLE | `temp_fuel_center` | Miez / Fascicul combustibil | Temperatura combustibilului |
| **TI-004** | THERMOCOUPLE | `temp_moderator` | Calandria / Miez moderator | Temperatura moderatorului D₂O |
| **PR-001** | PRESSURE_TRANSDUCER | `pressure` | Colector circuit primar | Presiunea D₂O în circuitul primar |
| **PR-002** | PRESSURE_TRANSDUCER | `steam_pressure` | Generator aburi 1 / Secundar | Presiunea aburului |
| **FM-001** | FLOW_METER | `flow_rate_primary` | Pompa PH-1A | Debitul D₂O prin primar |
| **FM-002** | FLOW_METER | `steam_flow_rate` | Linie abur / Turbină | Debitul de abur |
| **RM-001** | RADIATION_MONITOR | `activity_primary` | Circuit purificare moderator | Activitatea tritiului în moderator |
| **RM-002** | RADIATION_MONITOR | `dose_rate_reactor_bldg` | Clădire reactor | Doza ambientală |
| **LI-001** | LEVEL_SENSOR | `level_reactor_vessel` | Calandria | Nivelul moderatorului |
| **VI-001** | VIBRATION_SENSOR | `vibration` | Pompa PH-1A / Lagăre | Vibrațiile pompei principale |

**Corelații fizice simulate:**
- `temp_coolant_out` urmează `power_percent`
- `temp_moderator` e menținut constant la ~70°C de sistemul de răcire independent
- `pressure` urmează `temp_coolant_out`
- `activity_primary` (tritiu) se acumulează proporțional cu `power_percent`
- `flow_rate_primary` urmează `pressure` (sqrt flow-pressure)

---

### 6.4 FBR — Fast Breeder Reactor

FBR are 11 senzori. Răcire cu sodiu lichid, temperaturi ridicate, presiune joasă.

| Cod | Tip | measurement_field | Locație | Rol |
|-----|-----|------------------|---------|-----|
| **NI-001** | NEUTRON_DETECTOR | `power_percent` | Miez activ / Detector A | Puterea reactorului |
| **TI-001** | THERMOCOUPLE | `temp_coolant_out` | Miez / Ieșire sodiu | Temperatura sodiului la ieșirea din miez |
| **TI-002** | THERMOCOUPLE | `temp_coolant_in` | Miez / Intrare sodiu | Temperatura sodiului la intrarea în miez |
| **TI-003** | THERMOCOUPLE | `temp_fuel_center` | Miez / Zona fisibilă | Temperatura combustibilului (MOX) |
| **PR-001** | PRESSURE_TRANSDUCER | `pressure` | Circuit primar | Presiunea sodiului în primar (~0.5 MPa) |
| **PR-002** | PRESSURE_TRANSDUCER | `steam_pressure` | Generator abur 1 / Secundar | Presiunea aburului |
| **FM-001** | FLOW_METER | `flow_rate_primary` | Pompa SP-1A | Debitul sodiului în circuitul primar |
| **FM-002** | FLOW_METER | `flow_rate_secondary` | Pompa SI-1A | Debitul sodiului în circuitul intermediar |
| **RM-001** | RADIATION_MONITOR | `activity_primary` | Bypass Na-24 | Activitatea Na-24 în sodiul primar |
| **RM-002** | RADIATION_MONITOR | `dose_rate_reactor_bldg` | Clădire reactor | Doza ambientală |
| **LI-001** | LEVEL_SENSOR | `level_reactor_vessel` | Vas reactor | Nivelul sodiului în vas |
| **VI-001** | VIBRATION_SENSOR | `vibration` | Pompa SP-1A / Stator | Vibrațiile pompei electromagnetice de sodiu |

**Corelații fizice simulate:**
- `temp_coolant_out` urmează `power_percent` (interval 400–550°C)
- `temp_coolant_in` = `temp_coolant_out` − 150°C
- `pressure` e menținut aproape constant (~0.5 MPa)
- `activity_primary` (Na-24) — producție proporțională cu `power_percent` + dezintegrare naturală
- `flow_rate_primary` urmează `power_percent`
- `vibration` (VI-001) crește quadratic cu `flow_rate_primary` (specific FBR)
- Efectul Doppler: `power_percent` scade ușor dacă `temp_fuel_center` > 800°C (feed-back negativ)

---

## 7. Cum se configurează un reactor manual

Pași:

1. **Crează centrala** prin endpoint-ul de înregistrare a centralei (se generează automat un `power_plant`)
2. **Adaugă reactorul** cu tipul dorit (`PWR`, `BWR`, `PHWR`, `FBR`)
3. **Senzorii se atașează automat** din `sensor_templates` pe baza `reactor_type` — toți senzorii definiți în template pentru acel tip de reactor sunt copiați în `reactor_sensors`
4. **Simulatorul** preia reactorii în stare `OPERATIONAL` / `HOT_STANDBY` și începe să genereze măsurători la fiecare `tick` (1s)
5. Fiecare senzor își scrie valoarea în coloana `measurement_field` din `measurements`

Dacă dorești să adaugi un senzor personalizat, trebuie să:
1. Adaugi un rând în `sensor_templates` cu `reactor_type` potrivit
2. Asigură-te că `measurement_field` corespunde unei coloane existente în `measurements` (sau adaugi coloana)
3. (Opțional) implementezi corelația fizică în simulatorul corespunzător
