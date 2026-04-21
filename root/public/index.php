<?php

require_once __DIR__ . '/../src/Entities/User.php'; 
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'proiect_db';
$username = getenv('DB_USER') ?: 'admin';
$password = getenv('DB_PASSWORD') ?: 'glorierebeja';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, 
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die("Eroare critică: Nu se poate conecta la baza de date. (" . $e->getMessage() . ")");
}

$userRepository = new UserRepository($pdo);
$userService = new UserService($userRepository);
$userController = new UserController($userService);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {
    case '/':
    case '/register':
        if ($method === 'GET') {
            $userController->showRegisterForm();
        } elseif ($method === 'POST') {
            $userController->handleRegister();
        }
        break;

    case '/users':
        if ($method === 'GET') {
            $userController->listUsers();
        }
        break;

    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}