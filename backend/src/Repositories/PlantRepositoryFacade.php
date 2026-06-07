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

    // Data for feasibility reports 
    public function getPlantData(string $plantId): ?array { 
        try { 
            $basicData = $this->basicPlantRepository->findByPlantId($plantId); 
            $geologicalData = $this->geologicalPlantRepository->findByPlantId($plantId); 
            $technicalData = $this->technicalPlantRepository->findByPlantId($plantId); 

            error_log("[DEBUG] Testare Plant ID: " . $plantId);
            error_log("[DEBUG] Basic Data: " . ($basicData ? 'GASIT' : 'LIPSESTE'));
            error_log("[DEBUG] Geological Data: " . ($geologicalData ? 'GASIT' : 'LIPSESTE'));
            error_log("[DEBUG] Technical Data: " . ($technicalData ? 'GASIT' : 'LIPSESTE'));
            
            if(!$basicData || !$geologicalData || !$technicalData) { 
                return null; 
            }

            $reactorSchemas = $this->technicalPlantRepository->getSchemasByTechnicalDataId($technicalData->getId()); 

            return [
                'basic_data' => $basicData,
                'geological_data' => $geologicalData,
                'technical_data' => $technicalData,
                'reactor_schemas' => $reactorSchemas
            ];
        } catch(Exception $e) { 
            error_log("[PLANT FACADE ERROR] Eroare la asamblarea datelor centralei: " . $e->getMessage()); 
            throw new Exception("Eroare la asamblarea datelor de fezabilitate.");
        }
    }

    public function updatePlantStatus(string $plantId, string $status): void {
        $this->detailsPlantRepository->updateStatus($plantId, $status);
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

    public function getReactorSchemaByDetails(string $reactorType, string $coolingType): ?ReactorSchema {
        return $this->technicalPlantRepository->getReactorSchemaByDetails($reactorType, $coolingType);
    }
}