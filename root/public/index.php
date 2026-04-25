<?php

require_once __DIR__ . '/../src/Entities/User.php'; 
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

require_once __DIR__ . '/../src/Controllers/PlantController.php'; 
require_once __DIR__ .  '/../src/Services/PlantService.php'; 
require_once __DIR__ . '/../src/Repositories/PlantRepository.php'; 


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

$plantRepository = new PlantRepository($pdo); 
$plantService = new PlantService($plantRepository); 
$plantController = new PlantController($plantService); 

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/' || $uri === '/register') {
    if ($method === 'GET') {
        $userController->showRegisterForm();
    } elseif ($method === 'POST') {
        $userController->handleRegister();
    }
} 

elseif ($uri === '/users') {
    if ($method === 'GET') {
        $userController->listUsers();
    }
}

elseif ($uri === '/power-plant-create') { 
    if($method === 'GET') { 
        $plantController->showDetailsForm(); 
    } else if($method === 'POST') { 
        $plantController->handleSavePlantDetails(); 
    }
}

elseif ($uri === '/power-plant-list') { 
    if($method === 'GET') { 
        $plantController->showPowerPlantsList(); 
    } 
}

elseif (preg_match('#^/power-plants/([0-9a-fA-F\-]{36})/details$#', $uri, $matches)) {
    $plantUuid = $matches[1]; 

    if ($method === 'GET') { 
        $plantController->showDetailsFormForUpdate($plantUuid);
    } else if($method === 'POST') { 
        $plantController->handleUpdatePlantDetails($plantUuid);
    }
}

elseif (preg_match('#^/power-plants/([0-9a-fA-F\-]{36})/basics$#', $uri, $matches)) {
    $plantUuid = $matches[1]; 

    if ($method === 'GET') { 
        $plantController->showBasicsForm($plantUuid);
    }
}

elseif (preg_match('#^/power-plants/([0-9a-fA-F\-]{36})/geological$#', $uri, $matches)) {
    $plantUuid = $matches[1];

    if ($method === 'GET') { 
        $plantController->showGeologicalForm($plantUuid);
    }
}

else {
    http_response_code(404);
    echo "404 Not Found";
}