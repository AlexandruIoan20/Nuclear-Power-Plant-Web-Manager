<?php 


class DetailsPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function showDetailsForm() { 
        $countries = require __DIR__ . '/../../Constants/countries.php';
        require __DIR__ . '/../../Views/PlantViews/plant-details-form.view.php'; 
    }

    public function showDetailsFormForUpdate(string $id) { 
        $plant = $this->plantServiceFacade->getPlantDetailsById($id); 

        if(!$plant) { 
            header("Location: /power-plants/list"); 
            exit; 
        }

        $countries = require __DIR__ . '/../../Constants/countries.php';
        require_once __DIR__ . "/../../Views/PlantViews/plant-details-update-form.view.php"; 
    }

    public function showPowerPlantsList() { 
        $powerPlants = $this->plantServiceFacade->getAllPowerPlants(); 
        require __DIR__ . '/../../Views/UserPlantViews/plant-list.view.php'; 
    }

    public function handleSavePlantDetails() { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));
        try { 
            $this->plantServiceFacade->savePlantDetails($_POST); 
            
            header("Location /power-plant-list"); 
            exit; 
        } catch(Exception $e) { 
            echo "Error at POST for the new plant: " . htmlspecialchars($e->getMessage()); 
        }
    }

    public function handleUpdatePlantDetails(string $id) { 
        $dateFormular = $_POST; 
        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));

        try { 
            error_log("[DEBUG] Incearca update la date Formular"); 
            $this->plantServiceFacade->updatePlantDetails($_POST, $id); 
            
            exit; 
        } catch(Exception $e) { 
            echo "[ERROR] Update the details for a plant: " . htmlspecialchars($e->getMessage()); 
        }
    }
}