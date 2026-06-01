<?php 

class TechnicalPlantRepository { 
    private PDO $pdo; 

    public function __construct(PDO $pdo) { 
        $this->pdo = $pdo; 
    }

    public function findByPlantId(string $plantId): ?TechnicalPlantData { 
        $statement = $this->pdo->prepare("SELECT * FROM technical_data WHERE power_plant_id = :plantId"); 
        $statement->execute(['plantId' => $plantId]); 

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $technicalPlantData = new TechnicalPlantData ( 
            $row['power_plant_id'], 
            $row['id'], 
            $row['number_of_reactors'], 
            $row['estimated_efficiency'], 
            $row['operational_risk_level']
        ); 

        $relationStatement = $this->pdo->prepare("SELECT * FROM reactor_schema JOIN reactor_plant_data ON reactor_schema.id = reactor_plant_data.reactor_schema_id 
            JOIN technical_data ON technical_data.id = reactor_plant_data.technical_data_id WHERE technical_data.power_plant_id = :plantId
        "); 
        $relationStatement->execute(['plantId' => $plantId]); 

        while($relationRow = $relationStatement->fetch(PDO::FETCH_ASSOC)) { 
            $currentReactorSchema = new ReactorSchema(
                $relationRow['id'], 
                ReactorType::from($relationRow['reactor_type']), 
                CoolingType::from($relationRow['cooling_type'])
            ); 

            $technicalPlantData->addReactorConfiguration($currentReactorSchema); 
        }

        return $technicalPlantData; 
    }

    public function save(TechnicalPlantData $technicalPlantData): void { 
        $statement = $this->pdo->prepare("INSERT INTO technical_data (
            id, 
            power_plant_id,
            number_of_reactors, 
            estimated_efficiency, 
            operational_risk_level
        ) VALUES (
            :id, 
            :power_plant_id,
            :number_of_reactors, 
            :estimated_efficiency, 
            :operational_risk_level
        )"); 
    
        $statement->execute([
            'id' => $technicalPlantData->getId(), 
            'power_plant_id' => $technicalPlantData->getPowerPlantId(), 
            'number_of_reactors' => $technicalPlantData->getNumberOfReactors(), 
            'estimated_efficiency' => $technicalPlantData->getEstimatedEfficiency(), 
            'operational_risk_level' => $technicalPlantData->getOperationalRiskLevel()
        ]);
    
        $schemaStatement = $this->pdo->prepare("
            INSERT INTO reactor_schema (
                id, 
                reactor_type, 
                cooling_type
            ) VALUES (
                :id, 
                :reactor_type, 
                :cooling_type
            )
        ");
    
        $relationalStatement = $this->pdo->prepare("
            INSERT INTO reactor_plant_data (
                technical_data_id, 
                reactor_schema_id
            ) VALUES (
                :technical_data_id,
                :reactor_schema_id  
            )
        "); 
    
        $reactorConfigurations = $technicalPlantData->getReactorConfigurations(); 
        foreach($reactorConfigurations as $reactorConfiguration) { 
            $schemaStatement->execute([
                'id' => $reactorConfiguration->getId(),
                'reactor_type' => $reactorConfiguration->getType()->value,
                'cooling_type' => $reactorConfiguration->getCooling()->value
            ]);
    
            $relationalStatement->execute([
                'technical_data_id' => $technicalPlantData->getId(),  
                'reactor_schema_id' => $reactorConfiguration->getId() 
            ]); 
        }
    }

    public function update(TechnicalPlantData $technicalPlantData): void { 
        $statement = $this->pdo->prepare("
            UPDATE technical_data 
            SET 
                number_of_reactors = :number_of_reactors, 
                estimated_efficiency = :estimated_efficiency, 
                operational_risk_level = :operational_risk_level
            WHERE id = :id
        "); 

        $statement->execute([
            'id' => $technicalPlantData->getId(), 
            'number_of_reactors' => $technicalPlantData->getNumberOfReactors(), 
            'estimated_efficiency' => $technicalPlantData->getEstimatedEfficiency(), 
            'operational_risk_level' => $technicalPlantData->getOperationalRiskLevel()
        ]);

        $currentPlantData = $this->findByPlantId($technicalPlantData->getPowerPlantId()); 

        $currentReactorConfigurations = $currentPlantData->getReactorConfigurations();  
        $newReactorConfigurations = $technicalPlantData->getReactorConfigurations(); 

        // Tabele de dispersie => cautare in O(1)
        $dictCurrent = []; 
        foreach($currentReactorConfigurations as $currentConfig) { 
            $dictCurrent[$currentConfig->getId()] = $currentConfig; 
        }

        $dictNew = []; 
        foreach($newReactorConfigurations as $newConfig) { 
            $dictNew[$newConfig->getId()] = $newConfig; 
        }

        $deleteConfigurations = array_diff_key($dictCurrent, $dictNew); 
        $insertConfigurations = array_diff_key($dictNew, $dictCurrent); 

        $deleteRelationStatement = $this->pdo->prepare("
            DELETE FROM reactor_plant_data WHERE technical_data_id = :technical_data_id AND reactor_schema_id = :reactor_schema_id; 
        "); 

        $insertRelationStatement = $this->pdo->prepare("
            INSERT INTO reactor_plant_data (
                technical_data_id, 
                reactor_schema_id
            ) VALUES (
                :technical_data_id, 
                :reactor_schema_id 
            )
        "); 

        foreach($deleteConfigurations as $config) { 
            $deleteRelationStatement->execute([ 
                'technical_data_id' => $technicalPlantData->getId(), 
                'reactor_schema_id' => $config->getId()
            ]); 
        }

        foreach($insertConfigurations as $config) { 
            $insertRelationStatement->execute([ 
                'technical_data_id' => $technicalPlantData->getId(), 
                'reactor_schema_id' => $config->getId()
            ]); 

        }
    }
}