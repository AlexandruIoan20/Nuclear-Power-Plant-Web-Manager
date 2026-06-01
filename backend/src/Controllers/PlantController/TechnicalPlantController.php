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
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular Technical Save"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->saveTechnicalData($dateFormular, $plantId);
        } catch(Exception $e) { 
            echo "Error at POST for the new technical plant data " . htmlspecialchars($e->getMessage()); 
        }
    }

    public function updateTechnicalPlantData(string $plantId): void { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular Technical Update"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->updateTechnicalData($dateFormular, $plantId); 
        } catch(Exception $e) { 
            echo "Error at POST for updating the technical plant data" . htmlspecialchars($e->getMessage()); 
        }
    }
}