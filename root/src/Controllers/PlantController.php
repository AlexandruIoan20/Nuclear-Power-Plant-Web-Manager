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

    public function handleSavePlantDetails() { 
        $dateFormular = $_POST; 

        error_log("[DEBUG] Date Formular"); 
        error_log(print_r($dateFormular, true));
        try { 
            $this->plantService->savePlantDetails($_POST); 
            
            header("Location /power-plant-list"); 
            exit; 
        } catch(Exception $e) { 
            echo "Error at register: " . htmlspecialchars($e->getMessage()); 
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