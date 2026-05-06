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

    public function createBasicPlantData (string $plantId) { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->saveBasicData($dateFormular, $plantId); 
        } catch(Exception $e) { 
            echo "Error at POST for the new basic plant data: " . htmlspecialchars($e->getMessage()); 
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