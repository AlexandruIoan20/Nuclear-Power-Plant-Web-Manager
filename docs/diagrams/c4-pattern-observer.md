# Diagrama C4 — Pattern: Observer

## Sistemul de alerte și notificări

```mermaid
classDiagram
  class ObserverInterface {
    <<interface>>
    + update(ViolationEvent event) void
  }

  class SimulatorService {
    - ObserverInterface[] observers
    + attachObserver(ObserverInterface observer) void
    + run() void
    - notifyObservers(ViolationEvent event) void
    - getSimulator(Reactor reactor) AbstractReactorSimulator
  }

  SimulatorService o--> ObserverInterface : notifică

  class AlertObserver {
    - int debounce = 60
    + update(ViolationEvent event) void
    - saveReactorAlert(ViolationEvent) void
    - savePlantAlert(ViolationEvent) void
  }

  class NotificationObserver {
    - int debounce = 300
    + update(ViolationEvent event) void
    - saveNotification(ViolationEvent) void
    - sendEmailAlert(ViolationEvent) void
    - logEvent(ViolationEvent) void
  }

  class ScramObserver {
    - int debounce = 60
    + update(ViolationEvent event) void
    - setEmergencyShutdown(string reactorId) void
    - setUnplannedOutage(string reactorId) void
    - logScramEvent(ViolationEvent) void
  }

  ObserverInterface <|.. AlertObserver
  ObserverInterface <|.. NotificationObserver
  ObserverInterface <|.. ScramObserver

  class ViolationEvent {
    + string severity      // WARNING | ALERT | EMERGENCY
    + float value
    + ReactorSensor sensor
    + string reactorId
    + string plantId
    + float threshold
    + string timestamp
  }

  class AlertRepository {
    + saveReactorAlert(array data) void
    + savePlantEvent(string plantId, string type, string message) void
    + getPlantOwnerEmail(string plantId) string
  }

  class EmailService {
    + sendAlert(array data) bool
  }

  AlertObserver --> AlertRepository
  NotificationObserver --> AlertRepository
  NotificationObserver --> EmailService
  ScramObserver --> AlertRepository
```

## Comportamentul observatorilor

| Observer | Debounce | Acțiune la WARNING | Acțiune la ALERT | Acțiune la EMERGENCY |
|---|---|---|---|---|
| **AlertObserver** | 60s | Salvează reactor_alert | Salvează reactor_alert + alert | Salvează reactor_alert + alert |
| **NotificationObserver** | 300s | — | Salvează + loghează | Salvează + loghează + **trimite email** |
| **ScramObserver** | 60s | — | Setează UNPLANNED_OUTAGE | Setează **EMERGENCY_SHUTDOWN** + loghează |

## Fluxul unei alerte EMERGENCY (SCRAM)

```
  Simulator tick()
       │
       ▼
  ThresholdChecker.checkAll()
       │ detectează: neutron_flux > scram_high (8.5e14 > 7.2e14)
       │ severity = EMERGENCY
       ▼
  SimulatorService.notifyObservers(ViolationEvent)
       │
       ├──→ AlertObserver (debounce 60s)
       │       └── INSERT reactor_alert (type=SCRAM, severity=EMERGENCY)
       │
       ├──→ ScramObserver (debounce 60s)
       │       └── UPDATE reactor SET operational_status = 'EMERGENCY_SHUTDOWN'
       │
       └──→ NotificationObserver (debounce 300s, prima dată instant)
               ├── INSERT alert + log entry
               └── EmailService.sendAlert()
                       └── PHPMailer → SMTP (Mailtrap)
```

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
