<?php

require_once __DIR__ . '/../../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../Entities/Plant.php'; 
require_once __DIR__ . '/../../Repositories/PlantRepository/PlantRepository.php'; 

class PlantService { 
    private PlantRepository $plantRepository; 

    public function __construct(PlantRepository $plantRepository) { 
        $this->plantRepository = $plantRepository; 
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
        $this->plantRepository->save($plant); 
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
        $this->plantRepository->update($plant); 
    }

    public function getAllPowerPlants(): array { 
        return $this->plantRepository->findAll(); 
    }

    public function findById(string $plantId) { 
        $plant = $this->plantRepository->findById($plantId); 

        if($plant === null) { 
            echo "[ERROR] Plant with this id was not found"; 
        }

        return $plant; 
    }
}