<?php

require_once __DIR__ . '/../../Entities/SoilType.php';
require_once __DIR__ . '/../../Entities/WaterSourceType.php';

class GeologicalPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId) { 
        return $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
    }

    public function save(array $data, string $plantId): void { 
        $existingData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        if($existingData !== null) { 
            throw new Exception("Există deja date geologice pentru această centrală. Te rugăm să folosești metoda de UPDATE (PUT/PATCH).");
        }

        $soilType = (isset($data['soil_type']) && $data['soil_type'] !== '') 
            ? SoilType::from($data['soil_type']) 
            : null;

        $waterSourceType = (isset($data['water_source_type']) && $data['water_source_type'] !== '') 
            ? WaterSourceType::from($data['water_source_type']) 
            : null;

        $seismicStability = (isset($data['seismic_stability']) && $data['seismic_stability'] !== '') 
            ? (float) $data['seismic_stability'] 
            : null;

        $floodRisk = (isset($data['flood_risk']) && $data['flood_risk'] !== '') 
            ? (float) $data['flood_risk'] 
            : null;

        $groundwaterLevel = (isset($data['groundwater_level']) && $data['groundwater_level'] !== '') 
            ? (float) $data['groundwater_level'] 
            : null;

        $waterProximity = (isset($data['water_proximity']) && $data['water_proximity'] !== '') 
            ? (float) $data['water_proximity'] 
            : null;

        $waterFlowRate = (isset($data['water_flow_rate']) && $data['water_flow_rate'] !== '') 
            ? (float) $data['water_flow_rate'] 
            : null;

        $populationDensity = (isset($data['population_density']) && $data['population_density'] !== '') 
            ? (float) $data['population_density'] 
            : null;

        $transportInfrastructureScore = (isset($data['transport_infrastructure_score']) && $data['transport_infrastructure_score'] !== '') 
            ? (float) $data['transport_infrastructure_score'] 
            : null;

        $geologicalRiskScore = (isset($data['geological_risk_score']) && $data['geological_risk_score'] !== '') 
            ? (float) $data['geological_risk_score'] 
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
    }

    public function update(array $data, string $plantId): void { 
        $currentData = $this->plantRepositoryFacade->getGeologicalDataByPlantId($plantId); 
        
        $soilTypeRaw = $data['soil_type'] ?? '';
        $soilType = ($soilTypeRaw !== '') ? SoilType::from($soilTypeRaw) : null;

        $waterSourceTypeRaw = $data['water_source_type'] ?? '';
        $waterSourceType = ($waterSourceTypeRaw !== '') ? WaterSourceType::from($waterSourceTypeRaw) : null;

        $seismicStability = $data['seismic_stability'] ?? '';
        $seismicStability = ($seismicStability !== '') ? $seismicStability : null;

        $floodRisk = $data['flood_risk'] ?? '';
        $floodRisk = ($floodRisk !== '') ? $floodRisk : null;

        $groundwaterLevel = $data['groundwater_level'] ?? '';
        $groundwaterLevel = ($groundwaterLevel !== '') ? $groundwaterLevel : null;

        $waterProximity = $data['water_proximity'] ?? '';
        $waterProximity = ($waterProximity !== '') ? $waterProximity : null;

        $waterFlowRate = $data['water_flow_rate'] ?? '';
        $waterFlowRate = ($waterFlowRate !== '') ? $waterFlowRate : null;

        $populationDensity = $data['population_density'] ?? '';
        $populationDensity = ($populationDensity !== '') ? $populationDensity : null;

        $transportInfrastructureScore = $data['transport_infrastructure_score'] ?? '';
        $transportInfrastructureScore = ($transportInfrastructureScore !== '') ? $transportInfrastructureScore : null;

        $geologicalRiskScore = $data['geological_risk_score'] ?? '';
        $geologicalRiskScore = ($geologicalRiskScore !== '') ? $geologicalRiskScore : null;

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