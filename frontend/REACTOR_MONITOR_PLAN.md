# Plan: Real-time Reactor Monitoring Dashboard

## Obiectiv

Pagină de detaliu reactor care afișează senzorii în timp real, actualizați prin **Server-Sent Events (SSE)**. Valorile se schimbă live (la fiecare ~3s) fără reîncărcare pagină.

## Arhitectură

```
[Backend]  -- SSE (EventSource) -->  [Frontend: detail.html + detail.js]
                               
SSE endpoint: GET /api/reactors/{id}/stream
Date returnate: ReactorStreamDTO (timestamp, reactorId, senzori[])
```

## Fișiere de creat

### 1. `frontend/pages/reactors/detail.html`
- Link: `../../style.css`
- Navbar + topbar cu nume reactor + butoane (Înapoi la listă, Editează)
- Layout:
  - **Header reactor**: cod, tip, status operațional, putere termică/electrică
  - **Senzori**: grilă de carduri (`card-grid` / `grid-3`), câte un card per senzor
- Script: `../../modules/pages/reactors/detail.js`

### 2. `frontend/modules/pages/reactors/detail.js`
- Inițiază `EventSource` la URL-ul SSE
- Pe fiecare mesaj:
  1. Parsează JSON-ul primit (array de `StreamSensorDTO`)
  2. Trimite datele la un modul UI `reactorDetail.js` care face update DOM-ului
- La eroare EventSource: afișează "Conexiune pierdută" + iconiță roșie, încearcă reconnect automat
- La închidere pagină: `eventSource.close()`

### 3. `frontend/modules/ui/reactors/reactorDetail.js`
- **init(containerEl, reactordata)** — construiește scheletul paginii o singură dată
  - Header cu datele reactorului
  - Grilă carduri (câte un div gol per senzor, identificat prin `data-sensor-id`)
- **update(sensors[])** — actualizează cardurile:
  - Valoare + unitate
  - Cod senzor + tip (ex: TEMPERATURE → ℃, PRESSURE → iconiță manometru)
  - Status badge (`NORMAL`=verde, `ALARM`=galben, `ALERT`=portocaliu, `SCRAM`=roșu)
  - **Score strip**: bară verticală colorată care indică poziția valorii între min-max normal
  - Threshold markers: linii subțiri pentru alarmLow, alarmHigh, alertLow, alertHigh, scramLow, scramHigh

### 4. `frontend/modules/dto/StreamSensorDTO.js`
- Clasă helper care validează structura unui senzor din stream

### 5. `frontend/modules/services/sseService.js` (opțional)
- Wrap pentru EventSource cu reconnect logic și parse JSON

## Design & Stil

- Se folosesc clasele CSS existente (`page-shell`, `topbar`, `card-grid`, `card`, `tag`, `badge`)
- Fiecare card senzor:
  - Fundal semi-transparent (folosește `rgba(255,255,255,0.02)` ca `.card`)
  - Border color după status: `NORMAL`=verde, `ALARM`=galben, `ALERT`=portocaliu, `SCRAM`=roșu
  - Valoarea mare, bold, monospace
  - Bara de progres (normal range) — un `div` cu `background: linear-gradient` verde→roșu
- Animații: valoarea face `transition: color 0.3s` și un scurt `scale(1.05)` la schimbare

## Flux utilizator

1. Utilizatorul e pe `/pages/reactors/list.html?plantId=...` → dă click pe un rând
2. Se deschide `/pages/reactors/detail.html?reactorId=...`
3. Se încarcă datele reactorului (GET /api/reactors/{id})
4. Senzorii apar instant cu ultimele valori
5. EventSource deschide conexiunea SSE
6. La fiecare ~3s, cardurile se actualizează live
7. Dacă un senzor intră în ALERT/SCRAM, un sunet scurt + flash pe card

## Considerații tehnice

- SSE-ul backend cere cookie de sesiune, dar **nu** necesită CSRF token (e GET)
- EventSource trimite automat cookie-urile cu `withCredentials`
- Dacă backend-ul nu e pornit, EventSource încearcă reconnect după 3s (comportament standard)
- La reconnect, EventSource primește din nou toți senzorii cu valori curente

## Dependințe

- Backend: ruta `/api/reactors/{id}/stream` deja existentă
- Backend: ruta `/api/reactors/{id}` deja existentă (pentru datele reactorului)
- Frontend: `style.css` existent, `navbar.js` existent
