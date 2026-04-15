<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/data-entry.php');
    exit;
}

$plantName = trim($_POST['plant_name'] ?? '');
$country = trim($_POST['country'] ?? '');
$soilType = trim($_POST['soil_type'] ?? '');
$reactorCode = trim($_POST['reactor_code'] ?? '');
$status = trim($_POST['status'] ?? '');
$efficiency = ($_POST['efficiency'] ?? '') !== '' ? (float) $_POST['efficiency'] : null;
$riskLevel = ($_POST['risk_level'] ?? '') !== '' ? (int) $_POST['risk_level'] : null;
$weather = trim($_POST['weather'] ?? '');
$wearLevel = ($_POST['wear_level'] ?? '') !== '' ? (int) $_POST['wear_level'] : null;

$plannedMaintenance = isset($_POST['planned_maintenance']);
$unplannedStop = isset($_POST['unplanned_stop']);

$notes = trim($_POST['notes'] ?? '');
$createdBy = $_SESSION['username'] ?? 'admin';

if ($plantName === '' || $country === '' || $reactorCode === '' || $status === '') {
    http_response_code(422);
    exit('Câmpurile obligatorii lipsesc.');
}

$metricNames = $_POST['metric_name'] ?? [];
$metricValues = $_POST['metric_value'] ?? [];
$metrics = [];

for ($index = 0; $index < count($metricNames); $index++) {
    $name = trim($metricNames[$index] ?? '');
    $value = trim($metricValues[$index] ?? '');

    if ($name !== '') {
        $metrics[] = [
            'name' => $name,
            'value' => $value,
        ];
    }
}

$sql = "INSERT INTO admin_data_entries
(plant_name, reactor_code, country, soil_type, risk_level, efficiency, status, weather, wear_level, planned_maintenance, unplanned_stop, notes, metrics, created_by)
VALUES
(:plant_name, :reactor_code, :country, :soil_type, :risk_level, :efficiency, :status, :weather, :wear_level, :planned_maintenance, :unplanned_stop, :notes, :metrics::jsonb, :created_by)";

$stmt = db()->prepare($sql);

$stmt->bindValue(':plant_name', $plantName);
$stmt->bindValue(':reactor_code', $reactorCode);
$stmt->bindValue(':country', $country);
$stmt->bindValue(':soil_type', $soilType ?: null);
$stmt->bindValue(':risk_level', $riskLevel, $riskLevel === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->bindValue(':efficiency', $efficiency, $efficiency === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
$stmt->bindValue(':status', $status);
$stmt->bindValue(':weather', $weather ?: null);
$stmt->bindValue(':wear_level', $wearLevel, $wearLevel === null ? PDO::PARAM_NULL : PDO::PARAM_INT);


$stmt->bindValue(':planned_maintenance', (bool)$plannedMaintenance, PDO::PARAM_BOOL);
$stmt->bindValue(':unplanned_stop', (bool)$unplannedStop, PDO::PARAM_BOOL);

$stmt->bindValue(':notes', $notes ?: null);
$stmt->bindValue(':metrics', json_encode($metrics, JSON_UNESCAPED_UNICODE));
$stmt->bindValue(':created_by', $createdBy);

$stmt->execute();

header('Location: /admin/entries.php?ok=1');
exit;
