<?php

require_once __DIR__ . '/../Services/BasicPlantService.php'; 

class BasicPlantController { 
    private BasicPlantService $basicPlantService; 

    public function __construct(BasicPlantService $basicPlantService) { 
        $this->basicPlantService = $basicPlantService; 
    }

    public function showForm(string $plantId): void { 
        $basicPlantData = $this->basicPlantService->findByPlantId($plantId); 
        $isUpdate = ($basicPlantData !== null); 

        $formAction = "/power-plants/{$plantId}/basics";

        if ($isUpdate) {
            $formAction = "/power-plants/{$plantId}/basic-update";
        } else {
            $formAction = "/power-plants/{$plantId}/basic-save";
        }

        require_once __DIR__ . '/../Views/PlantViews/plant-basics-form.view.php'; 
    }

    public function createBasicPlantData (string $plantId) { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->basicPlantService->save($dateFormular, $plantId); 
        } catch(Exception $e) { 
            echo "Error at POST for the new plant: " . htmlspecialchars($e->getMessage()); 
        }
    }
}