<?php 

require_once __DIR__ . '/../config/scripts.php'; 
require_once __DIR__ . '/../src/Services/SimulatorService/SimulatorService.php'; 

require_once __DIR__ . '/../src/Repositories/SensorRepository.php'; 
require_once __DIR__ . '/../src/Repositories/MeasurementsRepository.php'; 
require_once __DIR__ . '/../src/Repositories/ReactorRepository.php'; 

require_once __DIR__ . '/../src/Services/SimulatorService/Observers/AlertObserver.php'; 
require_once __DIR__ . '/../src/Services/SimulatorService/Observers/ScramObserver.php'; 
require_once __DIR__ . '/../src/Services/SimulatorService/Observers/NotificationObserver.php'; 

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'proiect_db';
$username = getenv('DB_USER') ?: 'admin';
$password = getenv('DB_PASSWORD') ?: 'glorierebeja';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $db = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "[" . date("Y-m-d H:i:s") . "] Eroare conexiune DB: " . $e->getMessage() . "\n");
    exit(1);
}

$sensorRepository = new SensorRepository($db); 
$measurementsRepository = new MeasurementsRepository($db); 
$reactorRepository = new ReactorRepository($db); 

$simulatorService = new SimulatorService($sensorRepository, $measurementsRepository, $reactorRepository, SIMULATOR_TICK_INTERVAL); 
$simulatorService->attachObserver(new AlertObserver()); 
$simulatorService->attachObserver(new ScramObserver()); 
$simulatorService->attachObserver(new NotificationObserver()); 

echo "[" . date("Y-m-d H:i:s") . "] Simulator pornit (tick: " . SIMULATOR_TICK_INTERVAL . "s)\n"; 
$simulatorService->run(); 
echo "[" . date("Y-m-d H:i:s") . "] Simulator oprit\n"; 
