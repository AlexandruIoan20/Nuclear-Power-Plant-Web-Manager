<?php 

require_once __DIR__ . '/../config/scripts.php'; 
require_once __DIR__ . '/../src/Services/SimulatorService/SimulatorService.php'; 

require_once __DIR__ . '/../src/Repositories/SensorRepository.php'; 
require_once __DIR__ . '/../src/Repositories/MeasurementsRepository.php'; 
require_once __DIR__ . '/../src/Repositories/ReactorRepository.php'; 
require_once __DIR__ . '/../src/Repositories/AlertRepository.php'; 

require_once __DIR__ . '/../src/Services/EmailService.php'; 

require_once __DIR__ . '/../src/Services/LogService.php';
require_once __DIR__ . '/../src/Services/SimulatorService/Observers/AlertObserver.php'; 
require_once __DIR__ . '/../src/Services/SimulatorService/Observers/ScramObserver.php'; 
require_once __DIR__ . '/../src/Services/SimulatorService/Observers/NotificationObserver.php'; 
require_once __DIR__ . '/../src/Services/LogService.php'; 

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

// $dsn = "pgsql:host=$host;port=$port;dbname=$dbname"; // dev
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require"; // prod

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

LogService::init($db);

$sensorRepository = new SensorRepository($db); 
$measurementsRepository = new MeasurementsRepository($db); 
$reactorRepository = new ReactorRepository($db); 
$alertRepository = new AlertRepository($db); 
$emailService = new EmailService();
LogService::init($db);

$simulatorService = new SimulatorService($sensorRepository, $measurementsRepository, $reactorRepository, SIMULATOR_TICK_INTERVAL);
$simulatorService->attachObserver(new AlertObserver($alertRepository)); 
$simulatorService->attachObserver(new ScramObserver($reactorRepository)); 
$simulatorService->attachObserver(new NotificationObserver($emailService, $alertRepository)); 

echo "[" . date("Y-m-d H:i:s") . "] Simulator pornit (tick: " . SIMULATOR_TICK_INTERVAL . "s)\n"; 
$simulatorService->run(); 
echo "[" . date("Y-m-d H:i:s") . "] Simulator oprit\n"; 
