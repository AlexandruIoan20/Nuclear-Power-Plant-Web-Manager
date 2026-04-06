<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Test PostgreSQL</title>
</head>
<body>

    <h1>Verificare sistem...</h1>

    <?php
    // Datele de conectare (trebuie să fie identice cu cele din docker-compose.yml)
    // ATENȚIE: La "host" trecem numele serviciului din docker-compose, adică "db"
    $host = 'db';
    $dbname = 'proiect_db';
    $user = 'admin';
    $password = 'parola_secreta';

    try {
        // Încercăm să ne conectăm la PostgreSQL
        $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        echo "<h2 style='color: green;'>✅ Conexiune la PostgreSQL reușită!</h2>";
        
    } catch (PDOException $e) {
        echo "<h2 style='color: red;'>❌ Eroare de conectare:</h2>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
    ?>

</body>
</html>