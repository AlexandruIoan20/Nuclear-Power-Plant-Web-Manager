<?php 

class PlantController { 
    private PlantService $plantService; 

    public function __construct(PlantService $plantService) { 
        $this->plantService = $plantService; 
    }

    public function showDetailsForm() { 
        $countries = require __DIR__ . '/../Constants/countries.php';
        require __DIR__ . '/../Views/PlantViews/plant-details-form.view.php'; 
    }

    public function showDetailsFormForUpdate($id) { 
        $plant = $this->plantService->findById($id); 

        if(!$plant) { 
            header("Location: /power-plant-list"); 
            exit; 
        }

        require_once __DIR__ . "/../Views/PlantViews/plant-details-update-form.view.php"; 
    }

    public function showPowerPlantsList() { 
        $powerPlants = $this->plantService->getAllPowerPlants(); 
        require __DIR__ . '/../Views/UserPlantViews/plant-list.view.php'; 
    }

    public function handleSavePlantDetails() { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));
        try { 
            $this->plantService->savePlantDetails($_POST); 
            
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
            $this->plantService->updatePlantDetails($_POST, $id); 
            
            header("Location: /power-plant-list"); 
            exit; 
        } catch(Exception $e) { 
            echo "[ERROR] Update the details for a plant: " . htmlspecialchars($e->getMessage()); 
        }
    }

    public function showBasicsForm() { 
        require __DIR__ . '/../Views/PlantViews/plant-basics-form.view.php'; 
    }

    public function showGeologicalForm() { 
        require __DIR__ . '/../Views/PlantViews/plant-geological-form.view.php'; 
    }

    public function showTechnologicalForm() { 
        require __DIR__ . '/../Views/PlantViews/plant-technical-form.view.php'; 
    }
}