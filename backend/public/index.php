<?php

// Session cookie settings: for local dev we use 'Lax' to avoid Secure requirement
// with SameSite=None which modern browsers block on non-HTTPS origins.
session_start([
    'cookie_samesite' => 'Lax',
    'cookie_secure' => false,
    'cookie_httponly' => true,
]);

// Allow specific local origins (needed for fetch from frontend dev servers)
$allowedOrigins = [
    'http://localhost:5500',
    'http://localhost:8081',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // fallback to first allowed origin to be explicit when none provided
    header("Access-Control-Allow-Origin: {$allowedOrigins[0]}");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
// Allow frontend to read the redirect Location header when following login redirects
header("Access-Control-Expose-Headers: Location");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lightweight healthcheck for smoke tests that doesn't require DB
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

require_once __DIR__ . '/../src/Services/PlantService/DetailsPlantService.php'; 
require_once __DIR__ . '/../src/Services/PlantService/BasicPlantService.php'; 
require_once __DIR__ . '/../src/Services/PlantService/GeologicalPlantService.php'; 
require_once __DIR__ . '/../src/Services/PlantService/TechnicalPlantService.php'; 

require_once __DIR__ . '/../src/Repositories/PlantRepositoryFacade.php'; 
require_once __DIR__ . '/../src/Services/PlantServiceFacade.php'; 

require_once __DIR__ . '/../src/Controllers/PlantController/DetailsPlantController.php'; 
require_once __DIR__ . '/../src/Controllers/PlantController/BasicPlantController.php'; 
require_once __DIR__ . '/../src/Controllers/PlantController/GeologicalPlantController.php'; 
require_once __DIR__ . '/../src/Controllers/PlantController/TechnicalPlantController.php'; 

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
    die(json_encode(["status" => "error", "message" => "Conexiune la baza de date esuata."]));
}

$adminEmail = 'admin@nuclear.ro';
$adminPasswordHash = '$2y$12$pLgjMWjlhKbYoAAvRByCMuLnj3l5JlYl03QHgkgZwHci6c8Q59U.i';

$adminInsert = $pdo->prepare(
    'INSERT INTO users (username, first_name, last_name, email, password_hash, role) VALUES (:username, :first_name, :last_name, :email, :password_hash, :role) ON CONFLICT (email) DO UPDATE SET username = EXCLUDED.username, first_name = EXCLUDED.first_name, last_name = EXCLUDED.last_name, password_hash = EXCLUDED.password_hash, role = EXCLUDED.role'
);
$adminInsert->execute([
    'username' => 'admin',
    'first_name' => 'Admin',
    'last_name' => 'System',
    'email' => $adminEmail,
    'password_hash' => $adminPasswordHash,
    'role' => 'ADMIN',
]);

$plantRepositoryFacade = new PlantRepositoryFacade($pdo); 
$plantServiceFacade = new PlantServiceFacade($plantRepositoryFacade); 

$userRepository = new UserRepository($pdo);
$userService = new UserService($userRepository);

$router = new Router();

$router->get('/api/power-plants/list', function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsList(); 
}); 

$router->get('/api/countries', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getCountries();
});

$router->post('/api/power-plants/create', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleSavePlantDetails();
});

$router->get('/api/power-plants/{id}/details', function($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPlantDetails($id);
});

$router->post('/api/power-plants/{id}/details-update', function($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleUpdatePlantDetails($id);
});

//$router->get('/api/power-plants/{id}/basics', function($id) use ($plantServiceFacade) {
//    (new BasicPlantController($plantServiceFacade))->getBasicPlantData($id);
//});

$router->post('/api/power-plants/{id}/basic-update', function($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->updateBasicPlantData($id);
});

$router->post('/api/power-plants/{id}/basic-save', function($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->createBasicPlantData($id);
});

//$router->get('/api/power-plants/{id}/geological', function($id) use ($plantServiceFacade) {
//    (new GeologicalPlantController($plantServiceFacade))->getGeologicalPlantData($id);
//});

$router->post('/api/power-plants/{id}/geological-save', function($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->createGeologicalPlantData($id);
});

$router->post('/api/power-plants/{id}/geological-update', function($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->updateGeologicalPlantData($id);
});

//$router->get('/api/power-plants/{id}/technical', function($id) use ($plantServiceFacade) {
//    (new TechnicalPlantController($plantServiceFacade))->getTechnicalPlantData($id);
//});

$router->post('/api/power-plants/{id}/technical-save', function($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->createTechnicalPlantData($id);
});

$router->post('/api/power-plants/{id}/technical-update', function($id) use ($plantServiceFacade) {
     (new TechnicalPlantController($plantServiceFacade))->updateTechnicalPlantData($id);
});

// Authentication Routes
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

$router->get('/dashboard', function() use ($userService) {
    (new UserController($userService))->showDashboard();
});

$router->get('/users', function() use ($userService) {
    (new UserController($userService))->listUsers();
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);