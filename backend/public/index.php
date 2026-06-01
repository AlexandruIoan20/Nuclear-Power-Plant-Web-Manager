<?php

session_start([
    'cookie_samesite' => 'Lax',
    'cookie_secure' => false,
    'cookie_httponly' => true,
]);

// Allow specific local origins or any localhost/127.0.0.1 origin (useful for Live Server variations)
$allowedOrigins = [
    'http://localhost:5500',
    'http://localhost:8081',
    'http://127.0.0.1:5500',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    if ($origin === 'null') {
        header('Access-Control-Allow-Origin: null');
    } else {
        $isAllowed = in_array($origin, $allowedOrigins, true) || (bool)preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin);
        if ($isAllowed) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: {$allowedOrigins[0]}");
        }
    }
} else {
    // No origin header (direct requests) — default to first allowed origin
    header("Access-Control-Allow-Origin: {$allowedOrigins[0]}");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Expose-Headers: Location");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/health') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit();
}

require_once __DIR__ . '/../src/Router.php';

require_once __DIR__ . '/../src/Entities/User.php';
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Helpers/AuthHelper.php';

require_once __DIR__ . '/../src/Repositories/PlantRepository/DetailsPlantRepository.php';
require_once __DIR__ . '/../src/Repositories/PlantRepository/BasicPlantRepository.php';
require_once __DIR__ . '/../src/Repositories/PlantRepository/GeologicalPlantRepository.php';
require_once __DIR__ . '/../src/Repositories/PlantRepository/TechnicalPlantRepository.php';
require_once __DIR__ . '/../src/Repositories/FeasibiltyRepository.php';

require_once __DIR__ . '/../src/Services/PlantService/DetailsPlantService.php';
require_once __DIR__ . '/../src/Services/PlantService/BasicPlantService.php';
require_once __DIR__ . '/../src/Services/PlantService/GeologicalPlantService.php';
require_once __DIR__ . '/../src/Services/PlantService/TechnicalPlantService.php';
require_once __DIR__ . '/../src/Services/FeasibilityService/FeasibilityService.php';

require_once __DIR__ . '/../src/Repositories/PlantRepositoryFacade.php';
require_once __DIR__ . '/../src/Services/PlantServiceFacade.php';
require_once __DIR__ . '/../src/Services/FeasibilityService/FeasibilityServiceFactory.php';

require_once __DIR__ . '/../src/Controllers/PlantController/DetailsPlantController.php';
require_once __DIR__ . '/../src/Controllers/PlantController/BasicPlantController.php';
require_once __DIR__ . '/../src/Controllers/PlantController/GeologicalPlantController.php';
require_once __DIR__ . '/../src/Controllers/PlantController/TechnicalPlantController.php';
require_once __DIR__ . '/../src/Controllers/FeasibilitController.php';

$host     = getenv('DB_HOST')     ?: 'db';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'proiect_db';
$username = getenv('DB_USER')     ?: 'admin';
$password = getenv('DB_PASSWORD') ?: 'glorierebeja';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Conexiune la baza de date esuata."]));
}



$plantRepositoryFacade = new PlantRepositoryFacade($pdo);
$plantServiceFacade    = new PlantServiceFacade($plantRepositoryFacade);
$feasibilityService    = FeasibilityServiceFactory::create($pdo, $plantRepositoryFacade);

$userRepository = new UserRepository($pdo);
$userService = new UserService($userRepository);

$router = new Router();

// --- Countries ---
$router->get('/api/countries', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getCountries();
});

// --- Power Plants ---
$router->get('/api/power-plants/list', function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsList(); 
}); 

$router->get('/api/power-plants/map-data', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsMapData();
});

$router->get('/api/power-plants', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsList();
});

$router->post('/api/power-plants', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleSavePlantDetails();
});

// --- Details ---
$router->get('/api/power-plants/{id}/details', function ($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPlantDetails($id);
});

$router->put('/api/power-plants/{id}/details', function ($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleUpdatePlantDetails($id);
});

// --- Basics ---
$router->get('/api/power-plants/{id}/basics', function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->getBasicPlantData($id);
});

$router->post('/api/power-plants/coordinates-preview', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->previewCoordinates();
});

$router->post('/api/power-plants/{id}/basic-save', function($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->createBasicPlantData($id);
});

$router->post('/api/power-plants/{id}/basics', function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->createBasicPlantData($id);
});

$router->put('/api/power-plants/{id}/basics', function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->updateBasicPlantData($id);
});

// --- Geological ---
$router->get('/api/power-plants/{id}/geological', function ($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->getGeologicalPlantData($id);
});

$router->post('/api/power-plants/{id}/geological', function ($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->createGeologicalPlantData($id);
});

$router->put('/api/power-plants/{id}/geological', function ($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->updateGeologicalPlantData($id);
});

// --- Technical ---
$router->get('/api/power-plants/{id}/technical', function ($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->getTechnicalPlantData($id);
});

$router->post('/api/power-plants/{id}/technical', function ($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->createTechnicalPlantData($id);
});

$router->put('/api/power-plants/{id}/technical', function ($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->updateTechnicalPlantData($id);
});

// --- Authentication ---
$router->get('/login', function() use ($userService) {
    (new UserController($userService))->handleLogin();
});

$router->post('/login', function() use ($userService) {
    (new UserController($userService))->handleLogin();
});

$router->get('/register', function() use ($userService) {
    (new UserController($userService))->handleRegister();
});

$router->post('/register', function() use ($userService) {
    (new UserController($userService))->handleRegister();
});

$router->get('/logout', function() use ($userService) {
    (new UserController($userService))->handleLogout();
});

$router->get('/start', function() use ($userService) {
    (new UserController($userService))->showStart();
});

$router->get('/api/user/status', function() use ($userService) {
    (new UserController($userService))->getUserStatus();
});

$router->get('/api/users', function() use ($userService) {
    header('Content-Type: application/json; charset=UTF-8');
    $users = $userService->getAllUsers();

    $payload = array_map(function (User $user) {
        return [
            'id' => $user->getId(),
            'username' => $user->getName(),
            'email' => $user->getEmail(),
        ];
    }, $users);

    echo json_encode(['status' => 'success', 'data' => $payload]);
    exit;
});

$router->get('/dashboard', function() use ($userService) {
    (new UserController($userService))->showDashboard();
});

$router->get('/users', function() use ($userService) {
    (new UserController($userService))->listUsers();
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);