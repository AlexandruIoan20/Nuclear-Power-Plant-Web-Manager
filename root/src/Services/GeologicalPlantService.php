<?php

require_once __DIR__ . '/../Repositories/GeologicalPlantRepository.php'; 
require_once __DIR__ . '/../Entities/SoilType.php';
require_once __DIR__ . '/../Entities/WaterSourceType.php';

class GeologicalPlantService { 
    private GeologicalPlantRepository $geologicalPlantRepository; 

    public function __construct(GeologicalPlantRepository $geologicalPlantRepository) { 
        $this->geologicalPlantRepository = $geologicalPlantRepository; 
    }

    public function findByPlantId(string $plantId) { 
        return $this->geologicalPlantRepository->findByPlantId($plantId); 
    }

    public function save(array $data, string $plantId): void { 
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

        $this->geologicalPlantRepository->save($geologicalPlantData); 
    }

    public function update(array $data, string $plantId): void { 
        $currentData = $this->geologicalPlantRepository->findByPlantId($plantId); 
        
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

        $this->geologicalPlantRepository->update($geologicalPlantData); 
    }
}