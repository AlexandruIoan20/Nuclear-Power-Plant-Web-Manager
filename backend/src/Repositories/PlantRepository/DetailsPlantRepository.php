<?php 

require_once __DIR__ . '../../../Entities/PlantStatus.php'; 

class DetailsPlantRepository {
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function findAll(): array { 
        $statement = $this->db->query("
            SELECT p.id, p.name, p.status,
                   p.created_by, p.created_at, p.updated_at,
                   g.country, g.latitude, g.longitude
            FROM power_plants p
            LEFT JOIN geological_data g ON p.id = g.power_plant_id
        "); 
        $powerPlants = []; 

        while($row = $statement->fetch(PDO::FETCH_ASSOC)) { 
            $powerPlants[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'country' => $row['country'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
                'status' => $row['status'],
                'created_by' => $row['created_by'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ]; 
        }

        return $powerPlants; 
    }

    public function getPlantsByStatus(array $data): array { 
        $status = PlantStatus::from($data['status']); 
        $powerPlants = []; 

        $statement = $this->db->prepare("
            SELECT p.id, p.name, p.status,
                   p.created_by, p.created_at, p.updated_at,
                   g.country, g.latitude, g.longitude
            FROM power_plants p
            LEFT JOIN geological_data g ON p.id = g.power_plant_id
            WHERE p.status = :status
        "); 
        $statement->execute([ "status" => $status->value ]); 

        while($row = $statement->fetch(PDO::FETCH_ASSOC)) { 
            $powerPlants[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'country' => $row['country'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
                'status' => $row['status'],
                'created_by' => $row['created_by'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ]; 
        }
        
        return $powerPlants; 
    }

    public function findById(string $plantId) { 
        $statement = $this->db->prepare("
            SELECT p.id, p.name, p.status,
                   p.created_by, p.created_at, p.updated_at,
                   g.country, g.latitude, g.longitude
            FROM power_plants p
            LEFT JOIN geological_data g ON p.id = g.power_plant_id
            WHERE p.id = :plantId
        "); 
        $statement->execute([ 
            'plantId' => $plantId
        ]); 

        $row = $statement->fetch(PDO::FETCH_ASSOC); 
        if(!$row) {
            return null; 
        }

        return new Plant(
            $row['id'],
            $row['name'],
            PlantStatus::from($row['status']),
            $row['created_by'],
            $row['created_at'],
            $row['updated_at']
        );
    }
    
    public function save(Plant $plant): void { 
        $stmt = $this->db->prepare("
            INSERT INTO power_plants (
                id,
                name, 
                status,
                created_by,
                created_at,
                updated_at
            ) VALUES (
                :id, 
                :name, 
                :status,
                :created_by,
                NOW(),
                NOW()
            )
        "); 

        $stmt->execute([
            'id' => $plant->getId(),
            'name' => $plant->getName(),
            'status' => $plant->getStatus()->value,
            'created_by' => $plant->getCreatedBy()
        ]);
    }

    public function updateStatus(array $data, string $plantId): void { 
        $status = PlantStatus::from($data['status']);
    
        $statement = $this->db->prepare("
            UPDATE power_plants SET status = :status, updated_at = NOW() WHERE id = :id
        "); 
    
        $statement->execute([ 
            "status" => $status->value, 
            "id" => $plantId
        ]); 
    }

    public function update(Plant $plant): void {
        $stmt = $this->db->prepare("
            UPDATE power_plants 
            SET 
                name = :name,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        "); 
    
        $stmt->execute([
            'id' => $plant->getId(),
            'name' => $plant->getName(),
            'status' => $plant->getStatus()->value
        ]);

        $randuriModificate = $stmt->rowCount();
        LogService::instance()->debug("[DEBUG] ID cautat pentru update: " . $plant->getId());
        LogService::instance()->debug("[DEBUG] Randuri modificate efectiv: " . $randuriModificate);
    }
}
