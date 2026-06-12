<?php

require_once __DIR__ . '/../../Entities/GeologicalPlantData.php';
require_once __DIR__ . '/../../Entities/SoilType.php';
require_once __DIR__ . '/../../Entities/WaterSourceType.php';

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

        return $this->mapRowToEntity($row);
    }

    public function save(GeologicalPlantData $geologicalPlantData): bool {
        $statement = $this->pdo->prepare("
            INSERT INTO geological_data (
                id, 
                power_plant_id, 
                country,
                latitude,
                longitude,
                soil_type, 
                water_source_type,
                seismic_stability, 
                flood_risk,
                groundwater_level, 
                water_proximity, 
                water_flow_rate,
                population_density,
                transport_infrastructure_score,
                geological_risk_score,
                created_at,
                updated_at
            ) VALUES (
                :id, 
                :power_plant_id, 
                :country,
                :latitude,
                :longitude,
                :soil_type, 
                :water_source_type,
                :seismic_stability, 
                :flood_risk,
                :groundwater_level, 
                :water_proximity, 
                :water_flow_rate,
                :population_density,
                :transport_infrastructure_score,
                :geological_risk_score,
                NOW(),
                NOW()
            )
            ON CONFLICT (power_plant_id) DO NOTHING
        ");

        $params = $this->extractParameters($geologicalPlantData);
        unset($params['created_at']);
        $statement->execute($params);
        return $statement->rowCount() > 0;
    }

    public function update(GeologicalPlantData $geologicalPlantData): void {
        $statement = $this->pdo->prepare("
            UPDATE geological_data 
            SET 
                country = :country,
                latitude = :latitude,
                longitude = :longitude,
                soil_type = :soil_type, 
                water_source_type = :water_source_type,
                seismic_stability = :seismic_stability, 
                flood_risk = :flood_risk,
                groundwater_level = :groundwater_level, 
                water_proximity = :water_proximity, 
                water_flow_rate = :water_flow_rate,
                population_density = :population_density,
                transport_infrastructure_score = :transport_infrastructure_score,
                geological_risk_score = :geological_risk_score,
                updated_at = NOW()
            WHERE id = :id
        ");

        $params = $this->extractParameters($geologicalPlantData);
        unset($params['created_at'], $params['power_plant_id']);

        $statement->execute($params);
    }

    private function mapRowToEntity(array $row): GeologicalPlantData {
        $soilType = $row['soil_type'] ? SoilType::tryFrom($row['soil_type']) : null;
        $waterSourceType = $row['water_source_type'] ? WaterSourceType::tryFrom($row['water_source_type']) : null;

        return new GeologicalPlantData(
            $row['power_plant_id'],
            $row['id'],
            $row['country'],
            $row['latitude'] !== null ? (float)$row['latitude'] : null,
            $row['longitude'] !== null ? (float)$row['longitude'] : null,
            $soilType,
            $waterSourceType,
            $row['seismic_stability'] !== null ? (float)$row['seismic_stability'] : null,
            $row['flood_risk'] !== null ? (float)$row['flood_risk'] : null,
            $row['groundwater_level'] !== null ? (float)$row['groundwater_level'] : null,
            $row['water_proximity'] !== null ? (float)$row['water_proximity'] : null,
            $row['water_flow_rate'] !== null ? (float)$row['water_flow_rate'] : null,
            $row['population_density'] !== null ? (float)$row['population_density'] : null,
            $row['transport_infrastructure_score'] !== null ? (float)$row['transport_infrastructure_score'] : null,
            $row['geological_risk_score'] !== null ? (float)$row['geological_risk_score'] : null,
            $row['created_at'],
            $row['updated_at']
        );
    }

    private function extractParameters(GeologicalPlantData $g): array {
        return [
            'id' => $g->getId(),
            'power_plant_id' => $g->getPowerPlantId(),
            'country' => $g->getCountry(),
            'latitude' => $g->getLatitude(),
            'longitude' => $g->getLongitude(),
            'soil_type' => $g->getSoilType()?->value,
            'water_source_type' => $g->getWaterSourceType()?->value,
            'seismic_stability' => $g->getSeismicStability(),
            'flood_risk' => $g->getFloodRisk(),
            'groundwater_level' => $g->getGroundwaterLevel(),
            'water_proximity' => $g->getWaterProximity(),
            'water_flow_rate' => $g->getWaterFlowRate(),
            'population_density' => $g->getPopulationDensity(),
            'transport_infrastructure_score' => $g->getTransportInfrastructureScore(),
            'geological_risk_score' => $g->getGeologicalRiskScore(),
            'created_at' => $g->getCreatedAt(),
        ];
    }
}
