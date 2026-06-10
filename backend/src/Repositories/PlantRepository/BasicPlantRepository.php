<?php

require_once __DIR__ . '/../../Entities/BasicPlantData.php'; 
require_once __DIR__ . '/../../Services/LogService.php';

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

        $basicPlantData = new BasicPlantData(
            $row['power_plant_id'],
            $row['id'],
            $row['capacity_mw'],
            $row['construction_duration_years'],
            $row['description'],
            $row['created_at'],
            $row['updated_at']
        ); 
        return $basicPlantData; 
    }

    public function save(BasicPlantData $basicPlantData): void { 
        $statement = $this->pdo->prepare("
            INSERT INTO basic_data (
                id, 
                power_plant_id, 
                capacity_mw, 
                construction_duration_years, 
                description,
                created_at,
                updated_at
            ) VALUES (
                :id, 
                :powerPlantId, 
                :capacity, 
                :constructionDurationYears, 
                :description,
                NOW(),
                NOW()
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

    public function update(BasicPlantData $basicPlantData): void {
        $statement = $this->pdo->prepare("
            UPDATE basic_data 
            SET 
                capacity_mw = :capacity, 
                construction_duration_years = :constructionDurationYears, 
                description = :description,
                updated_at = NOW()
            WHERE id = :id
        "); 
    
        $statement->execute([ 
            'id' => $basicPlantData->getId(), 
            'capacity' => $basicPlantData->getCapacity(), 
            'constructionDurationYears' => $basicPlantData->getConstructionDurationYears(), 
            'description' => $basicPlantData->getDescription()
        ]);

        $randuriModificate = $statement->rowCount();
        LogService::instance()->debug("ID centrala cautata pentru update basics: " . $basicPlantData->getPowerPlantId());
        LogService::instance()->debug("Randuri modificate efectiv in basic_data: " . $randuriModificate);
    }
}
