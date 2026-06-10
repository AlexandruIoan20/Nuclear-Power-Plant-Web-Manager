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
            $row['operational_risk_level'],
            [],
            $row['created_at'],
            $row['updated_at']
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
        try {
            LogService::instance()->info("[saveTechnicalData] START");
            LogService::instance()->info("[saveTechnicalData] TechnicalPlantData: " . print_r($technicalPlantData, true));
    
            $this->pdo->beginTransaction();
            LogService::instance()->info("[saveTechnicalData] Transaction started");
    
            $statement = $this->pdo->prepare("INSERT INTO technical_data (
                    id, power_plant_id, number_of_reactors, estimated_efficiency, operational_risk_level,
                    created_at, updated_at
                ) VALUES (
                    :id, :power_plant_id, :number_of_reactors, :estimated_efficiency, :operational_risk_level,
                    NOW(), NOW()
                )");
    
            $insertParams = [
                'id' => $technicalPlantData->getId(), 
                'power_plant_id' => $technicalPlantData->getPowerPlantId(), 
                'number_of_reactors' => $technicalPlantData->getNumberOfReactors(), 
                'estimated_efficiency' => $technicalPlantData->getEstimatedEfficiency(), 
                'operational_risk_level' => $technicalPlantData->getOperationalRiskLevel()
            ];
            LogService::instance()->info("[saveTechnicalData] INSERT technical_data params: " . print_r($insertParams, true));
    
            $statement->execute($insertParams);
            LogService::instance()->info("[saveTechnicalData] technical_data inserted, rowCount: " . $statement->rowCount());
    
            $reactorConfigurations = $technicalPlantData->getReactorConfigurations(); 
            LogService::instance()->info("[saveTechnicalData] reactorConfigurations count: " . count($reactorConfigurations));
            LogService::instance()->info("[saveTechnicalData] reactorConfigurations raw: " . print_r($reactorConfigurations, true));
    
            $groupedConfigurations = [];
            foreach ($reactorConfigurations as $index => $config) {
                $key = $config->getType()->value . '_' . $config->getCooling()->value;
                LogService::instance()->info("[saveTechnicalData] config[$index] key: $key | type: " . $config->getType()->value . " | cooling: " . $config->getCooling()->value);
    
                if (!isset($groupedConfigurations[$key])) {
                    $groupedConfigurations[$key] = [
                        'type' => $config->getType()->value,
                        'cooling' => $config->getCooling()->value,
                        'quantity' => 0
                    ];
                }
                $groupedConfigurations[$key]['quantity']++;
                LogService::instance()->info("[saveTechnicalData] config[$index] quantity for key '$key' now: " . $groupedConfigurations[$key]['quantity']);
            }
            LogService::instance()->info("[saveTechnicalData] groupedConfigurations final: " . print_r($groupedConfigurations, true));
    
            $relationalStatement = $this->pdo->prepare("
                    INSERT INTO reactor_plant_data (technical_data_id, reactor_schema_id, number_of_reactors)
                    SELECT :technical_data_id, id, :number_of_reactors 
                    FROM reactor_schema 
                    WHERE reactor_type = :reactor_type AND cooling_type = :cooling_type
            ");
    
            foreach ($groupedConfigurations as $key => $group) { 
                $relationalParams = [
                    'technical_data_id' => $technicalPlantData->getId(),
                    'reactor_type' => $group['type'],
                    'cooling_type' => $group['cooling'],
                    'number_of_reactors' => $group['quantity']
                ];
                LogService::instance()->info("[saveTechnicalData] INSERT reactor_plant_data for key '$key': " . print_r($relationalParams, true));
    
                $relationalStatement->execute($relationalParams); 
    
                $rowCount = $relationalStatement->rowCount();
                LogService::instance()->info("[saveTechnicalData] reactor_plant_data rowCount for key '$key': $rowCount");
    
                if ($rowCount === 0) {
                    LogService::instance()->error("[saveTechnicalData] ERROR - reactor_schema not found for type: " . $group['type'] . " | cooling: " . $group['cooling']);
                    throw new Exception("Configurația reactorului (" . $group['type'] . " - " . $group['cooling'] . ") nu există în catalog.");
                }
    
                LogService::instance()->info("[saveTechnicalData] reactor_plant_data inserted successfully for key '$key'");
            }
    
            $this->pdo->commit();
            LogService::instance()->info("[saveTechnicalData] Transaction committed - DONE");
    
        } catch (Exception $e) { 
            $this->pdo->rollBack();
            LogService::instance()->error("[saveTechnicalData] ROLLBACK triggered");
            LogService::instance()->error("[saveTechnicalData] Eroare la salvare: " . $e->getMessage());
            LogService::instance()->error("[saveTechnicalData] Stack trace: " . $e->getTraceAsString());
            throw new Exception("Eroare la salvarea datelor tehnice: " . $e->getMessage());
        }
    }

    public function update(TechnicalPlantData $technicalPlantData): void { 
        try {
            $this->pdo->beginTransaction();

            $statement = $this->pdo->prepare("
                UPDATE technical_data 
                SET 
                    number_of_reactors = :number_of_reactors, 
                    estimated_efficiency = :estimated_efficiency, 
                    operational_risk_level = :operational_risk_level,
                    updated_at = NOW()
                WHERE id = :id
            "); 

            $statement->execute([
                'id' => $technicalPlantData->getId(), 
                'number_of_reactors' => $technicalPlantData->getNumberOfReactors(), 
                'estimated_efficiency' => $technicalPlantData->getEstimatedEfficiency(), 
                'operational_risk_level' => $technicalPlantData->getOperationalRiskLevel()
            ]);

            $newReactorConfigurations = $technicalPlantData->getReactorConfigurations(); 
            $groupedConfigurations = [];

            foreach ($newReactorConfigurations as $config) {
                $key = $config->getType()->value . '_' . $config->getCooling()->value;
                
                if (!isset($groupedConfigurations[$key])) {
                    $groupedConfigurations[$key] = [
                        'type' => $config->getType()->value,
                        'cooling' => $config->getCooling()->value,
                        'quantity' => 0
                    ];
                }
                
                $groupedConfigurations[$key]['quantity']++;
            }

            $deleteRelationStatement = $this->pdo->prepare("
                DELETE FROM reactor_plant_data 
                WHERE technical_data_id = :technical_data_id
            "); 
            $deleteRelationStatement->execute([
                'technical_data_id' => $technicalPlantData->getId()
            ]);

            $insertRelationStatement = $this->pdo->prepare("
                INSERT INTO reactor_plant_data (technical_data_id, reactor_schema_id, number_of_reactors)
                SELECT :technical_data_id, id, :number_of_reactors 
                FROM reactor_schema 
                WHERE reactor_type = :reactor_type AND cooling_type = :cooling_type
            "); 

            foreach ($groupedConfigurations as $group) { 
                $insertRelationStatement->execute([ 
                    'technical_data_id' => $technicalPlantData->getId(), 
                    'reactor_type' => $group['type'],
                    'cooling_type' => $group['cooling'],
                    'number_of_reactors' => $group['quantity']
                ]); 

                if ($insertRelationStatement->rowCount() === 0) {
                    throw new Exception("Configurația reactorului (" . $group['type'] . " - " . $group['cooling'] . ") nu există în catalog.");
                }
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            LogService::instance()->error("[TechnicalPlantRepository] Eroare la actualizare: " . $e->getMessage());
            throw new Exception("Eroare la actualizarea datelor tehnice: " . $e->getMessage());
        }
    }

    public function getReactorSchemaByDetails(string $reactorType, string $coolingType): ReactorSchema { 
        $statement = $this->pdo->prepare( 
            "SELECT * FROM reactor_schema WHERE reactor_type = :reactorType AND cooling_type = :coolingType"
        ); 

        $statement->execute([
            ':reactorType' => $reactorType, 
            ':coolingType' => $coolingType, 
        ]); 

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Schema de reactor pentru tipul {$reactorType} cu răcirea {$coolingType} nu a fost găsită în catalog.");
        }

        return new ReactorSchema(
            $row['id'],
            ReactorType::from($row['reactor_type']),
            CoolingType::from($row['cooling_type'])
        );
    }

    public function getSchemasByTechnicalDataId(string $technicalDataId): array  {
        $statement = $this->pdo->prepare(
            "SELECT rs.id, rs.reactor_type, rs.cooling_type 
             FROM reactor_plant_data rpd 
             JOIN reactor_schema rs ON rpd.reactor_schema_id = rs.id 
             WHERE rpd.technical_data_id = :tech_id"
        ); 

        $statement->execute([
            ':tech_id' => $technicalDataId
        ]); 

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC); 
        $schemas = [];

        foreach ($rows as $row) {
            $schemas[] = new ReactorSchema(
                $row['id'],
                ReactorType::from($row['reactor_type']),
                CoolingType::from($row['cooling_type'])
            );
        }

        return $schemas;
    }
}
