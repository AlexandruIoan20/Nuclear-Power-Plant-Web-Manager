<?php

class GeologicalPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function showForm(string $plantId): void { 
        $geologicalPlantData = $this->plantServiceFacade->getGeologicalDataByPlantId($plantId); 
        $isUpdate = ($geologicalPlantData !== null); 

        if ($isUpdate) {
            $formAction = "/power-plants/{$plantId}/geological-update";
        } else {
            $formAction = "/power-plants/{$plantId}/geological-save";
        }

        require_once __DIR__ . '/../../Views/PlantViews/plant-geological-form.view.php'; 
    }

    public function createGeologicalPlantData(string $plantId): void { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true) ?? [];

        error_log("[DEBUG] Date Formular Geological (Create)"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->saveGeologicalData($dateFormular, $plantId); 
            
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Datele geologice au fost salvate cu succes.'
            ]);
        } catch(Exception $e) { 
            error_log("[ERROR] POST Geo Data Create: " . $e->getMessage());
            
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la salvarea datelor geologice: ' . $e->getMessage()
            ]); 
        }
    }

    public function updateGeologicalPlantData(string $plantId): void { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true) ?? [];

        error_log("[DEBUG] Date Formular Geological (Update)"); 
        error_log(print_r($dateFormular, true));
        
        try { 
            $this->plantServiceFacade->updateGeologicalData($dateFormular, $plantId); 
            
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => 'Datele geologice au fost actualizate cu succes.'
            ]);
        } catch(Exception $e) { 
            error_log("[ERROR] POST Geo Data Update: " . $e->getMessage());
            
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Eroare la actualizarea datelor geologice: ' . $e->getMessage()
            ]); 
        }
    }
}