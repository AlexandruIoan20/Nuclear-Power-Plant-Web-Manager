<?php
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    exit('ID invalid.');
}

$statement = db()->prepare('SELECT * FROM admin_data_entries WHERE id = :id');
$statement->execute([':id' => $id]);
$row = $statement->fetch();

if (!$row) {
    http_response_code(404);
    exit('Raportul nu a fost găsit.');
}

$metrics = json_decode($row['metrics'] ?? '[]', true);
if (!is_array($metrics)) {
    $metrics = [];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalii raport #<?= (int) $row['id'] ?></title>
    <link rel="stylesheet" href="/assets/css/admin-form.css">
</head>
<body>
<div class="container">
    <h1>Detalii raport #<?= (int) $row['id'] ?></h1>
    <a href="/admin/entries.php" class="link-btn">Înapoi la listă</a>

    <div class="card">
        <p><strong>Tip:</strong> <?= htmlspecialchars($row['entry_type']) ?></p>
        <p><strong>Centrală:</strong> <?= htmlspecialchars($row['plant_name']) ?></p>
        <p><strong>Reactor:</strong> <?= htmlspecialchars($row['reactor_code']) ?></p>
        <p><strong>Țară:</strong> <?= htmlspecialchars($row['country']) ?></p>
        <p><strong>Tip sol:</strong> <?= htmlspecialchars((string) $row['soil_type']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></p>
        <p><strong>Eficiență:</strong> <?= htmlspecialchars((string) $row['efficiency']) ?>%</p>
        <p><strong>Nivel risc:</strong> <?= htmlspecialchars((string) $row['risk_level']) ?></p>
        <p><strong>Meteo:</strong> <?= htmlspecialchars((string) $row['weather']) ?></p>
        <p><strong>Nivel uzură:</strong> <?= htmlspecialchars((string) $row['wear_level']) ?></p>
        <p><strong>Oprire planificată:</strong> <?= $row['planned_maintenance'] ? 'Da' : 'Nu' ?></p>
        <p><strong>Oprire neplanificată:</strong> <?= $row['unplanned_stop'] ? 'Da' : 'Nu' ?></p>
        <p><strong>Note:</strong><br><?= nl2br(htmlspecialchars((string) $row['notes'])) ?></p>
        <p><strong>Creat de:</strong> <?= htmlspecialchars($row['created_by']) ?></p>
        <p><strong>Creat la:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
    </div>

    <div class="card">
        <h2>Metrici suplimentare</h2>
        <?php if (count($metrics) === 0): ?>
            <p>Nu există metrici adăugate.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($metrics as $metric): ?>
                    <li>
                        <strong><?= htmlspecialchars((string) ($metric['name'] ?? '')) ?>:</strong>
                        <?= htmlspecialchars((string) ($metric['value'] ?? '')) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
