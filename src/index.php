<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuclear Power Plant Web Manager</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f8fb; margin: 0; padding: 24px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: #fff; border-radius: 10px; padding: 18px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; text-decoration: none; border-radius: 6px; padding: 10px 14px; color: #fff; }
        .btn-primary { background: #0b5ed7; }
        .btn-success { background: #198754; }
    </style>
</head>
<body>

    <div class="container">
    <h1>Nuclear Power Plant Web Manager</h1>
    <p>Punct de intrare pentru modulele aplicației.</p>

    <div class="card">
        <h2>Verificare sistem</h2>

    <?php
    $host = 'db';
    $dbname = 'proiect_db';
    $user = 'admin';
    $password = 'parola_secreta';

    try {
        $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        echo "<p style='color: green; font-weight: 700;'>✅ Conexiune la PostgreSQL reușită!</p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red; font-weight: 700;'>❌ Eroare de conectare:</p>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
    ?>
    </div>

    <div class="card">
        <h2>Modul admin - input date</h2>
        <p>Formular structurat pe secțiuni pentru introducerea rapoartelor reactorului.</p>
        <div class="actions">
            <a class="btn btn-primary" href="/admin/data-entry.php">Deschide formularul</a>
            <a class="btn btn-success" href="/admin/entries.php">Vezi rapoarte salvate</a>
        </div>
    </div>

    </div>

</body>
</html>