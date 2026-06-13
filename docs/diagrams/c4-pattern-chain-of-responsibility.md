# Diagrama C4 — Pattern: Chain of Responsibility + Strategy

## Lanțul de verificare a fezabilității (NSVI)

```mermaid
classDiagram
  class AbstractFeasibilityChecker {
    <<abstract>>
    - AbstractFeasibilityChecker next
    + setNext(AbstractFeasibilityChecker) AbstractFeasibilityChecker
    + check(array plantData, array reactorTypes) FeasibilityResult
    # nextCheck(array plantData, array reactorTypes) FeasibilityResult
  }

  class GeologicalCriticalChecker {
    + check(array plantData, array reactorTypes) FeasibilityResult
    - checkSoilType() bool
    - checkSeismicStability() bool
    - checkPopulationDensity() bool
    - checkFloodRisk() bool
  }

  class TechnicalCriticalChecker {
    + check(array plantData, array reactorTypes) FeasibilityResult
    - checkEfficiency() bool
    - checkNumberOfReactors() bool
  }

  class ScoringChecker {
    + check(array plantData, array reactorTypes) FeasibilityResult
    - calculateNSVI() float
    - getStrategyForReactorType() ScoringStrategy
  }

  class FeasibilityServiceFactory {
    + create() AbstractFeasibilityChecker
  }

  AbstractFeasibilityChecker <|-- GeologicalCriticalChecker
  AbstractFeasibilityChecker <|-- TechnicalCriticalChecker
  AbstractFeasibilityChecker <|-- ScoringChecker
  AbstractFeasibilityChecker o--> AbstractFeasibilityChecker : next

  FeasibilityServiceFactory ..> AbstractFeasibilityChecker : creează lanțul

  class FeasibilityResult {
    + string status
    + float nsvi_score
    + array deficiencies
    + array errors
    + string message
  }

  class ScoringStrategy {
    <<interface>>
    + calculate(array data, object params) float
  }

  class PwrStrategy {
    + calculate(array data, object params) float
  }

  class BwrStrategy {
    + calculate(array data, object params) float
  }

  class PhwrStrategy {
    + calculate(array data, object params) float
  }

  class FbrStrategy {
    + calculate(array data, object params) float
  }

  ScoringStrategy <|.. PwrStrategy
  ScoringStrategy <|.. BwrStrategy
  ScoringStrategy <|.. PhwrStrategy
  ScoringStrategy <|.. FbrStrategy
  ScoringChecker --> ScoringStrategy
```

## Lanțul de verificare — flux

```
  FeasibilityServiceFactory
        │
        ▼
  GeologicalCriticalChecker → Verifică: sol instabil? seismic < 4? populație > 500?
        │                     transport < 3? debit apă < 20? inundații > 8? freatic < 2m?
        │
        ▼  (dacă OK)
  TechnicalCriticalChecker  → Verifică: eficiență între 15-45%? nr reactoare între 1-8?
        │
        ▼  (dacă OK)
  ScoringChecker → Pentru fiecare tip reactor:
                        ├── PWR → PwrStrategy
                        ├── BWR → BwrStrategy
                        ├── PHWR → PhwrStrategy
                        └── FBR → FbrStrategy
                   Media ponderată = NSVI (0-100)
```

## Criterii de scor NSVI

| Scor NSVI | Status | Acțiune |
|---|---|---|
| ≥ 75 | APPROVED | Centrala poate fi construită |
| ≥ 50 | REVIEW | Necesită revizuire |
| < 50 | REJECTED | Locație nepotrivită |

## Strategii de scor per tip reactor

| Strategie | Penalizări specifice |
|---|---|
| **PwrStrategy** | Eficiență scăzută, proximitate mare apă, debit mic, construcție lungă, risc geologic mare |
| **BwrStrategy** | Seismic sub prag, populație peste prag, transport slab, eficiență scăzută, construcție lungă |
| **PhwrStrategy** | Freatic scăzut (risc tritiu), construcție lungă, eficiență scăzută, seismic scăzut (vulnerabilitate Calandria) |
| **FbrStrategy** | Inundații mari, seismic scăzut, eficiență scăzută, construcție lungă |

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
