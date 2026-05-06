<?php


header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../src/Router.php'; 

require_once __DIR__ . '/../src/Entities/User.php'; 
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/UserService.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

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
    die("Eroare critică: Nu se poate conecta la baza de date. (" . $e->getMessage() . ")");
}

$plantRepositoryFacade = new PlantRepositoryFacade($pdo); 
$plantServiceFacade = new PlantServiceFacade($plantRepositoryFacade); 


$router = new Router();

$router->get('/power-plants/list', function() use ($plantServiceFacade) { 
    (new DetailsPlantController($plantServiceFacade))->showPowerPlantsList(); 
}); 

$router->get('/power-plants/create', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->showDetailsForm();
});

$router->post('/power-plants/create', function() use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleSavePlantDetails();
});

$router->get('/power-plants/{id}/details', function($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->showDetailsFormForUpdate($id);
});

$router->post('/power-plants/{id}/details', function($id) use ($plantServiceFacade) {
    (new DetailsPlantController($plantServiceFacade))->handleUpdatePlantDetails($id);
});

$router->get('/power-plants/{id}/basics', function($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->showForm($id);
});

$router->post('/power-plants/{id}/basic-update', function($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->updateBasicPlantData($id);
});

$router->post('/power-plants/{id}/basic-save', function($id) use ($plantServiceFacade) {
    (new BasicPlantController($plantServiceFacade))->createBasicPlantData($id);
});

$router->get('/power-plants/{id}/geological', function($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->showForm($id);
});

$router->post('/power-plants/{id}/geological-save', function($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->createGeologicalPlantData($id);
});

$router->post('/power-plants/{id}/geological-update', function($id) use ($plantServiceFacade) {
    (new GeologicalPlantController($plantServiceFacade))->updateGeologicalPlantData($id);
});

$router->get('/power-plants/{id}/technical', function($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->showForm($id);
});

$router->post('/power-plants/{id}/technical-save', function($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->createTechnicalPlantData($id);
});

$router->post('/power-plants/{id}/technical-update', function($id) use ($plantServiceFacade) {
    (new TechnicalPlantController($plantServiceFacade))->updateTechnicalPlantData($id);
});


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);