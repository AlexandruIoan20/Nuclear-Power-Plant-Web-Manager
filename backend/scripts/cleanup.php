<?php 

require_once __DIR__ . '/../config/scripts.php'; 
require_once __DIR__ . '/../src/Repositories/MeasurementsRepository.php'; 
require_once __DIR__ . '/../src/Services/LogService.php'; 

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

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

LogService::init($db);

$measurementsRepository = new MeasurementsRepository($db); 

echo "[" . date("Y-m-d H:i:s") . "] Cleanup pornit (păstrez ultimele " . CLEANUP_INTERVAL . "s de date)\n"; 

while(true) { 
    try { 
        $since = date('Y-m-d H:i:s', time() - CLEANUP_INTERVAL);
        $deleted = $measurementsRepository->deleteOlderThan($since); 
        echo "[" . date("Y-m-d H:i:s") . "] Proces de curățare completat (am șters {$deleted} rânduri mai vechi de " . CLEANUP_INTERVAL . "s)\n"; 
    } catch(Exception $e) { 
        fwrite(STDERR,  "[" . date("Y-m-d H:i:s") . "] Eroare cleanup: " . $e->getMessage() . "\n"); 
    }

    sleep(CLEANUP_INTERVAL); 
}
