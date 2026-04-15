<?php
require_once __DIR__ . '/../config/database.php';

$rows = db()->query('SELECT id, plant_name, reactor_code, status, efficiency, risk_level, created_by, created_at FROM admin_data_entries ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapoarte Admin</title>
    <link rel="stylesheet" href="/assets/css/admin-form.css">
</head>
<body>
<div class="container">
    <h1>Rapoarte introduse</h1>
    <a href="/admin/data-entry.php" class="link-btn">+ Raport nou</a>

    <?php if (isset($_GET['ok'])): ?>
        <p class="ok">Raport salvat cu succes.</p>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Centrală</th>
                <th>Reactor</th>
                <th>Status</th>
                <th>Eficiență</th>
                <th>Risc</th>
                <th>Creat de</th>
                <th>Data</th>
                <th>Acțiuni</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int) $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['plant_name']) ?></td>
                    <td><?= htmlspecialchars($row['reactor_code']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars((string) $row['efficiency']) ?></td>
                    <td><?= htmlspecialchars((string) $row['risk_level']) ?></td>
                    <td><?= htmlspecialchars($row['created_by']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><a href="/admin/view-entry.php?id=<?= (int) $row['id'] ?>">Detalii</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
