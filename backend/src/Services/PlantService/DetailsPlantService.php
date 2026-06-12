<?php

require_once __DIR__ . '/../../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../Entities/Plant.php'; 

require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php'; 
require_once __DIR__ . '/../LogService.php';

class DetailsPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function savePlantDetails(array $data): CreateDataResponseDTO { 
        $name = $data['name'] ?? ''; 
        $name = ($name !== '') ? $name : null; 

        $status = PlantStatus::DRAFT; 
        $id = generateUUID(); 
        $createdBy = $_SESSION['user_id'] ?? null;

        $plant = new Plant($id, $name, $status, $createdBy); 
        $this->plantRepositoryFacade->savePlantDetails($plant); 

        return new CreateDataResponseDTO($id); 
    }

    public function updateStatus(array $data, string $plantId) { 
        $this->plantRepositoryFacade->updateStatus($data, $plantId); 
    }

    public function updatePlantDetails(array $data, string $id) { 
        $existing = $this->plantRepositoryFacade->getPlantDetailsById($id);
        if (!$existing) {
            throw new Exception("Centrala nu a fost găsită.");
        }
        if ($existing->getStatus()->value !== 'DRAFT') {
            throw new Exception("Nu poți edita o centrală care nu este în status DRAFT.");
        }

        $name = $data['name'] ?? ''; 
        $status = PlantStatus::DRAFT; 

        $plant = new Plant($id, $name, $status); 

        LogService::instance()->debug("[DEBUG] A power plant was built successfully");
        LogService::instance()->debug("[DEBUG]" . print_r($plant, true));
        $this->plantRepositoryFacade->updatePlantDetails($plant); 
    }

    public function getAllPowerPlants(): array { 
        return $this->plantRepositoryFacade->getAllPowerPlants(); 
    }

    public function getMyPowerPlants(string $userId): array {
        return $this->plantRepositoryFacade->findByUser($userId);
    }

    public function getPlantsByStatus(array $data): array { 
        $status = PlantStatus::tryFrom($data['status']); 
    
        if($status === null) {
            throw new Exception("Nu exista centrale cu acest status de proiect.");
        }
    
        $powerPlants = $this->plantRepositoryFacade->getPlantsByStatus(['status' => $status->value]); 
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