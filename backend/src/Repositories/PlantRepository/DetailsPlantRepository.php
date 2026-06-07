<?php 

class DetailsPlantRepository {
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function findAll(): array { 
        $statement = $this->db->query("SELECT * FROM power_plants"); 
        $powerPlants = []; 

        while($row = $statement->fetch(PDO::FETCH_ASSOC)) { 
            $powerPlants[] = new Plant($row['country'], $row['id'], $row['name'], PlantStatus::from($row['status']), 
            $row['latitude'], $row['longitude']); 
        }

        return $powerPlants; 
    }

    public function findById(string $plantId) { 
        $statement = $this->db->prepare("SELECT * FROM power_plants WHERE id = :plantId"); 
        $statement->execute([ 
            'plantId' => $plantId
        ]); 

        $row = $statement->fetch(PDO::FETCH_ASSOC); 
        if(!$row) {
            return null; 
        }

        return new Plant(
            $row['country'],
            $row['id'],
            $row['name'],
            PlantStatus::from($row['status']),
            $row['latitude'],
            $row['longitude']
        );
    }
    
    public function save(Plant $plant): void { 
        $stmt = $this->db->prepare("
            INSERT INTO power_plants (
                id,
                name, 
                country, 
                latitude, 
                longitude,
                status
            ) VALUES (
                :id, 
                :name, 
                :country, 
                :latitude, 
                :longitude,
                :status
            )
        "); 

        $latVal = $plant->getLatitude() !== null ? number_format($plant->getLatitude(), 6, '.', '') : null;
        $lonVal = $plant->getLongitude() !== null ? number_format($plant->getLongitude(), 6, '.', '') : null;

        $stmt->execute([
            'id' => $plant->getId(),
            'name' => $plant->getName(),
            'country' => $plant->getCountry(),
            'latitude' => $latVal,
            'longitude' => $lonVal,
            'status' => $plant->getStatus()->value
        ]);
    }

    public function update(Plant $plant): void {
        $stmt = $this->db->prepare("
            UPDATE power_plants 
            SET 
                name = :name, 
                country = :country, 
                latitude = :latitude, 
                longitude = :longitude,
                status = :status
            WHERE id = :id
        "); 
    
        $latVal = $plant->getLatitude() !== null ? number_format($plant->getLatitude(), 6, '.', '') : null;
        $lonVal = $plant->getLongitude() !== null ? number_format($plant->getLongitude(), 6, '.', '') : null;

        $stmt->execute([
            'id' => $plant->getId(),
            'name' => $plant->getName(),
            'country' => $plant->getCountry(),
            'latitude' => $latVal,
            'longitude' => $lonVal,
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