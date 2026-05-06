<?php

class PlantRepositoryFacade { 
    private DetailsPlantRepository $detailsPlantRepository;
    private BasicPlantRepository $basicPlantRepository;
    private GeologicalPlantRepository $geologicalPlantRepository;
    private TechnicalPlantRepository $technicalPlantRepository;

    public function __construct (private PDO $db) {
        $this->detailsPlantRepository = new DetailsPlantRepository($this->db);
        $this->basicPlantRepository = new BasicPlantRepository($this->db);
        $this->geologicalPlantRepository = new GeologicalPlantRepository($this->db);
        $this->technicalPlantRepository = new TechnicalPlantRepository($this->db);
    }

    // Details 
    public function getAllPowerPlants(): array {
        return $this->detailsPlantRepository->findAll();
    }

    public function getPlantDetailsById(string $plantId): ?Plant {
        return $this->detailsPlantRepository->findById($plantId);
    }

    public function savePlantDetails(Plant $plant): void {
        $this->detailsPlantRepository->save($plant);
    }

    public function updatePlantDetails(Plant $plant): void {
        $this->detailsPlantRepository->update($plant);
    }

    // Basics
    public function getBasicDataByPlantId(string $plantId): ?BasicPlantData {
        return $this->basicPlantRepository->findByPlantId($plantId);
    }

    public function saveBasicData(BasicPlantData $basicData): void {
        $this->basicPlantRepository->save($basicData);
    }

    public function updateBasicData(BasicPlantData $basicData): void {
        $this->basicPlantRepository->update($basicData);
    }


    // Geological 
    public function getGeologicalDataByPlantId(string $plantId): ?GeologicalPlantData {
        return $this->geologicalPlantRepository->findByPlantId($plantId);
    }

    public function saveGeologicalData(GeologicalPlantData $geoData): void {
        $this->geologicalPlantRepository->save($geoData);
    }

    public function updateGeologicalData(GeologicalPlantData $geoData): void {
        $this->geologicalPlantRepository->update($geoData);
    }


    // Technical 
    public function getTechnicalDataByPlantId(string $plantId): ?TechnicalPlantData {
        return $this->technicalPlantRepository->findByPlantId($plantId);
    }

    public function saveTechnicalData(TechnicalPlantData $techData): void {
        $this->technicalPlantRepository->save($techData);
    }

    public function updateTechnicalData(TechnicalPlantData $techData): void {
        $this->technicalPlantRepository->update($techData);
    }
}