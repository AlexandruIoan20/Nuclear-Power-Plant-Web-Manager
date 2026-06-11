<?php

require_once __DIR__ . '/src/Entities/SensorQuality.php';
require_once __DIR__ . '/src/Entities/ReactorSensor.php';
require_once __DIR__ . '/src/Repositories/AlertRepository.php';
require_once __DIR__ . '/src/Repositories/ReactorRepository.php';
require_once __DIR__ . '/src/Repositories/SensorRepository.php';
require_once __DIR__ . '/src/Repositories/MeasurementsRepository.php';
require_once __DIR__ . '/src/Services/EmailService.php';
require_once __DIR__ . '/src/Services/LogService.php';

require_once __DIR__ . '/src/Services/SimulatorService/ReactorSimulator/AbstractReactorSimulator.php';
require_once __DIR__ . '/src/Services/SimulatorService/ReactorSimulator/PwrSimulator.php';
require_once __DIR__ . '/src/Services/SimulatorService/Observers/AlertObserver.php';
require_once __DIR__ . '/src/Services/SimulatorService/Observers/ScramObserver.php';
require_once __DIR__ . '/src/Services/SimulatorService/Observers/NotificationObserver.php';

$host     = getenv('DB_HOST')     ?: 'db';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'proiect_db';
$username = getenv('DB_USER')     ?: 'admin';
$password = getenv('DB_PASSWORD') ?: 'glorierebeja';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

LogService::init($pdo);

echo "=== TEST OBSERVERI REACTOR ===\n\n";

// --- Setup: centrala + reactor PWR + senzor THERMOCOUPLE ---
$plantId = 'a0000000-0000-0000-0000-000000000001';
$pdo->prepare("
    INSERT INTO power_plants (id, name, status, created_by)
    VALUES (:id, 'Centrala de Test', 'APPROVED', (SELECT id FROM users LIMIT 1))
    ON CONFLICT (id) DO NOTHING
")->execute(['id' => $plantId]);
echo "[OK] Centrala de test: $plantId\n";

$reactorId = 'b0000000-0000-0000-0000-000000000001';
$pdo->prepare("
    INSERT INTO reactor (id, power_plant_id, reactor_code, reactor_type, cooling_type, operational_status)
    VALUES (:id, :plant_id, 'R-TEST-001', 'PWR', 'NATURAL_DRAFT_WET', 'FULL_POWER')
    ON CONFLICT (id) DO NOTHING
")->execute(['id' => $reactorId, 'plant_id' => $plantId]);
echo "[OK] Reactor PWR: $reactorId\n";

$sensorId = 'c0000000-0000-0000-0000-000000000001';
$pdo->prepare("
    INSERT INTO reactor_sensors (
        id, reactor_id, sensor_code, sensor_type, description, location_zone,
        unit_of_measure, measurement_field,
        normal_min, normal_max, alarm_low, alarm_high,
        alert_low, alert_high, scram_low, scram_high,
        current_value, is_active
    ) VALUES (
        :id, :reactor_id, 'TI-TEST', 'THERMOCOUPLE', 'Senzor test iesire',
        'Test', '°C', 'temp_coolant_out',
        290, 325,   285, 330,
        280, 340,   270, 350,
        300, true
    )
    ON CONFLICT (id) DO UPDATE SET current_value = 300
")->execute(['id' => $sensorId, 'reactor_id' => $reactorId]);
echo "[OK] Senzor THERMOCOUPLE: $sensorId\n";
echo "    Praguri: WARNING>340 | ALERT>330 | EMERGENCY>350\n\n";

// --- Instanțiază ---
$sensorRepo = new SensorRepository($pdo);
$measurementsRepo = new MeasurementsRepository($pdo);
$reactorRepo = new ReactorRepository($pdo);
$alertRepo = new AlertRepository($pdo);
$emailService = new EmailService();

$simulator = new PwrSimulator($sensorRepo, $measurementsRepo, $reactorRepo);
$simulator->attachObserver(new AlertObserver($alertRepo));
$simulator->attachObserver(new ScramObserver($reactorRepo, $alertRepo));
$simulator->attachObserver(new NotificationObserver($emailService, $alertRepo));
echo "[OK] Observeri atașați\n\n";

// ================================================================
// TEST 1: EMERGENCY (valoare > scram_high=350)
// ================================================================
echo "--- TEST 1: EMERGENCY ---\n";
$pdo->prepare("UPDATE reactor_sensors SET current_value = 355 WHERE id = :id")
    ->execute(['id' => $sensorId]);
$simulator->tick($reactorId);

$stmt = $pdo->prepare("SELECT * FROM reactor_alerts WHERE reactor_id = :rid AND severity = 'EMERGENCY'");
$stmt->execute(['rid' => $reactorId]);
$row = $stmt->fetch();

if ($row) {
    echo "[OK]  Alertă EMERGENCY salvată\n";
    echo "      Type={$row['type']}, Value={$row['value']}, Threshold={$row['threshold']}\n";
    echo "      Message: {$row['message']}\n";
} else {
    echo "[FAIL] Nicio alertă EMERGENCY\n";
}

$status = $pdo->prepare("SELECT operational_status FROM reactor WHERE id = :id");
$status->execute(['id' => $reactorId]);
$s = $status->fetchColumn();
if ($s === 'EMERGENCY_SHUTDOWN') {
    echo "[OK]  Reactor -> EMERGENCY_SHUTDOWN\n";
} else {
    echo "[FAIL] Reactor status: $s (așteptam EMERGENCY_SHUTDOWN)\n";
}
echo "\n";

// ================================================================
// TEST 2: ALERT (valoare > alarm_high=330, dar < scram_high=350)
// Resetăm reactorul pe FULL_POWER întâi
// ================================================================
echo "--- TEST 2: ALERT (severity ALERT) ---\n";
$pdo->prepare("UPDATE reactor_sensors SET current_value = 335 WHERE id = :id")
    ->execute(['id' => $sensorId]);
$pdo->prepare("UPDATE reactor SET operational_status = 'FULL_POWER' WHERE id = :id")
    ->execute(['id' => $reactorId]);
$simulator->tick($reactorId);

$stmt = $pdo->prepare("SELECT * FROM reactor_alerts WHERE reactor_id = :rid AND severity = 'ALERT'");
$stmt->execute(['rid' => $reactorId]);
$row = $stmt->fetch();

if ($row) {
    echo "[OK]  Alertă ALERT salvată\n";
    echo "      Type={$row['type']}, Value={$row['value']}, Threshold={$row['threshold']}\n";
    echo "      Message: {$row['message']}\n";
} else {
    echo "[FAIL] Nicio alertă ALERT\n";
}
echo "\n";

// ================================================================
// TEST 3: WARNING (valoare > alert_high=340, dar < alarm_high=330)
// Alege valoarea 345 (340<345<330 nu-i adevărat; 345>330 deci ALERT)
// Folosim un alt prag: scădem alarm_high să fie mai mare decât alert_high
// De fapt, ordinea e: EMERGENCY > ALERT > WARNING
// 345 > 330 (alarm_high) → ALERT, nu WARNING.
// Pentru WARNING trebuie o valoare între alert_high și alarm_high,
// dar alarm_high (330) < alert_high (340) în setup.
// Refacem: alert_high=330, alarm_high=340
// Atunci 335 > 330 (alert_high) dar <340 (alarm_high) → WARNING
// ================================================================
echo "--- TEST 3: WARNING (severity WARNING) ---\n";

// Reconfigurem senzorul cu ordinea corectă: alert_high < alarm_high < scram_high
$pdo->prepare("
    UPDATE reactor_sensors SET
        alert_high = 330,
        alarm_high = 340,
        scram_high = 350,
        current_value = 335
    WHERE id = :id
")->execute(['id' => $sensorId]);
echo "[INFO] Praguri noi: WARNING>330 | ALERT>340 | EMERGENCY>350\n";

$simulator->tick($reactorId);

$stmt = $pdo->prepare("SELECT * FROM reactor_alerts WHERE reactor_id = :rid AND severity = 'WARNING' ORDER BY created_at DESC");
$stmt->execute(['rid' => $reactorId]);
$row = $stmt->fetch();

if ($row) {
    echo "[OK]  Alertă WARNING salvată\n";
    echo "      Type={$row['type']}, Value={$row['value']}, Threshold={$row['threshold']}\n";
    echo "      Message: {$row['message']}\n";
} else {
    echo "[FAIL] Nicio alertă WARNING\n";
}
echo "\n";

echo "=== TEST FINALIZAT ===\n";
