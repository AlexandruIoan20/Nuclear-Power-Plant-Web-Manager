<?php
/**
 * Seed script: creează 4 reactoare sub centrala "test"
 * și le populează senzorii din template-uri.
 */

$host     = getenv('DB_HOST')     ?: 'db';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'proiect_db';
$username = getenv('DB_USER')     ?: 'admin';
$password = getenv('DB_PASSWORD') ?: 'glorierebeja';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$stmt = $pdo->query("SELECT id FROM power_plants WHERE name = 'test' LIMIT 1");
$plantId = $stmt->fetchColumn();
if (!$plantId) {
    echo "ERROR: Nu am găsit centrala 'test'.\n";
    exit(1);
}
echo "Centrala 'test' are ID: $plantId\n";

// Definim cele 4 reactoare cu date complete
$reactors = [
    [
        'id'          => 'a0000001-0000-0000-0000-000000000001',
        'code'        => 'PWR-ALPHA',
        'type'        => 'PWR',
        'cooling'     => 'ONCE_THROUGH_FRESH',
        'status'      => 'HOT_STANDBY',
        'thermal_mw'  => 3000.00,
        'electrical'  => 1000.00,
        'fuel_days'   => 548,
        'cycle_day'   => 0,
        'wear'        => 0.0000,
        'lifetime'    => 40,
        'desc'        => 'Reactor PWR de test - presurizat cu apă ușoară',
    ],
    [
        'id'          => 'a0000001-0000-0000-0000-000000000002',
        'code'        => 'BWR-BETA',
        'type'        => 'BWR',
        'cooling'     => 'ONCE_THROUGH_FRESH',
        'status'      => 'SHUTDOWN',
        'thermal_mw'  => 2900.00,
        'electrical'  => 950.00,
        'fuel_days'   => 548,
        'cycle_day'   => 0,
        'wear'        => 0.0000,
        'lifetime'    => 40,
        'desc'        => 'Reactor BWR de test - apă ușoară fierbătoare',
    ],
    [
        'id'          => 'a0000001-0000-0000-0000-000000000003',
        'code'        => 'PHWR-GAMMA',
        'type'        => 'PHWR',
        'cooling'     => 'NATURAL_DRAFT_WET',
        'status'      => 'SHUTDOWN',
        'thermal_mw'  => 2600.00,
        'electrical'  => 850.00,
        'fuel_days'   => 450,
        'cycle_day'   => 0,
        'wear'        => 0.0000,
        'lifetime'    => 40,
        'desc'        => 'Reactor PHWR de test - apă grea sub presiune (CANDU-like)',
    ],
    [
        'id'          => 'a0000001-0000-0000-0000-000000000004',
        'code'        => 'FBR-DELTA',
        'type'        => 'FBR',
        'cooling'     => 'DRY_COOLING',
        'status'      => 'SHUTDOWN',
        'thermal_mw'  => 2500.00,
        'electrical'  => 900.00,
        'fuel_days'   => 720,
        'cycle_day'   => 0,
        'wear'        => 0.0000,
        'lifetime'    => 50,
        'desc'        => 'Reactor FBR de test - cu neutroni rapizi și sodiu',
    ],
];

$insertReactor = $pdo->prepare("
    INSERT INTO reactor (id, power_plant_id, reactor_code, reactor_type, cooling_type,
                          operational_status, thermal_power_mw, electrical_power_mw,
                          fuel_cycle_days, current_cycle_day, wear_index,
                          design_lifetime_yr, description)
    VALUES (:id, :plant_id, :code, :type::reactor_types, :cooling::cooling_types,
            :status::reactor_operational_status, :thermal, :electrical,
            :fuel_days, :cycle_day, :wear,
            :lifetime, :desc)
    ON CONFLICT (id) DO NOTHING
");

// Load entities for sensor population
require_once __DIR__ . '/../src/Helpers/generateUUID.php';
require_once __DIR__ . '/../src/Entities/ReactorType.php';
require_once __DIR__ . '/../src/Entities/SensorType.php';
require_once __DIR__ . '/../src/Entities/SensorQuality.php';
require_once __DIR__ . '/../src/Entities/SensorTemplate.php';
require_once __DIR__ . '/../src/Entities/ReactorSensor.php';
require_once __DIR__ . '/../src/Repositories/SensorRepository.php';
require_once __DIR__ . '/../src/Repositories/SensorTemplateRepository.php';

$sensorRepo = new SensorRepository($pdo);
$templateRepo = new SensorTemplateRepository($pdo);

foreach ($reactors as $r) {
    $insertReactor->execute([
        'id'         => $r['id'],
        'plant_id'   => $plantId,
        'code'       => $r['code'],
        'type'       => $r['type'],
        'cooling'    => $r['cooling'],
        'status'     => $r['status'],
        'thermal'    => $r['thermal_mw'],
        'electrical' => $r['electrical'],
        'fuel_days'  => $r['fuel_days'],
        'cycle_day'  => $r['cycle_day'],
        'wear'       => $r['wear'],
        'lifetime'   => $r['lifetime'],
        'desc'       => $r['desc'],
    ]);
    echo "Inserted reactor {$r['code']} ({$r['type']})\n";

    $reactorType = ReactorType::tryFrom($r['type']);
    $templates = $templateRepo->findByReactorType($reactorType);
    $sensorRepo->insertBulk($r['id'], $templates);
    echo "  -> Populated " . count($templates) . " sensors\n";
}

echo "\nDone! 4 reactoare create cu senzorii aferenți.\n";
