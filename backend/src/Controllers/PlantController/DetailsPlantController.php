<?php 

require_once __DIR__ . '/../../Dto/PlantDTO.php';
require_once __DIR__ . '/../../Dto/PlantDetailsDTO.php'; 
require_once __DIR__ . '/../../Dto/GetPlantDTO.php';  

class DetailsPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function getCountries() { 
        header('Content-Type: application/json; charset=UTF-8');
        
        $countries = require __DIR__ . '/../../Constants/countries.php';
        
        http_response_code(200);
        echo json_encode($countries);
        exit;
    }

    public function getPlant(string $id) {
        header('Content-Type: application/json; charset=utf-8');
    
        $plant = $this->plantServiceFacade->getCompletePlantProfile($id);
        if(!$plant) { 
            echo json_encode(["status" => "error", "message" => "Centrala nu a fost gasita"]); 
            exit; 
        } 
        $dto = GetPlantDTO::fromServiceArray($plant);
    
        echo json_encode($dto);
    }

    public function getPlantDetails(string $id) { 
        header('Content-Type: application/json; charset=UTF-8');
        
        $plant = $this->plantServiceFacade->getPlantDetailsById($id); 

        if(!$plant) { 
            http_response_code(404); 
            echo json_encode(["status" => "error", "message" => "Centrala nu a fost găsită."]);
            exit; 
        }

        $plantDTO = PlantDetailsDTO::fromEntity($plant);

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $plantDTO]);
        exit;
    }

    public function getPowerPlantsList() { 
        header('Content-Type: application/json; charset=UTF-8');
        $powerPlants = $this->plantServiceFacade->getAllPowerPlants(); 
        
        $dtos = array_map(function($plant) {
            return PlantDTO::fromEntity($plant);
        }, $powerPlants);
        
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $dtos]);
        exit;
    }

    public function getPlantsByStatus() { 
        header("Content-Type: application/json; charset=UTF-8"); 

        $status = $_GET['status'] ?? null;
    
        if (!$status) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Parametrul 'status' lipseste"]);
            return;
        }
    
        $data = ['status' => $status];
    
        error_log("[DEBUG] Date getPlantsByStatus: " . print_r($data, true));
    
        try { 
            $powerPlants = $this->plantServiceFacade->getPlantsByStatus($data); 
            error_log("powerPlants in controller:  " . print_r($powerPlants, true)); 

            $dtos = array_map(function($plant) {
                return PlantDTO::fromEntity($plant);
            }, $powerPlants);
            
            http_response_code(200); 
            echo json_encode(["status" => "success", "data" => $dtos]); 
        } catch(Exception $e) { 
            error_log("[ERROR] GET Plants By Status: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Eroare la cautarea dupa status: " . $e->getMessage()]);
        }
    }
    
    public function getPowerPlantsMapData()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $powerPlants = $this->plantServiceFacade->getAllPowerPlants();

        $dtos = array_map(function ($plant) {
            $dto = PlantDTO::fromEntity($plant);

            $hasCoordinates = $dto->latitude !== null && $dto->longitude !== null;

            return [
                'id' => $dto->id,
                'name' => $dto->name,
                'country' => $dto->country,
                'latitude' => $dto->latitude,
                'longitude' => $dto->longitude,
                'status' => $dto->status,
                'has_coordinates' => $hasCoordinates,
                'coordinates_label' => $hasCoordinates ? number_format($dto->latitude, 6, '.', '') . ', ' . number_format($dto->longitude, 6, '.', '') : 'Fără coordonate',
                'popup_title' => $dto->name ?: 'Centrală',
                'popup_subtitle' => $dto->country ?: 'Țară nespecificată',
                'edit_url' => '/power-plants/update.html?id=' . urlencode($dto->id),
            ];
        }, $powerPlants);

        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $dtos]);
        exit;
    }

    public function handleSavePlantDetails() { 
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        error_log("[DEBUG] Date Formular API Creare: " . print_r($dateFormular, true));
        
        if (empty($dateFormular)) {
            http_response_code(400); 
            echo json_encode(["status" => "error", "message" => "Nu s-au primit date."]);
            exit;
        }

        try { 
            $responseDTO = $this->plantServiceFacade->savePlantDetails($dateFormular); 
            
            http_response_code(201); 
            echo json_encode(["status" => "success", "message" => "Centrala a fost salvată cu succes.", "plantId" => $responseDTO->dataId ]);
            exit; 
        } catch(Exception $e) { 
            error_log("[ERROR] Save Plant: " . $e->getMessage());
            http_response_code(500); 
            echo json_encode(["status" => "error", "message" => "Eroare la salvare: " . $e->getMessage()]);
            exit;
        }
    }

    public function updateStatus(string $plantId) { 
        header('Content-Type: application/json; charset=UTF-8'); 

        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        error_log("[DEBUG] Date Formular API Creare: " . print_r($dateFormular, true));
        
        if (empty($dateFormular)) {
            http_response_code(400); 
            echo json_encode(["status" => "error", "message" => "Nu s-au primit date."]);
            exit;
        }

        try { 
            $verified = $this->plantServiceFacade->updateStatus($dateFormular, $plantId);  
            
            if(!$verified) { 
                echo json_encode([
                    "status" => "error", 
                    "message" => "Eroare la actualizarea statusului"
                ]); 

                exit; 
            } 

            echo json_encode([ 
                "status" => "success", 
                "message" => "Status actualizat cu succes"
            ]); 
        } catch(Exception $e) { 
            error_log("[ERROR] Update Plant: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Eroare la actualizare statusului: " . $e->getMessage()]);
            exit;
        }
    }

    public function handleUpdatePlantDetails(string $id) { 
        header('Content-Type: application/json; charset=UTF-8');
        
        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        error_log("[DEBUG] Date Formular API Update pt ID {$id}: " . print_r($dateFormular, true));

        if (empty($dateFormular)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Date incomplete pentru actualizare."]);
            exit;
        }

        try { 
            $this->plantServiceFacade->updatePlantDetails($dateFormular, $id); 
            
            http_response_code(200); 
            echo json_encode(["status" => "success", "message" => "Detaliile au fost actualizate cu succes."]);
            exit; 
        } catch(Exception $e) { 
            error_log("[ERROR] Update Plant: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Eroare la actualizare: " . $e->getMessage()]);
            exit;
        }
    }

    public function previewCoordinates()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents('php://input');
        $body = json_decode($jsonPayload, true);

        if (!$body || !isset($body['latitude']) || !isset($body['longitude'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Payload invalid: latitude și longitude sunt necesare.']);
            exit;
        }

        $lat = filter_var($body['latitude'], FILTER_VALIDATE_FLOAT);
        $lon = filter_var($body['longitude'], FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lon === false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Coordonate invalide.']);
            exit;
        }

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Coordonate în afara intervalului permis.']);
            exit;
        }

        $latNorm = round($lat, 6);
        $lonNorm = round($lon, 6);

        $label = number_format($latNorm, 6, '.', '') . ', ' . number_format($lonNorm, 6, '.', '');

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => [
                'latitude' => $latNorm,
                'longitude' => $lonNorm,
                'coordinates_label' => $label,
                'message' => 'Coordonate validate.'
            ]
        ]);
        exit;
    }
}