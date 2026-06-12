<?php

require_once __DIR__ . '/../Dto/CreateDataResponseDTO.php';
require_once __DIR__ . '/../Dto/GeologicalPlantDataDTO.php';

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
        $this->plantRepositoryFacade->updatePlantStatus($plantId, $status);
    }

    public function submitForReview(string $plantId, string $userId): bool {
        $plant = $this->detailsPlantService->findById($plantId);
        if (!$plant) return false;
        if ($plant->getStatus()->value !== 'DRAFT') return false;
        if ($plant->getCreatedBy() !== $userId) return false;

        $completePlantProfile = $this->getCompletePlantProfile($plantId);
        foreach ($completePlantProfile as $profile) {
            if ($profile == null) return false;
        }

        $this->plantRepositoryFacade->updatePlantStatus($plantId, 'REVIEW');
        return true;
    }

    public function reopenDraft(string $plantId, string $userId): bool {
        $plant = $this->detailsPlantService->findById($plantId);
        if (!$plant) return false;
        if ($plant->getStatus()->value !== 'REJECTED') return false;
        if ($plant->getCreatedBy() !== $userId) return false;

        $this->plantRepositoryFacade->updatePlantStatus($plantId, 'DRAFT');
        return true;
    }

    public function getAllPowerPlants(): array {
        return $this->detailsPlantService->getAllPowerPlants();
    }

  
    public function getPendingApprovalsList(): array {
        $allPlants = $this->detailsPlantService->getAllPowerPlants();

        $pendingPlants = array_filter($allPlants, function($plant) {
            return ($plant['status'] ?? '') === PlantStatus::REVIEW->value;
        });

        return array_values($pendingPlants);
    }
    
    public function getPlantsByStatus(array $data): array { 
        $powerPlants = $this->detailsPlantService->getPlantsByStatus($data); 
        return $powerPlants;
    }

    public function getPlantDetailsById(string $plantId): ?Plant {
        return $this->detailsPlantService->findById($plantId);
    }

    public function savePlantDetails(array $data): CreateDataResponseDTO {
        return $this->detailsPlantService->savePlantDetails($data);
    }

    public function updateStatus(array $data, string $plantId) { 
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
        return true; 
    }

    public function updatePlantDetails(array $data, string $plantId): void {
        $this->detailsPlantService->updatePlantDetails($data, $plantId);
    }

    // Basics
    public function getBasicDataByPlantId(string $plantId): ?BasicPlantData {
        return $this->basicPlantService->findByPlantId($plantId);
    }

    public function saveBasicData(array $data, string $plantId): CreateDataResponseDTO {
        return $this->basicPlantService->save($data, $plantId);
    }

    public function updateBasicData(array $data, string $plantId): void {
        $this->basicPlantService->update($data, $plantId);
    }

    // Geological
    public function previewGeologicalLocation(float $lat, float $lon): array {
        return $this->geologicalPlantService->runAutoGeolocation($lat, $lon);
    }

    public function getGeologicalDataByPlantId(string $plantId): ?GeologicalPlantDataDTO {
        return $this->geologicalPlantService->getGeologicalData($plantId);
    }

    public function saveGeologicalData(array $data, string $plantId): CreateDataResponseDTO {
        return $this->geologicalPlantService->save($data, $plantId);
    }

    public function updateGeologicalData(array $data, string $plantId): void {
        $this->geologicalPlantService->update($data, $plantId);
    }
    
    // Technical 
    public function getTechnicalDataByPlantId(string $plantId): ?TechnicalPlantData {
        return $this->technicalPlantService->findByPlantId($plantId);
    }

    public function saveTechnicalData(array $data, string $plantId): CreateDataResponseDTO {
        return $this->technicalPlantService->save($data, $plantId);
    }

    public function updateTechnicalData(array $data, string $plantId): void {
        $this->technicalPlantService->update($data, $plantId);
    }

    public function getCompletePlantProfile(string $plantId): array {
        return [
            'details' => $this->detailsPlantService->findById($plantId),
            'basic' => $this->basicPlantService->findByPlantId($plantId),
            'geological' => $this->geologicalPlantService->findByPlantId($plantId),
            'technical' => $this->technicalPlantService->findByPlantId($plantId),
        ];
    }
}