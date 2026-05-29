<?php

require_once __DIR__ . '/../../Services/PlantService/BasicPlantService.php'; 

class BasicPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function showForm(string $plantId): void { 
        $basicPlantData = $this->plantServiceFacade->getBasicDataByPlantId($plantId); 
        $isUpdate = ($basicPlantData !== null); 

        $formAction = "/power-plants/{$plantId}/basics";

        if ($isUpdate) {
            $formAction = "/power-plants/{$plantId}/basic-update";
        } else {
            $formAction = "/power-plants/{$plantId}/basic-save";
        }

        require_once __DIR__ . '/../../Views/PlantViews/plant-basics-form.view.php'; 
    }

    public function createBasicPlantData(string $plantId) { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true);
        
        if ($dateFormular === null) {
            $dateFormular = [];
        }

        error_log("[DEBUG] Date Formular decodate din JSON:"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->saveBasicData($dateFormular, $plantId); 
            
            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => 'Datele de bază au fost salvate cu succes.'
            ]);

        } catch(Exception $e) { 
            error_log("[ERROR] POST Basic Data: " . $e->getMessage());
            
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Eroare la salvare: ' . $e->getMessage()
            ]); 
        }
    }


    public function updateBasicPlantData(string $plantId) { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));
        try { 
            $this->plantServiceFacade->updateBasicData($dateFormular, $plantId); 
        } catch(Exception $e) { 
            echo "Error at POST for updating the basic plant data: " . htmlspecialchars($e->getMessage()); 
        }
    }
}