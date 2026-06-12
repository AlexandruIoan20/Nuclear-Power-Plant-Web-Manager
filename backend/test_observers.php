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

$plantId = 'a0000000-0000-0000-0000-000000000001';
$reactorId = 'b0000000-0000-0000-0000-000000000001';
$sensorId = 'c0000000-0000-0000-0000-000000000001';

echo "=== TEST OBSERVERI REACTOR ===\n\n";

// --- Setup ---
$pdo->prepare("
    INSERT INTO power_plants (id, name, status, created_by)
    VALUES (:id, 'Centrala de Test', 'APPROVED', (SELECT id FROM users LIMIT 1))
    ON CONFLICT (id) DO NOTHING
")->execute(['id' => $plantId]);

$pdo->prepare("
    INSERT INTO reactor (id, power_plant_id, reactor_code, reactor_type, cooling_type, operational_status)
    VALUES (:id, :plant_id, 'R-TEST-001', 'PWR', 'NATURAL_DRAFT_WET', 'FULL_POWER')
    ON CONFLICT (id) DO NOTHING
")->execute(['id' => $reactorId, 'plant_id' => $plantId]);

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

$sensorRepo = new SensorRepository($pdo);
$measurementsRepo = new MeasurementsRepository($pdo);
$reactorRepo = new ReactorRepository($pdo);
$alertRepo = new AlertRepository($pdo);
$emailService = new EmailService();

$simulator = new PwrSimulator($sensorRepo, $measurementsRepo, $reactorRepo);
$simulator->attachObserver(new AlertObserver($alertRepo));
$simulator->attachObserver(new ScramObserver($reactorRepo));
$simulator->attachObserver(new NotificationObserver($emailService, $alertRepo));
echo "[OK] Observeri atașați\n\n";

// Ștergem alertele vechi ca să nu polueze testul
$pdo->exec("DELETE FROM reactor_alerts WHERE plant_id = '{$plantId}'");
$pdo->exec("DELETE FROM alerts WHERE plant_id = '{$plantId}'");

$checkAlertTable = function($label, $severity, $expectedType, $expectedStatus) use ($pdo, $reactorId, $plantId) {
    echo "--- $label ---\n";

    $ra = $pdo->prepare("SELECT * FROM reactor_alerts WHERE reactor_id = :rid AND severity = :sev ORDER BY created_at DESC LIMIT 1");
    $ra->execute(['rid' => $reactorId, 'sev' => $severity]);
    $rowRa = $ra->fetch();

    $al = $pdo->prepare("SELECT * FROM alerts WHERE plant_id = :pid ORDER BY created_at DESC LIMIT 1");
    $al->execute(['pid' => $plantId]);
    $rowAl = $al->fetch();

    $ok = true;

    if ($rowRa) {
        echo "  [OK]  reactor_alerts: type={$rowRa['type']}, severity={$rowRa['severity']}, value={$rowRa['value']}, threshold={$rowRa['threshold']}\n";
        if ($rowRa['type'] !== $expectedType) {
            echo "  [FAIL] Așteptam type=$expectedType în reactor_alerts, e {$rowRa['type']}\n";
            $ok = false;
        }
    } else {
        echo "  [FAIL] Nicio alertă severity=$severity în reactor_alerts\n";
        $ok = false;
    }

    if ($rowAl) {
        echo "  [OK]  alerts: type={$rowAl['alert_type']}, message={$rowAl['message']}\n";
    } else {
        echo "  [FAIL] Nicio alertă în alerts (pop-up)!\n";
        $ok = false;
    }

    if ($expectedStatus !== null) {
        $st = $pdo->prepare("SELECT operational_status FROM reactor WHERE id = :id");
        $st->execute(['id' => $reactorId]);
        $s = $st->fetchColumn();
        if ($s === $expectedStatus) {
            echo "  [OK]  Reactor status=$expectedStatus\n";
        } else {
            echo "  [FAIL] Reactor status=$s (așteptam $expectedStatus)\n";
            $ok = false;
        }
    }

    if ($ok) echo "  [PASS]\n";
    else echo "  [FAIL]\n";
    echo "\n";
};

// ================================================================
// TEST 1: EMERGENCY (valoare > scram_high=350, praguri implicite)
// ================================================================
$pdo->prepare("
    UPDATE reactor_sensors SET
        alert_high = 340, alarm_high = 330, scram_high = 350,
        current_value = 355
    WHERE id = :id
")->execute(['id' => $sensorId]);
$simulator->tick($reactorId);
$checkAlertTable('TEST 1: EMERGENCY (355 > scram_high=350)', 'EMERGENCY', 'SCRAM', 'EMERGENCY_SHUTDOWN');

// ================================================================
// TEST 2: ALERT (valoare > alarm_high=330, dar < scram_high=350)
// ================================================================
$pdo->prepare("
    UPDATE reactor_sensors SET
        alert_high = 340, alarm_high = 330, scram_high = 350,
        current_value = 335
    WHERE id = :id
")->execute(['id' => $sensorId]);
$pdo->prepare("UPDATE reactor SET operational_status = 'FULL_POWER' WHERE id = :id")
    ->execute(['id' => $reactorId]);
$simulator->tick($reactorId);
$checkAlertTable('TEST 2: ALERT (335 > alarm_high=330)', 'ALERT', 'ALARM', 'UNPLANNED_OUTAGE');

// ================================================================
// TEST 3: WARNING (valoare între alert_high și alarm_high)
// Reconfigurăm: alert_high=330 < alarm_high=340 < scram_high=350
// ================================================================
$pdo->prepare("
    UPDATE reactor_sensors SET
        alert_high = 330, alarm_high = 340, scram_high = 350,
        current_value = 335
    WHERE id = :id
")->execute(['id' => $sensorId]);
$pdo->prepare("UPDATE reactor SET operational_status = 'FULL_POWER' WHERE id = :id")
    ->execute(['id' => $reactorId]);
$simulator->tick($reactorId);
$checkAlertTable('TEST 3: WARNING (335 > alert_high=330, < alarm_high=340)', 'WARNING', 'ALERT', null);

echo "=== TEST FINALIZAT ===\n";
