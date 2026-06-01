<?php

require_once __DIR__ . '/../../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../Entities/Plant.php'; 

class DetailsPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function savePlantDetails(array $data) { 
        $name = $data['name'] ?? ''; 
        $name = ($name !== '') ? $name : null; 

        $country = $data['country'] ?? ''; 
        $country = ($country !== '') ? $country : null; 

        $latitude = $data['latitude'] ?? ''; 
        $latitude = ($latitude !== '') ? $latitude : null; 

        $longitude = $data['longitude'] ?? ''; 
        $longitude = ($longitude !== '') ? $longitude : null;

        $status = PlantStatus::DRAFT; 
        $id = generateUUID(); 

        $plant = new Plant($country, $id, $name, $status, $latitude, $longitude); 
        error_log("PLANT: "); 
        error_log(print_r($plant, true)); 
        $this->plantRepositoryFacade->savePlantDetails($plant); 
    }

    public function updatePlantDetails(array $data, string $id) { 
        $name = $data['name'] ?? ''; 
        $name = ($name !== '') ? $name : null; 

        $country = $data['country'] ?? ''; 
        $country = ($country !== '') ? $country : null; 

        $latitude = $data['latitude'] ?? ''; 
        $latitude = ($latitude !== '') ? $latitude : null; 

        $longitude = $data['longitude'] ?? ''; 
        $longitude = ($longitude !== '') ? $longitude : null;

        $status = PlantStatus::DRAFT; 

        $plant = new Plant($country, $id, $name, $status, $latitude, $longitude); 

        error_log("[DEBUG] A power plant was built successfully"); 
        error_log("[DEBUG]" . print_r($plant, true)); 
        $this->plantRepositoryFacade->updatePlantDetails($plant); 
    }

    public function getAllPowerPlants(): array { 
        return $this->plantRepositoryFacade->getAllPowerPlants(); 
    }

    public function findById(string $plantId) { 
        $plant = $this->plantRepositoryFacade->getPlantDetailsById($plantId); 

        if($plant === null) { 
            echo "[ERROR] Plant with this id was not found"; 
        }

        return $plant; 
    }
}