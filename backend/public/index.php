<?php

session_start([
    'cookie_samesite' => 'None', 
    'cookie_secure' => false,    
    'cookie_httponly' => true,
]);

while (ob_get_level()) ob_end_clean();

set_exception_handler(function (Throwable $e) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'error', 'message' => 'Eroare internă.', 'debug' => $e->getMessage()]);
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['status' => 'error', 'message' => 'Eroare fatală.', 'debug' => $error['message']]);
        exit;
    }
});

$allowedOrigins = [
    'http://localhost:5500',
    'http://localhost:8081',
    'http://127.0.0.1:5500',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin) {
    $isAllowed = in_array($origin, $allowedOrigins, true) || (bool)preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin);
    if ($isAllowed) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    } else {
        http_response_code(403);
        die();
    }
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
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

// --- Countries ---
$router->get('/api/countries', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getCountries();
});

// --- Power Plants ---
$router->get('/api/power-plants/list', function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPowerPlantsList(); 
}); 

$router->get('/api/power-plants/pending-approvals', function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->getPendingApprovalsList(); 
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

$router->post('/api/power-plants', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleSavePlantDetails();
});

$router->post('/api/power-plants/coordinates-preview', function () use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->previewCoordinates();
});

// --- Details ---
$router->get('/api/power-plants/{id}/details', function ($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->getPlantDetails($id);
});

$router->patch('/api/power-plants/{id}/status', function ($plantId) use ($plantServiceFacade){ 
    (new DetailsPlantController($plantServiceFacade)->updateStatus($plantId)); 
}); 

$router->put('/api/power-plants/{id}/details', function ($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleUpdatePlantDetails($id);
});

// --- Basics ---
$router->get('/api/power-plants/{id}/basics', function ($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->getBasicPlantData($id);
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
// --- Notifications ---
$router->get('/api/notifications', function () use ($notificationService) {
    (new NotificationController($notificationService))->getNotifications();
});

// --- Alerts ---

$router->post('/api/alerts/receive', function () use ($alertService) {
    (new AlertController($alertService))->receiveAlert();
});

$router->get('/api/alerts/unread', function () use ($alertService) {
    (new AlertController($alertService))->getUnread();
});

$router->put('/api/alerts/{id}/read', function ($id) use ($alertService) {
    (new AlertController($alertService))->markRead($id);
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

// De cautat cod duplicat la refactorizare
$router->put('/api/power-plants/{id}/status', function ($id) use ($plantServiceFacade) {
    (new ApprovalController($plantServiceFacade))->updateStatus($id);
});

// Email
$router->post('/api/send-email', function () use ($emailController) {
    $emailController->handleSendEmail();
});

// RSS 
$router->get('/api/rss/power-plants', function () use ($rssController) {
    $rssController->handleGetPlantsFeed();
});

// --- Feasibility ---
$router->get('/api/power-plants/{id}/feasibility', function ($id) use ($feasibilityService) {
    (new FeasibilityController($feasibilityService))->getLastByPlantId($id);
});

$router->post('/api/power-plants/{id}/feasibility', function ($id) use ($feasibilityService) {
    (new FeasibilityController($feasibilityService))->generate($id);
});

// --- Reactoare ---

$router->get('/api/reactors', function () use ($reactorService) { 
    (new ReactorController($reactorService))->getAllReactors(); 
}); 

$router->get('/api/reactors/{id}', function($id) use ($reactorService) { 
    (new ReactorController($reactorService))->getReactor($id); 
}); 

$router->get('/api/power-plants/{id}/reactors', function($id) use ($reactorService) { 
    (new ReactorController($reactorService))->getReactorsByPlant($id); 
}); 

$router->post('/api/reactors', function() use ($reactorService) { 
    (new ReactorController($reactorService))->createReactor(); 
}); 

$router->put('/api/reactors/{id}', function ($id) use ($reactorService) { 
    (new ReactorController($reactorService))->updateReactor($id); 
});

$router->delete('/api/reactors/{id}', function ($id) use ($reactorService) { 
    (new ReactorController($reactorService))->deleteReactor($id); 
}); 

// --- Dispatch ---
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);
