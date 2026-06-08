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
            ]; 
        }

        return $powerPlants; 
    }

    public function getPlantsByStatus(array $data): array { 
        $status = PlantStatus::from($data['status']); 
        $powerPlants = []; 

        $statement = $this->db->prepare("
            SELECT p.id, p.name, p.status,
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
            ]; 
        }
        
        return $powerPlants; 
    }

    public function findById(string $plantId) { 
        $statement = $this->db->prepare("
            SELECT p.id, p.name, p.status,
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
            PlantStatus::from($row['status'])
        );
    }
    
    public function save(Plant $plant): void { 
        $stmt = $this->db->prepare("
            INSERT INTO power_plants (
                id,
                name, 
                status
            ) VALUES (
                :id, 
                :name, 
                :status
            )
        "); 

        $stmt->execute([
            'id' => $plant->getId(),
            'name' => $plant->getName(),
            'status' => $plant->getStatus()->value
        ]);
    }

    public function updateStatus(array $data, string $plantId): void { 
        $status = PlantStatus::from($data['status']);
    
        $statement = $this->db->prepare("
            UPDATE power_plants SET status = :status WHERE id = :id
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
                status = :status
            WHERE id = :id
        "); 
    
        $stmt->execute([
            'id' => $plant->getId(),
            'name' => $plant->getName(),
            'status' => $plant->getStatus()->value
        ]);

        $randuriModificate = $stmt->rowCount();
        error_log("[DEBUG] ID cautat pentru update: " . $plant->getId());
        error_log("[DEBUG] Randuri modificate efectiv: " . $randuriModificate);
    }


    public function updateStatus(string $plantId, string $status): void {
        $stmt = $this->db->prepare("
            UPDATE power_plants 
            SET status = :status 
            WHERE id = :id
        ");
        
        $stmt->execute([
            'id' => $plantId,
            'status' => $status
        ]);
    }
}
