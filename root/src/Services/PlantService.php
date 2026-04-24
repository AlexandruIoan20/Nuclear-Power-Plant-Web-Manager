<?php

class PlantService { 
    private PlantRepository $plantRepository; 

    public function __construct(PlantRepository $plantRepository) { 
        $this->plantRepository = $plantRepository; 
    }

    public function savePlantDetails(array $data) { 
        // to do: from POST request to valid Plant.php entity
    }
}