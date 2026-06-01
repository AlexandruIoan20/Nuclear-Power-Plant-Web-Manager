<?php

require_once __DIR__ . '/../../Services/PlantServiceFacade.php'; 
require_once __DIR__ . '/../../Dto/TechnicalPlantDataDTO.php'; 

class TechnicalPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function getTechnicalPlantData(string $plantId) { 
        header('Content-Type: application/json; charset=UTF-8'); 

        $technicalPlantData = $this->plantServiceFacade->getTechnicalDataByPlantId($plantId); 

        if(!$technicalPlantData) { 
            http_response_code(404); 
            echo json_encode(["status" => "error", "message" => "Datele tehnice ale centralei nu au fost gasite"]); 
            exit; 
        }

        $technicalPlantDataDTO = TechnicalPlantDataDTO::fromEntity($technicalPlantData); 

        http_response_code(200); 
        echo json_encode(["status" => "success", "data" => $technicalPlantDataDTO]); 
        exit; 
    }

    public function createTechnicalPlantData(string $plantId): void { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true) ?? [];

        error_log("[DEBUG] Date Formular Technical (Create)"); 
        error_log(print_r($dateFormular, true));

        try { 
            $responseDTO = $this->plantServiceFacade->saveTechnicalData($dateFormular, $plantId);
            
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Datele tehnice au fost salvate cu succes.', 
                'plantId' => $plantId, 
                'technicalId' => $responseDTO->dataId
            ]);
        } catch(Exception $e) { 
            error_log("[ERROR] POST Tech Data Create: " . $e->getMessage()); 
        
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la salvarea datelor tehnice: ' . $e->getMessage()
            ]);
        }
    }

    public function updateTechnicalPlantData(string $plantId): void { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true) ?? [];

        error_log("[DEBUG] Date Formular Technical (Update)"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->updateTechnicalData($dateFormular, $plantId); 
            
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Datele tehnice au fost actualizate cu succes.'
            ]);
        } catch(Exception $e) { 
            error_log("[ERROR] POST Tech Data Update: " . $e->getMessage()); 
            
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la actualizarea datelor tehnice: ' . $e->getMessage()
            ]);
        }
    }
}