<?php

require_once __DIR__ . '/../Entities/BasicPlantData.php'; 

class BasicPlantRepository {
    private PDO $pdo; 

    public function __construct(PDO $pdo) { 
        $this->pdo = $pdo; 
    }

    public function findByPlantId(string $plantId) { 
        $statement = $this->pdo->prepare("SELECT * FROM basic_data WHERE power_plant_id = :plantId"); 
        $statement->execute([ 'plantId' => $plantId ]); 

        $row = $statement->fetch(PDO::FETCH_ASSOC); 
        if(!$row) { 
            return null; 
        }

        $basicPlantData = new BasicPlantData($row['power_plant_id'], $row['id'], $row['capacity_mw'], $row['construction_duration_years'], $row['description']); 
        return $basicPlantData; 
    }

    public function save(BasicPlantData $basicPlantData): void { 
        $statement = $this->pdo->prepare("
            INSERT INTO basic_data (
                id, 
                power_plant_id, 
                capacity_mw, 
                construction_duration_years, 
                description
            ) VALUES (
                :id, 
                :powerPlantId, 
                :capacity, 
                :constructionDurationYears, 
                :description 
            )
        "); 

        $statement->execute([
            'id' => $basicPlantData->getId(), 
            'powerPlantId' => $basicPlantData->getPowerPlantId(), 
            'capacity' => $basicPlantData->getCapacity(), 
            'constructionDurationYears' => $basicPlantData->getConstructionDurationYears(), 
            'description' => $basicPlantData->getDescription()
        ]); 
    }
}