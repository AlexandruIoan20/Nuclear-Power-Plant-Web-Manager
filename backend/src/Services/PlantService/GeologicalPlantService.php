<?php

require_once __DIR__ . '/../../Entities/SoilType.php';
require_once __DIR__ . '/../../Entities/WaterSourceType.php';
require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php'; 

class GeologicalPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId) { 
        return $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
    }

    public function save(array $data, string $plantId): CreateDataResponseDTO { 
        $existingData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        if ($existingData !== null) { 
            throw new Exception("Există deja date geologice pentru această centrală. Te rugăm să folosești metoda de UPDATE (PUT/PATCH).");
        }

        $soilType = (isset($data['soilType']) && $data['soilType'] !== '') 
            ? SoilType::from($data['soilType']) 
            : null;

        $waterSourceType = (isset($data['waterSourceType']) && $data['waterSourceType'] !== '') 
            ? WaterSourceType::from($data['waterSourceType']) 
            : null;

        $seismicStability = (isset($data['seismicStability']) && $data['seismicStability'] !== '') 
            ? (float) $data['seismicStability'] 
            : null;

        $floodRisk = (isset($data['floodRisk']) && $data['floodRisk'] !== '') 
            ? (float) $data['floodRisk'] 
            : null;

        $groundwaterLevel = (isset($data['groundwaterLevel']) && $data['groundwaterLevel'] !== '') 
            ? (float) $data['groundwaterLevel'] 
            : null;

        $waterProximity = (isset($data['waterProximity']) && $data['waterProximity'] !== '') 
            ? (float) $data['waterProximity'] 
            : null;

        $waterFlowRate = (isset($data['waterFlowRate']) && $data['waterFlowRate'] !== '') 
            ? (float) $data['waterFlowRate'] 
            : null;

        $populationDensity = (isset($data['populationDensity']) && $data['populationDensity'] !== '') 
            ? (float) $data['populationDensity'] 
            : null;

        $transportInfrastructureScore = (isset($data['transportInfrastructureScore']) && $data['transportInfrastructureScore'] !== '') 
            ? (float) $data['transportInfrastructureScore'] 
            : null;

        $geologicalRiskScore = (isset($data['geologicalRiskScore']) && $data['geologicalRiskScore'] !== '') 
            ? (float) $data['geologicalRiskScore'] 
            : null;

        $geologicalPlantData = new GeologicalPlantData(
            $plantId, 
            null, 
            $soilType, 
            $waterSourceType,
            $seismicStability,
            $floodRisk,
            $groundwaterLevel, 
            $waterProximity,
            $waterFlowRate,
            $populationDensity,
            $transportInfrastructureScore,
            $geologicalRiskScore
        ); 

        $this->plantRepositoryFacade->saveGeologicalData($geologicalPlantData); 
        return new CreateDataResponseDTO($geologicalPlantData->getId()); 
    }

    public function update(array $data, string $plantId): void { 
        $currentData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        if ($currentData === null) {
            throw new Exception("Nu s-au găsit date geologice existente pentru a efectua actualizarea.");
        }
        
        $soilTypeRaw = $data['soilType'] ?? '';
        $soilType = ($soilTypeRaw !== '') ? SoilType::from($soilTypeRaw) : null;

        $waterSourceTypeRaw = $data['waterSourceType'] ?? '';
        $waterSourceType = ($waterSourceTypeRaw !== '') ? WaterSourceType::from($waterSourceTypeRaw) : null;

        $seismicStability = (isset($data['seismicStability']) && $data['seismicStability'] !== '') ? (float)$data['seismicStability'] : null;
        $floodRisk = (isset($data['floodRisk']) && $data['floodRisk'] !== '') ? (float)$data['floodRisk'] : null;
        $groundwaterLevel = (isset($data['groundwaterLevel']) && $data['groundwaterLevel'] !== '') ? (float)$data['groundwaterLevel'] : null;
        $waterProximity = (isset($data['waterProximity']) && $data['waterProximity'] !== '') ? (float)$data['waterProximity'] : null;
        $waterFlowRate = (isset($data['waterFlowRate']) && $data['waterFlowRate'] !== '') ? (float)$data['waterFlowRate'] : null;
        $populationDensity = (isset($data['populationDensity']) && $data['populationDensity'] !== '') ? (float)$data['populationDensity'] : null;
        $transportInfrastructureScore = (isset($data['transportInfrastructureScore']) && $data['transportInfrastructureScore'] !== '') ? (float)$data['transportInfrastructureScore'] : null;
        $geologicalRiskScore = (isset($data['geologicalRiskScore']) && $data['geologicalRiskScore'] !== '') ? (float)$data['geologicalRiskScore'] : null;

        $geologicalPlantData = new GeologicalPlantData(
            $plantId, 
            $currentData->getId(), 
            $soilType, 
            $waterSourceType,
            $seismicStability, 
            $floodRisk,
            $groundwaterLevel, 
            $waterProximity, 
            $waterFlowRate,
            $populationDensity,
            $transportInfrastructureScore,
            $geologicalRiskScore
        ); 

        $this->plantRepositoryFacade->updateGeologicalData($geologicalPlantData); 
    }
}