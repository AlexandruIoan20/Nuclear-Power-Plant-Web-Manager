<?php

require_once __DIR__ . '/../Repositories/GeologicalPlantRepository.php'; 
require_once __DIR__ . '/../Entities/SoilType.php';

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

        $seismicStability = $data['seismic_stability'] ?? '';
        $seismicStability = ($seismicStability !== '') ? $seismicStability : null;

        $groundwaterLevel = $data['groundwater_level'] ?? '';
        $groundwaterLevel = ($groundwaterLevel !== '') ? $groundwaterLevel : null;

        $waterProximity = $data['water_proximity'] ?? '';
        $waterProximity = ($waterProximity !== '') ? $waterProximity : null;

        $geologicalRiskScore = $data['geological_risk_score'] ?? '';
        $geologicalRiskScore = ($geologicalRiskScore !== '') ? $geologicalRiskScore : null;

        $geologicalPlantData = new GeologicalPlantData(
            $plantId, 
            null, 
            $soilType, 
            $seismicStability, 
            $groundwaterLevel, 
            $waterProximity, 
            $geologicalRiskScore
        ); 

        $this->geologicalPlantRepository->save($geologicalPlantData); 
    }

    public function update(array $data, string $plantId): void { 
        $currentData = $this->geologicalPlantRepository->findByPlantId($plantId); 
        
        $soilTypeRaw = $data['soil_type'] ?? '';
        $soilType = ($soilTypeRaw !== '') ? SoilType::from($soilTypeRaw) : null;

        $seismicStability = $data['seismic_stability'] ?? '';
        $seismicStability = ($seismicStability !== '') ? $seismicStability : null;

        $groundwaterLevel = $data['groundwater_level'] ?? '';
        $groundwaterLevel = ($groundwaterLevel !== '') ? $groundwaterLevel : null;

        $waterProximity = $data['water_proximity'] ?? '';
        $waterProximity = ($waterProximity !== '') ? $waterProximity : null;

        $geologicalRiskScore = $data['geological_risk_score'] ?? '';
        $geologicalRiskScore = ($geologicalRiskScore !== '') ? $geologicalRiskScore : null;

        $geologicalPlantData = new GeologicalPlantData(
            $plantId, 
            $currentData->getId(), 
            $soilType, 
            $seismicStability, 
            $groundwaterLevel, 
            $waterProximity, 
            $geologicalRiskScore
        ); 

        $this->geologicalPlantRepository->update($geologicalPlantData); 
    }
}