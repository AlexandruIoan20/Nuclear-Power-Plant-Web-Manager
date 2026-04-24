<?php 

class PlantRepository {
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
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

        $stmt->execute([ 
            'id' => $plant->getId(), 
            'name' => $plant->getName(), 
            'country' => $plant->getCountry(), 
            'latitude' => $plant->getLatitude(), 
            'longitude' => $plant->getLongitude(), 
            'status' => $plant->getStatus()->value 
        ]);
    }
}