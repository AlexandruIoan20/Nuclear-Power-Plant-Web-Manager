<?php

require_once __DIR__ . '/../../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../Entities/Plant.php'; 

require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php'; 

class DetailsPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function savePlantDetails(array $data): CreateDataResponseDTO { 
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

        return new CreateDataResponseDTO($id); 
    }

    public function updateStatus(array $data, string $plantId) { 
        $this->plantRepositoryFacade->updateStatus($data, $plantId); 
    }

    public function updatePlantDetails(array $data, string $id) { 
        $name = $data['name'] ?? ''; 
        $country = $data['country'] ?? ''; 

        $latitude = (isset($data['latitude']) && $data['latitude'] !== '') ? (float) $data['latitude'] : null;         
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? (float) $data['longitude'] : null;
        $status = PlantStatus::DRAFT; 

        $plant = new Plant($country, $id, $name, $status, $latitude, $longitude); 

        error_log("[DEBUG] A power plant was built successfully"); 
        error_log("[DEBUG]" . print_r($plant, true)); 
        $this->plantRepositoryFacade->updatePlantDetails($plant); 
    }

    public function getAllPowerPlants(): array { 
        return $this->plantRepositoryFacade->getAllPowerPlants(); 
    }

    public function getPlantsByStatus(array $data): array { 
        $status = PlantStatus::tryFrom($data['status']); 
    
        if($status === null) {
            return ["success" => false, "message" => "Nu exista centrale cu acest status de proiect."];
        }
    
        $powerPlants = $this->plantRepositoryFacade->getPlantsByStatus(['status' => $status->value]); 
        error_log("powerPlants in service 2:  " . print_r($powerPlants, true)); 
        return $powerPlants; 
    }

    public function findById(string $plantId) { 
        $plant = $this->plantRepositoryFacade->getPlantDetailsById($plantId); 

        if($plant === null) { 
            echo "[ERROR] Plant with this id was not found"; 
        }

        return $plant; 
    }
}