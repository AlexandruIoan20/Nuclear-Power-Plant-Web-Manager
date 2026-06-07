<?php

require_once __DIR__ . '/../Dto/CreateDataResponseDTO.php'; 

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

    // Details
    public function getAllPowerPlants(): array {
        return $this->detailsPlantService->getAllPowerPlants();
    }

    public function getPlantDetailsById(string $plantId): ?Plant {
        return $this->detailsPlantService->findById($plantId);
    }

    public function savePlantDetails(array $data): CreateDataResponseDTO {
        return $this->detailsPlantService->savePlantDetails($data);
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
    public function getGeologicalDataByPlantId(string $plantId): ?GeologicalPlantData {
        return $this->geologicalPlantService->findByPlantId($plantId);
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