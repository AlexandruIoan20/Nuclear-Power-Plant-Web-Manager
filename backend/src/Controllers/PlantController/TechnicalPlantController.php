<?php

class TechnicalPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function showForm(string $plantId): void { 
        $technicalPlantData = $this->plantServiceFacade->getTechnicalDataByPlantId($plantId); 
        $isUpdate = ($technicalPlantData !== null); 

        if($isUpdate) { 
            $formAction = "/power-plants/{$plantId}/technical-update"; 
        } else { 
            $formAction = "/power-plants/{$plantId}/technical-save"; 
        }

        require_once __DIR__ . '/../../Entities/ReactorType.php'; 
        require_once __DIR__ . '/../../Entities/CoolingType.php'; 

        require_once __DIR__ . '/../../Views/PlantViews/plant-technical-form.view.php'; 
    }

    public function createTechnicalPlantData(string $plantId): void { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true) ?? [];

        error_log("[DEBUG] Date Formular Technical (Create)"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->saveTechnicalData($dateFormular, $plantId);
            
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Datele tehnice au fost salvate cu succes.'
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