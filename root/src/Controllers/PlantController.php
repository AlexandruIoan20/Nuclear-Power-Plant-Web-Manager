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

    // TO DO: parse the command over in index.php
    public function handleSavePlantDetails() { 
        try { 
            $this->plantService->savePlantDetails($_POST); 
            
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