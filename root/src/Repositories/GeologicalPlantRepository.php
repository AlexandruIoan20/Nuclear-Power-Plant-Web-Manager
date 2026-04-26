<?php

require_once __DIR__ . '/../Entities/GeologicalPlantData.php';
require_once __DIR__ . '/../Entities/SoilType.php';

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

        return new GeologicalPlantData(
            $row['power_plant_id'],
            $row['id'],
            $soilType,
            $row['seismic_stability'],
            $row['groundwater_level'],
            $row['water_proximity'],
            $row['geological_risk_score']
        );
    }

    public function save(GeologicalPlantData $geologicalPlantData): void {
        $statement = $this->pdo->prepare("
            INSERT INTO geological_data (
                id, 
                power_plant_id, 
                soil_type, 
                seismic_stability, 
                groundwater_level, 
                water_proximity, 
                geological_risk_score
            ) VALUES (
                :id, 
                :powerPlantId, 
                :soilType, 
                :seismicStability, 
                :groundwaterLevel, 
                :waterProximity, 
                :geologicalRiskScore 
            )
        ");

        $statement->execute([
            'id' => $geologicalPlantData->getId(),
            'powerPlantId' => $geologicalPlantData->getPowerPlantId(),
            'soilType' => $geologicalPlantData->getSoilType()?->value,
            'seismicStability' => $geologicalPlantData->getSeismicStability(),
            'groundwaterLevel' => $geologicalPlantData->getGroundwaterLevel(),
            'waterProximity' => $geologicalPlantData->getWaterProximity(),
            'geologicalRiskScore' => $geologicalPlantData->getGeologicalRiskScore()
        ]);
    }

    /**
     * Actualizează datele geologice existente
     */
    public function update(GeologicalPlantData $geologicalPlantData): void {
        $statement = $this->pdo->prepare("
            UPDATE geological_data 
            SET 
                soil_type = :soilType, 
                seismic_stability = :seismicStability, 
                groundwater_level = :groundwaterLevel, 
                water_proximity = :waterProximity, 
                geological_risk_score = :geologicalRiskScore
            WHERE id = :id
        ");

        $statement->execute([
            'id' => $geologicalPlantData->getId(),
            'soilType' => $geologicalPlantData->getSoilType()?->value,
            'seismicStability' => $geologicalPlantData->getSeismicStability(),
            'groundwaterLevel' => $geologicalPlantData->getGroundwaterLevel(),
            'waterProximity' => $geologicalPlantData->getWaterProximity(),
            'geologicalRiskScore' => $geologicalPlantData->getGeologicalRiskScore()
        ]);

        $randuriModificate = $statement->rowCount();
        error_log("[DEBUG DB] ID centrala pentru update geological: " . $geologicalPlantData->getPowerPlantId());
        error_log("[DEBUG DB] Randuri modificate efectiv in geological_data: " . $randuriModificate);
    }
}