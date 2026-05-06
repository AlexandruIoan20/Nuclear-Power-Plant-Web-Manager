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
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular Geological"); 
        error_log(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->saveGeologicalData($dateFormular, $plantId); 
            header("Location: /power-plants/{$plantId}/geological");
            exit;
        } catch(Exception $e) { 
            echo "Error at POST for the new geological plant data: " . htmlspecialchars($e->getMessage()); 
        }
    }

    public function updateGeologicalPlantData(string $plantId): void { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular Geological Update"); 
        error_log(print_r($dateFormular, true));
        
        try { 
            $this->plantServiceFacade->updateGeologicalData($dateFormular, $plantId); 
            header("Location: /power-plants/{$plantId}/geological");
            exit;
        } catch(Exception $e) { 
            echo "Error at POST for updating the geological plant data: " . htmlspecialchars($e->getMessage()); 
        }
    }
}