# Jurnal de Dezvoltare — Nuclear Power Plant Web Manager

> Generat din git history la data de 2026-06-13.

## 2026-04-05 — Setup inițial

- `8c83698` — initial setup (structură proiect, configurare Apache, composer)

## 2026-04-06 — Configurare PostgreSQL

- `59703ba` — Setup PostgreSQL (tabele inițiale, extensii uuid-ossp, pgcrypto)

## 2026-04-09 — Propuneri diagrame

- `2afc346` — propunere diagrame (mockup-uri arhitectură, wireframe-uri)

## 2026-04-15 — Rapoarte

- `609ac3e` — rapoarte stil europass(template-uri rapoarte)

## 2026-04-21 — Autentificare

- `0245537` — Fixed all errors
- `d386116` — in work user register (formular înregistrare + validare)

## 2026-04-23 — Fixuri setup

- `3158984` — Fixed all errors for setup

## 2026-04-24 — Power Plant basic data (început)

- `a2509c4` — Start work at basic details for Power Plant
- `a2509c4` — Merge pull request #1 din AlexandruIoan20/arhitecture-planing

## 2026-04-25 — Formulare centrale

- `b3304bd` — Create plant form in work
- `834a6a7` — Update plant details in work

## 2026-04-26 — Date basic + geological

- `c8f16b4` — fixed update power plant details
- `1336246` — setup + next button to reach basics form
- `86c5d26` — create basic details for plant
- `88565e6` — update for basic plant data
- `644e2ee` — setup for geological form
- `e3f3e11` — create + update geological informations for plant
- `f8b6c63` — Updated database for accepting valid technical input
- `de5938f` — setup for technical form
- `e248351` — sql

## 2026-04-28 — Technical data + enums

- `cd24e60` — enum sql + pseudorandom generator values
- `8122589` — Technical Repository done

## 2026-04-29 — Formular fezabilitate

- `026fc95` — final technical repo
- `91f1392` — Post technical data done + update technical data in work
- `8d1b714` — Final form for fezability
- `e92e74b` — debug masiv

## 2026-05-04 — Arhitectură + frontend

- `8f540c8` — arhitecture planning
- `02c68ad` — Merge pull request #2: power_plant_form
- `77eb71f` — planu (planificare feature-uri)
- `750c501` — huge merge
- `efded1c` — Merge pull request #4: generator+routing
- `a600cb9` — haialex
- `7148e9c` — start frontend
- `9c0af98` — start frontend
- `36212b7` — Frontend de test
- `25d619e` — Added headers
- `afc5198` — Merge pull request #5: testalex
- `de53d68` — merge solve
- `fabd1fe` — Merge pull request #6: testalex

## 2026-05-06 — Refactor: Facade + Router

- `a82e677` — Refactor: Facade + Router (restructurare backend, sistem de rutare)

## 2026-05-07 — Frontend implementation model + DTO

- `882093f` — frontend implementation model
- `649cea8` — Added DTO example
- `9f50890` — Frontend for update details - model for all forms implementation

## 2026-05-19 — Autentificare completă

- `62f7b14` — Implement complete authentication system with login, sessions, and role-based protection

## 2026-05-20 — Înregistrare + auth

- `631fb11` — auth implemented
- `e7c991b` — Add complete register functionality with validation

## 2026-05-26 — Chain of Responsibility + Checker Tehnic

- `8252fbe` — Start Chain of Responsibility: GeologicalCriticalChecker
- `5ec721a` — Checker Tehnic
- `5bd654b` — frontend skeleton done

## 2026-05-28 — Algoritm fezabilitate

- `746ded5` — algoritm de fezabilitate (calcul scor NSVI)

## 2026-06-02 — Fixuri

- `a2a17e3` — (fixuri)

## 2026-06-04 — Email + rapoarte

- `468eb06` — patches + form logic
- `bbc8082` — Full display for just made report
- `b1349f30` — email system basics in place (PHPMailer, Mailtrap)

## 2026-06-05 — Statusuri + listare

- `ab9f56d` — lista cu centrale ce duce la detaliile despre centrala
- `a8d7c87` — navbar
- `b4fffa1` — functionalitate backend pentru schimbarea statusului
- `58914e4` — seteaza centrala pe review la finalizarea raportului

## 2026-06-06 — Admin approvals

- `6a6b14c` — get plants by status
- `9d23065` — listare dupa status pentru admini
- `533a185` — View project + report si acceptare / respingere de catre admini

## 2026-06-07 — RSS

- `e80ecfe` — rss basic implementation

## 2026-06-08 — Reverse geocoding + approvals

- `6590755` — added reverse fill by API data for coordinates
- `ba63545` — added reverse fill by API data for coordinates
- `6e37ccc` — fixed coordonates bug which caused error
- `c5a9a2b` — implementat approvals + admin acceptance
- `9f616c4` — login uses cookies now, CORS is in distress, reject button added
- `63ae13a` — tabele reactor + modularizare database pe arhitectura domain driven
- `b78bfbc` — CRUD pentru reactoare
- `93ed6a31` — alert system implemented and working with email service
- `e011013` — notification impl. alert+approval
- `53c6328` — seek plant for dev
- `b0d0351` — Repo + Service pentru senzori (fara generarea de valori)
- `807bd68` — Merge pull request #11: moraru/review-plants

## 2026-06-09 — Email service + auth complet

- `f60b522` — merge + refactor geological
- `f71f0f0` — Merge pull request #12: emailservice
- `662d582` — first fixes
- `06b7bdc` — Sistem de autentificare complet
- `8f1c553` — Final

## 2026-06-10 — Senzori + strategii + debugging

- `fdd63d5` — geological refactor
- `5ef93bd` — feasibility bug fixing
- `1f1a7ee` — Merge pull request #14: moraru/debug
- `0365687` — sensor generator strategies (Strategy Pattern pentru generarea valorilor)
- `e6a7599` — Template Method pentru reactoare - comportament fizic
- `f379ad8` — adaugare observere pentru valorile din senzori
- `4fadff4` — Merge pull request #15: moraru/sensors
- `a6c2742` — loguri
- `b087a08` — debugging
- `d97b5cf` — logs working
- `1e8055a` — intermediate debug commit
- `f929670` — frontend flow and page population for backend data
- `5ca7802` — minor ui changes

## 2026-06-11 — CLI Simulator + hărți + NSVI

- `2b609c1` — nsvi fixed
- `7cad7ba` — map ui tweaks
- `2e32d2a` — another map ui tweak
- `0b0f9af` — Cli Php Script pentru generarea valorilor (daemon simulator)
- `57ce527` — xss vuln gestioned
- `856ee4e` — frontend pentru senzori
- `7ab7ca5` — optimizare baza de date

## 2026-06-12 — Feature-uri majore

- `129b558` — start refactor frontend
- `bee16ce` — refactor frontend in desfasurare
- `2d6ae50` — alert observer service
- `658eac6` — Merge pull request #17: new_statistics_logs
- `bb631a5` — fixed conflict
- `cc710df` — Merge pull request #16: moraru/sensors
- `b24289a` — connected alert to reactors
- `5a2f73f` — stats import export
- `6faf3c3` — stats import export
- `4dcccb7` — Admin actions pentru useri + refactor frontend
- `a04f31f` — notificari alerte si approvals
- `b9bd6f4` — modified logs to save notifs and alerts
- `fad890d` — more data to logs
- `51bf768` — tests for import, made import atomic even for a list
- `cd22aec` — logs finalised
- `03ed276` — frontend refactor
- `a74873e` — Bug fixing backend
- `81f9fdb` — reactor creation logic
- `870abb8` — sensor CRUD
- `690a787` — sensor create manual
- `fc0af7f` — Merge pull request #18: statistics_imports
- `91731a8` — final refactor before merge + my plants page
- `ccc0ef9` — fix errors
- `32d0f5d` — Merge pull request #19: moraru/refactor-frontend
- `f535add` — repo logic moved to repo instead of service
- `b8bbcc2` — DTO uri + fix ultiml conflict
- `83455da` — bug fixing

## 2026-06-13 — Final bug fixes + debugging session

- `996e7e9` — debug
- `4e54efc` — Merge pull request #21: last_debug
- `e0e6bb1` — merge finished
- `e2d4feb` — Merge pull request #20: moraru/dto
- `c406460` — rz
- `7d5eb80` — solved conflicts

## Statistici Git

| Metrică | Valoare |
|---|---|
| Total commit-uri | ~110 |
| Autori | 2 (PaulMogos, AlexandruIoan20) |
| Pull request-uri | ~14 |
| Ramuri active | main + feature branches |
| Perioadă de dezvoltare | 5 aprilie — 13 iunie 2026 (~10 săptămâni) |

## Licență

Acest document și întregul proiect sunt licențiate sub [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).
