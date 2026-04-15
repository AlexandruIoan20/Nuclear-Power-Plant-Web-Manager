<?php

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'proiect_db';
        $user = getenv('DB_USER') ?: 'admin';
        $password = getenv('DB_PASSWORD') ?: 'change_me_in_env';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}
