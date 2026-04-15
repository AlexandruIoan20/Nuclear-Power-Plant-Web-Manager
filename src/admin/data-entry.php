<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Data Entry - Nuclear Manager</title>
    <link rel="stylesheet" href="/assets/css/admin-form.css">
</head>
<body>
<div class="container">
    <h1>Formular Admin - Reactor Report</h1>

    <form action="/admin/save-entry.php" method="POST">
        <section class="card">
            <h2>1) Date centrală</h2>

            <label for="plant_name">Nume centrală</label>
            <input id="plant_name" type="text" name="plant_name" required>

            <label for="country">Țară</label>
            <input id="country" type="text" name="country" required>

            <label for="soil_type">Tip sol</label>
            <input id="soil_type" type="text" name="soil_type" placeholder="ex: stâncos, argilos">
        </section>

        <section class="card">
            <h2>2) Date reactor</h2>

            <label for="reactor_code">Cod reactor</label>
            <input id="reactor_code" type="text" name="reactor_code" required>

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="operational">Operațional</option>
                <option value="maintenance">Mentenanță</option>
                <option value="stopped">Oprit</option>
                <option value="alert">Alertă</option>
            </select>

            <label for="efficiency">Eficiență (%)</label>
            <input id="efficiency" type="number" name="efficiency" min="0" max="100" step="0.01">

            <label for="risk_level">Nivel risc (1-5)</label>
            <input id="risk_level" type="number" name="risk_level" min="1" max="5">
        </section>

        <section class="card">
            <h2>3) Condiții externe</h2>

            <label for="weather">Meteo</label>
            <input id="weather" type="text" name="weather" placeholder="ex: vânt puternic, ploaie">

            <label for="wear_level">Nivel uzură (1-5)</label>
            <input id="wear_level" type="number" name="wear_level" min="1" max="5">

            <div class="checks">
                <label><input type="checkbox" name="planned_maintenance" value="1"> Oprire planificată</label>
                <label><input type="checkbox" name="unplanned_stop" value="1"> Oprire neplanificată</label>
            </div>
        </section>

        <section class="card">
            <h2>4) Metrici suplimentare (dinamice)</h2>
            <div id="metrics-wrapper"></div>
            <button type="button" id="addMetricBtn" class="secondary">+ Adaugă metrică</button>
        </section>

        <section class="card">
            <h2>5) Observații</h2>
            <label for="notes">Note</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Detalii tehnice, evenimente, decizii..."></textarea>
        </section>

        <div class="actions">
            <button type="submit" class="primary">Salvează raport</button>
            <a href="/admin/entries.php" class="link-btn">Vezi rapoarte</a>
        </div>
    </form>
</div>

<script>
const wrapper = document.getElementById('metrics-wrapper');
const addMetricBtn = document.getElementById('addMetricBtn');

function createMetricRow() {
    const row = document.createElement('div');
    row.className = 'metric-row';
    row.innerHTML = `
        <input type="text" name="metric_name[]" placeholder="Nume metrică (ex: neutron_flux)">
        <input type="text" name="metric_value[]" placeholder="Valoare (ex: 1.23e5)">
        <button type="button" class="danger removeMetric">Șterge</button>
    `;
    wrapper.appendChild(row);
}

addMetricBtn.addEventListener('click', createMetricRow);

wrapper.addEventListener('click', (event) => {
    if (event.target.classList.contains('removeMetric')) {
        event.target.closest('.metric-row').remove();
    }
});
</script>
</body>
</html>
