<?php

require_once __DIR__ . '/../Entities/GeologicalPlantData.php';
require_once __DIR__ . '/../Entities/SoilType.php';
require_once __DIR__ . '/../Entities/WaterSourceType.php';

class GeologicalPlantRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByPlantId(string $plantId): ?GeologicalPlantData {
        $statement = $this->pdo->prepare("SELECT * FROM geological_data WHERE power_plant_id = :plantId");
        $statement->execute(['plantId' => $plantId]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $soilType = $row['soil_type'] ? SoilType::from($row['soil_type']) : null;
        $waterSourceType = $row['water_source_type'] ? WaterSourceType::from($row['water_source_type']) : null;

        return new GeologicalPlantData(
            $row['power_plant_id'],
            $row['id'],
            $soilType,
            $waterSourceType,
            $row['seismic_stability'] !== null ? (float)$row['seismic_stability'] : null,
            $row['flood_risk'] !== null ? (float)$row['flood_risk'] : null,
            $row['groundwater_level'] !== null ? (float)$row['groundwater_level'] : null,
            $row['water_proximity'] !== null ? (float)$row['water_proximity'] : null,
            $row['water_flow_rate'] !== null ? (float)$row['water_flow_rate'] : null,
            $row['population_density'] !== null ? (float)$row['population_density'] : null,
            $row['transport_infrastructure_score'] !== null ? (float)$row['transport_infrastructure_score'] : null,
            $row['geological_risk_score'] !== null ? (float)$row['geological_risk_score'] : null
        );
    }

    public function save(GeologicalPlantData $geologicalPlantData): void {
        $statement = $this->pdo->prepare("
            INSERT INTO geological_data (
                id, 
                power_plant_id, 
                soil_type, 
                water_source_type,
                seismic_stability, 
                flood_risk,
                groundwater_level, 
                water_proximity, 
                water_flow_rate,
                population_density,
                transport_infrastructure_score,
                geological_risk_score
            ) VALUES (
                :id, 
                :powerPlantId, 
                :soilType, 
                :waterSourceType,
                :seismicStability, 
                :floodRisk,
                :groundwaterLevel, 
                :waterProximity, 
                :waterFlowRate,
                :populationDensity,
                :transportInfrastructureScore,
                :geologicalRiskScore 
            )
        ");

        $statement->execute([
            'id' => $geologicalPlantData->getId(),
            'powerPlantId' => $geologicalPlantData->getPowerPlantId(),
            'soilType' => $geologicalPlantData->getSoilType()?->value,
            'waterSourceType' => $geologicalPlantData->getWaterSourceType()?->value,
            'seismicStability' => $geologicalPlantData->getSeismicStability(),
            'floodRisk' => $geologicalPlantData->getFloodRisk(),
            'groundwaterLevel' => $geologicalPlantData->getGroundwaterLevel(),
            'waterProximity' => $geologicalPlantData->getWaterProximity(),
            'waterFlowRate' => $geologicalPlantData->getWaterFlowRate(),
            'populationDensity' => $geologicalPlantData->getPopulationDensity(),
            'transportInfrastructureScore' => $geologicalPlantData->getTransportInfrastructureScore(),
            'geologicalRiskScore' => $geologicalPlantData->getGeologicalRiskScore()
        ]);
    }

    public function update(GeologicalPlantData $geologicalPlantData): void {
        $statement = $this->pdo->prepare("
            UPDATE geological_data 
            SET 
                soil_type = :soilType, 
                water_source_type = :waterSourceType,
                seismic_stability = :seismicStability, 
                flood_risk = :floodRisk,
                groundwater_level = :groundwaterLevel, 
                water_proximity = :waterProximity, 
                water_flow_rate = :waterFlowRate,
                population_density = :populationDensity,
                transport_infrastructure_score = :transportInfrastructureScore,
                geological_risk_score = :geologicalRiskScore
            WHERE id = :id
        ");

        $statement->execute([
            'id' => $geologicalPlantData->getId(),
            'soilType' => $geologicalPlantData->getSoilType()?->value,
            'waterSourceType' => $geologicalPlantData->getWaterSourceType()?->value,
            'seismicStability' => $geologicalPlantData->getSeismicStability(),
            'floodRisk' => $geologicalPlantData->getFloodRisk(),
            'groundwaterLevel' => $geologicalPlantData->getGroundwaterLevel(),
            'waterProximity' => $geologicalPlantData->getWaterProximity(),
            'waterFlowRate' => $geologicalPlantData->getWaterFlowRate(),
            'populationDensity' => $geologicalPlantData->getPopulationDensity(),
            'transportInfrastructureScore' => $geologicalPlantData->getTransportInfrastructureScore(),
            'geologicalRiskScore' => $geologicalPlantData->getGeologicalRiskScore()
        ]);
    }
}