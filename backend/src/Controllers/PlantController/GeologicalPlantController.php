<?php

require_once __DIR__ . '/../../Services/PlantServiceFacade.php'; 
require_once __DIR__ . '/../../Dto/GeologicalPlantDataDTO.php'; 

class GeologicalPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function getGeologicalPlantData(string $plantId): void { 
        header('Content-Type: application/json; charset=UTF-8'); 

        $geologicalData = $this->plantServiceFacade->getGeologicalDataByPlantId($plantId); 

        if (!$geologicalData) { 
            http_response_code(404); 
            echo json_encode(["status" => "error", "message" => "Datele geografice nu au fost gasite."]);
            exit;
        }

        $geologicalPlantDataDTO = GeologicalPlantDataDTO::fromEntity($geologicalData); 

        http_response_code(200); 
        echo json_encode(["status" => "success", "data" => $geologicalPlantDataDTO]); 
        exit;
    }


    public function createGeologicalPlantData(string $plantId): void { 
        header('Content-Type: application/json; charset=UTF-8');
        error_log('=== CREATE START === plantId: ' . $plantId);

        
        $jsonPayload = file_get_contents('php://input');
        error_log('=== raw payload: ' . $jsonPayload);
        
        $dateFormular = json_decode($jsonPayload, true) ?? [];
        error_log('=== dateFormular: ' . print_r($dateFormular, true));
        
        try {
            error_log('=== inainte de saveGeologicalData');
            $responseDTO = $this->plantServiceFacade->saveGeologicalData($dateFormular, $plantId);
            error_log('=== dupa saveGeologicalData, responseDTO: ' . print_r($responseDTO, true));
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Datele geologice au fost salvate cu succes.',
                "plantId" => $plantId,
                "geologicalId" => $responseDTO->dataId
            ]);
            error_log('=== CREATE DONE ===');
            
        } catch(Exception $e) {
            error_log('[ERROR] mesaj: ' . $e->getMessage());
            error_log('[ERROR] fisier: ' . $e->getFile() . ' linia: ' . $e->getLine());
            error_log('[ERROR] trace: ' . $e->getTraceAsString());
            
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Eroare: ' . $e->getMessage()
            ]);
        }
    }

    public function updateGeologicalPlantData(string $plantId): void { 
        header('Content-Type: application/json; charset=UTF-8');
        
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true) ?? [];

        error_log("[DEBUG] Date Formular Geological (Update)"); 
        error_log(print_r($dateFormular, true));
        
        try { 
            $this->plantServiceFacade->updateGeologicalData($dateFormular, $plantId); 
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Datele geologice au fost actualizate cu succes.'
            ]);
            exit;
        } catch(Exception $e) { 
            error_log("[ERROR] POST Geo Data Update: " . $e->getMessage());
            
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Eroare la actualizarea datelor geologice: ' . $e->getMessage()
            ]); 
            exit;
        }
    }
}