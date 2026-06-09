<?php

require_once __DIR__ . '/../src/Constants/urls.php';

ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);

session_start([
    'cookie_samesite' => 'Lax', 
    'cookie_secure' => false,    
    'cookie_httponly' => true,
]);

if(empty($_SESSION['csrf_token'])) { 
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}

if(!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'OPTIONS', 'HEAD'])) { 
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''; 
    if(empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) { 
        http_response_code(403); 
        header('Content-Type: application/json'); 
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token' ]); 
        exit; 
    }
} 

$allowedOrigins = [
    URL_FRONTEND,
    URL_BACKEND,
    'http://127.0.0.1:5500',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin) {
    $isAllowed = in_array($origin, $allowedOrigins, true);
    if ($isAllowed) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    } else {
        http_response_code(403);
        die();
    }
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
header("Access-Control-Expose-Headers: Location");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// For testing only
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/health') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/../src/Services/EmailService.php';
    try {
        $emailService = new EmailService();
        $testData = [
            'to_email' => 'test@nuc.nuc',
            'subject' => 'deschidemadacapoti',
            'message' => 'daca vezi asta inseamna ca paul le are cu programarea.'
        ];
        $emailService->sendAlert($testData);
        echo json_encode(['status' => 'ok', 'mail_system' => 'Email sent successfully to Mailtrap!']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Mail system failed: ' . $e->getMessage()]);
    }
    exit();
}

require_once __DIR__ . '/../src/Constants/urls.php';
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
require_once __DIR__ . '/../src/Repositories/ReactorRepository.php'; 

require_once __DIR__ . '/../src/Services/PlantService/DetailsPlantService.php';
require_once __DIR__ . '/../src/Services/PlantService/BasicPlantService.php';
require_once __DIR__ . '/../src/Services/PlantService/GeologicalPlantService.php';
require_once __DIR__ . '/../src/Services/PlantService/TechnicalPlantService.php';
require_once __DIR__ . '/../src/Services/FeasibilityService/FeasibilityService.php';

require_once __DIR__ . '/../src/Repositories/PlantRepositoryFacade.php';
require_once __DIR__ . '/../src/Services/PlantServiceFacade.php';
require_once __DIR__ . '/../src/Services/FeasibilityService/FeasibilityServiceFactory.php';
require_once __DIR__ . '/../src/Services/ReactorService.php'; 

require_once __DIR__ . '/../src/Controllers/PlantController/DetailsPlantController.php';
require_once __DIR__ . '/../src/Controllers/PlantController/BasicPlantController.php';
require_once __DIR__ . '/../src/Controllers/PlantController/GeologicalPlantController.php';
require_once __DIR__ . '/../src/Controllers/PlantController/TechnicalPlantController.php';
require_once __DIR__ . '/../src/Controllers/FeasibilitController.php';
require_once __DIR__ . '/../src/Controllers/ApprovalController.php';
require_once __DIR__ . '/../src/Controllers/ReactorController.php'; 

require_once __DIR__ . '/../src/Services/EmailService.php';
require_once __DIR__ . '/../src/Controllers/EmailController.php';

require_once __DIR__ . '/../src/Services/RssService.php';
require_once __DIR__ . '/../src/Controllers/RssController.php';


require_once __DIR__ . '/../src/Repositories/AlertRepository.php';
require_once __DIR__ . '/../src/Services/AlertService.php';
require_once __DIR__ . '/../src/Controllers/AlertController.php';

require_once __DIR__ . '/../src/Services/NotificationService.php';
require_once __DIR__ . '/../src/Controllers/NotificationController.php';



// Database configuration
$host     = getenv('DB_HOST')     ?: 'db';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'proiect_db';
$username = getenv('DB_USER')     ?: 'admin';
$password = getenv('DB_PASSWORD') ?: 'glorierebeja';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

$plantRepositoryFacade = new PlantRepositoryFacade($pdo);
$plantServiceFacade = new PlantServiceFacade($plantRepositoryFacade);
$feasibilityService = FeasibilityServiceFactory::create($pdo, $plantRepositoryFacade);

$emailService = new EmailService();
$emailController = new EmailController($emailService);

$alertRepository = new AlertRepository($pdo);
$alertService = new AlertService($alertRepository, $emailService);

$rssService = new RssService($plantServiceFacade);
$rssController = new RssController($rssService);    

$userRepository = new UserRepository($pdo);
$userService = new UserService($userRepository);

$notificationService = new NotificationService($plantServiceFacade, $alertService);
$reactorRepository = new ReactorRepository($pdo); 
$reactorService = new ReactorService($reactorRepository);

$router = new Router();

// ──────────────────────────────────────────────
// Wrapper de autentificare
// ──────────────────────────────────────────────
function auth(?string $role, callable $handler): callable {
    return function (...$args) use ($role, $handler) {
        /* 
        if (!AuthHelper::isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'Neautorizat: autentificare necesară']);
            exit;
        }
        if ($role !== null) {
            AuthHelper::requireRole($role);
        }
            */
        $handler(...$args);
    };
}

// ──────────────────────────────────────────────
// RUTE PUBLICE (fără autentificare)
// ──────────────────────────────────────────────

$router->get('/api/csrf-token', function() { 
    header('Content-Type: application/json'); 
    echo json_encode(['csrf_token' => $_SESSION['csrf_token'] ?? '']); 
}); 

$router->get('/api/countries', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getCountries();
});

$router->get('/api/power-plants/list', function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsList(); 
}); 

$router->get('/api/power-plants/map-data', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsMapData();
});

$router->get('/api/power-plants', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsList();
});

$router->get('/api/power-plants/filter', function () use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPlantsByStatus(); 
}); 

$router->get('/api/power-plants/{id}', function ($id) use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPlant($id); 
}); 

$router->get('/api/power-plants/{id}/details', function ($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPlantDetails($id);
});

$router->get('/api/rss/power-plants', function () use ($rssController) {
    $rssController->handleGetPlantsFeed();
});

// Validare coordonate — nu modifică date, doar validează
$router->post('/api/power-plants/coordinates-preview', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->previewCoordinates();
});

// ──────────────────────────────────────────────
// RUTE PROTEJATE (orice utilizator autentificat)
// ──────────────────────────────────────────────

$router->post('/api/power-plants', auth(null, function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleSavePlantDetails();
}));

$router->patch('/api/power-plants/{id}/status', auth(null, function ($plantId) use ($plantServiceFacade){ 
    (new DetailsPlantController($plantServiceFacade)->updateStatus($plantId)); 
})); 

$router->put('/api/power-plants/{id}/details', auth(null, function ($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleUpdatePlantDetails($id);
}));

$router->get('/api/power-plants/{id}/basics', auth(null, function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->getBasicPlantData($id);
}));

$router->post('/api/power-plants/{id}/basics', auth(null, function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->createBasicPlantData($id);
}));

$router->put('/api/power-plants/{id}/basics', auth(null, function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->updateBasicPlantData($id);
}));

$router->get('/api/power-plants/{id}/geological', auth(null, function ($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->getGeologicalPlantData($id);
}));

$router->post('/api/power-plants/{id}/geological', auth(null, function ($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->createGeologicalPlantData($id);
}));

$router->put('/api/power-plants/{id}/geological', auth(null, function ($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->updateGeologicalPlantData($id);
}));

$router->get('/api/power-plants/{id}/technical', auth(null, function ($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->getTechnicalPlantData($id);
}));

$router->post('/api/power-plants/{id}/technical', auth(null, function ($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->createTechnicalPlantData($id);
}));

$router->put('/api/power-plants/{id}/technical', auth(null, function ($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->updateTechnicalPlantData($id);
}));

$router->get('/api/notifications', auth(null, function () use ($notificationService) {
    (new NotificationController($notificationService))->getNotifications();
}));

$router->post('/api/alerts/receive', auth(null, function () use ($alertService) {
    (new AlertController($alertService))->receiveAlert();
}));

$router->get('/api/alerts/unread', auth(null, function () use ($alertService) {
    (new AlertController($alertService))->getUnread();
}));

$router->put('/api/alerts/{id}/read', auth(null, function ($id) use ($alertService) {
    (new AlertController($alertService))->markRead($id);
}));

$router->get('/api/power-plants/pending-approvals', auth(null, function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPendingApprovalsList(); 
}));

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

$router->get('/api/user/status', function() use ($userService) {
    (new UserController($userService))->getUserStatus();
});

// --- Dispatch ---
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);
