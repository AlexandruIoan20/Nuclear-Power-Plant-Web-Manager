<?php 

require_once __DIR__ . '/../../Dto/PlantDTO.php';
require_once __DIR__ . '/../../Dto/PlantDetailsDTO.php'; 

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
}