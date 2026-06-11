<?php

require_once __DIR__ . '/../config/scripts.php';
require_once __DIR__ . '/../src/Repositories/MeasurementsRepository.php';

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

$measurementsRepository = new MeasurementsRepository($db);

echo "[" . date("Y-m-d H:i:s") . "] Agregator pornit (interval: " . AGGREGATOR_INTERVAL . "s)\n";

while (true) {
    try {
        $result = $measurementsRepository->aggregateHourly(intervalSeconds: AGGREGATOR_INTERVAL);
        $count = $result['rows'];
        if ($count > 0) {
            echo "[" . date("Y-m-d H:i:s") . "] Proces de agregare completat (" . $result['from'] . " – " . $result['to'] . ")\n";
        } else {
            echo "[" . date("Y-m-d H:i:s") . "] Nicio oră nouă de agregat (deja actualizat)\n";
        }
    } catch (Exception $e) {
        fwrite(STDERR, "[" . date("Y-m-d H:i:s") . "] Eroare agregare: " . $e->getMessage() . "\n");
    }

    sleep(AGGREGATOR_INTERVAL);
}
