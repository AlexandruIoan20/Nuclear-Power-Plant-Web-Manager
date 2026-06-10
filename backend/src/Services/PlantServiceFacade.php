<?php

require_once __DIR__ . '/../Dto/CreateDataResponseDTO.php';
require_once __DIR__ . '/../Dto/GeologicalPlantDataDTO.php';

require_once __DIR__ . '/LogService.php';

class PlantServiceFacade {
    private DetailsPlantService $detailsPlantService;
    private BasicPlantService $basicPlantService;
    private GeologicalPlantService $geologicalPlantService;
    private TechnicalPlantService $technicalPlantService;

    public function __construct(
        private PlantRepositoryFacade $plantRepositoryFacade
    ) {
        $this->detailsPlantService = new DetailsPlantService($this->plantRepositoryFacade);
        $this->basicPlantService = new BasicPlantService($this->plantRepositoryFacade);
        $this->geologicalPlantService = new GeologicalPlantService($this->plantRepositoryFacade);
        $this->technicalPlantService = new TechnicalPlantService($this->plantRepositoryFacade);
    }

    public function updatePlantStatus(string $plantId, string $status): void {
        LogService::instance()->info("Actualizare status centrală", ['plant_id' => $plantId, 'status' => $status]);
        $this->plantRepositoryFacade->updatePlantStatus($plantId, $status);
    }


    public function getAllPowerPlants(): array {
        LogService::instance()->debug("Obținere toate centralele");
        return $this->detailsPlantService->getAllPowerPlants();
    }

  
    public function getPendingApprovalsList(): array {
        LogService::instance()->debug("Obținere lista centrale în așteptare");
        $allPlants = $this->detailsPlantService->getAllPowerPlants();
        
        $pendingPlants = array_filter($allPlants, function($plant) {
            $statusRaw = is_object($plant) && method_exists($plant, 'getStatus') 
                ? $plant->getStatus() 
                : (is_array($plant) ? ($plant['status'] ?? '') : '');
                
          
            if ($statusRaw instanceof \UnitEnum) {
                
                $statusStr = property_exists($statusRaw, 'value') ? $statusRaw->value : $statusRaw->name;
            } else {
               
                $statusStr = (string)$statusRaw;
            }
                
            return strtoupper($statusStr) === 'PENDING' || strtoupper($statusStr) === 'DRAFT';
        });

        
        return array_values($pendingPlants);
    }
    
    public function getPlantsByStatus(array $data): array {
        LogService::instance()->debug("Obținere centrale după status", ['status' => $data['status'] ?? '']);
        $powerPlants = $this->detailsPlantService->getPlantsByStatus($data);
        return $powerPlants;
    }

    public function getPlantDetailsById(string $plantId): ?Plant {
        LogService::instance()->debug("Obținere detalii centrală", ['plant_id' => $plantId]);
        return $this->detailsPlantService->findById($plantId);
    }

    public function savePlantDetails(array $data): CreateDataResponseDTO {
        LogService::instance()->info("Salvare detalii centrală", ['plant_name' => $data['name'] ?? '']);
        return $this->detailsPlantService->savePlantDetails($data);
    }

    public function updateStatus(array $data, string $plantId) {
        LogService::instance()->info("Actualizare status centrală", ['plant_id' => $plantId, 'new_status' => $data['status'] ?? '']);
        if (!isset($data['status'])) return false;

        $status = PlantStatus::tryFrom($data['status']);
        if($status == null) return false;

        $plantData = $this->plantRepositoryFacade->getPlantDetailsById($plantId);
        if($plantData->getStatus()->value == $status->value) return false;

        $completePlantProfile = $this->getCompletePlantProfile($plantId);
        foreach($completePlantProfile as $profile) {
            if($profile == null) return false;
        }

        $this->detailsPlantService->updateStatus($data, $plantId);
        LogService::instance()->info("Status centrală actualizat cu succes", ['plant_id' => $plantId, 'status' => $data['status']]);
        return true;
    }

    public function updatePlantDetails(array $data, string $plantId): void {
        LogService::instance()->info("Actualizare detalii centrală", ['plant_id' => $plantId]);
        $this->detailsPlantService->updatePlantDetails($data, $plantId);
    }

    // Basics
    public function getBasicDataByPlantId(string $plantId): ?BasicPlantData {
        LogService::instance()->debug("Obținere date de bază centrală", ['plant_id' => $plantId]);
        return $this->basicPlantService->findByPlantId($plantId);
    }

    public function saveBasicData(array $data, string $plantId): CreateDataResponseDTO {
        LogService::instance()->info("Salvare date de bază centrală", ['plant_id' => $plantId]);
        return $this->basicPlantService->save($data, $plantId);
    }

    public function updateBasicData(array $data, string $plantId): void {
        LogService::instance()->info("Actualizare date de bază centrală", ['plant_id' => $plantId]);
        $this->basicPlantService->update($data, $plantId);
    }

    // Geological
    public function getGeologicalDataByPlantId(string $plantId): ?GeologicalPlantDataDTO {
        LogService::instance()->debug("Obținere date geologice centrală", ['plant_id' => $plantId]);
        return $this->geologicalPlantService->getGeologicalData($plantId);
    }

    public function saveGeologicalData(array $data, string $plantId): CreateDataResponseDTO {
        LogService::instance()->info("Salvare date geologice centrală", ['plant_id' => $plantId]);
        return $this->geologicalPlantService->save($data, $plantId);
    }

    public function updateGeologicalData(array $data, string $plantId): void {
        LogService::instance()->info("Actualizare date geologice centrală", ['plant_id' => $plantId]);
        $this->geologicalPlantService->update($data, $plantId);
    }
    
    // Technical 
    public function getTechnicalDataByPlantId(string $plantId): ?TechnicalPlantData {
        LogService::instance()->debug("Obținere date tehnice centrală", ['plant_id' => $plantId]);
        return $this->technicalPlantService->findByPlantId($plantId);
    }

    public function saveTechnicalData(array $data, string $plantId): CreateDataResponseDTO {
        LogService::instance()->info("Salvare date tehnice centrală", ['plant_id' => $plantId]);
        return $this->technicalPlantService->save($data, $plantId);
    }

    public function updateTechnicalData(array $data, string $plantId): void {
        LogService::instance()->info("Actualizare date tehnice centrală", ['plant_id' => $plantId]);
        $this->technicalPlantService->update($data, $plantId);
    }

    public function getCompletePlantProfile(string $plantId): array {
        LogService::instance()->debug("Obținere profil complet centrală", ['plant_id' => $plantId]);
        return [
            'details' => $this->detailsPlantService->findById($plantId),
            'basic' => $this->basicPlantService->findByPlantId($plantId),
            'geological' => $this->geologicalPlantService->findByPlantId($plantId),
            'technical' => $this->technicalPlantService->findByPlantId($plantId),
        ];
    }
}