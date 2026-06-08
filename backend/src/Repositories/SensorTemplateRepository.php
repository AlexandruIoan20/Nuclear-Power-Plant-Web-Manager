<?php 

require_once __DIR__ . '/../Entities/ReactorType.php'; 
require_once __DIR__ . '/../Entities/SensorTemplate.php'; 
require_once __DIR__ . '/../Entities/SensorType.php'; 

class SensorTemplateRepository { 
    private PDO $db; 

    public function __construct (PDO $db) { 
        $this->db = $db; 
    }

    public function findByReactorType(ReactorType $type): array { 
        $statement = $this->db->prepare("SELECT * FROM sensor_templates WHERE reactor_type = :reactor_type"); 
        $statement->execute(["reactor_type" => $type->value]); 
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC); 

        return array_map(fn($row) => $this->mapRowToEntity($row), $rows); 
    }

    private function mapRowToEntity(array $row): SensorTemplate {
        return new SensorTemplate(
            ReactorType::from($row['reactor_type']),
            $row['sensor_code'],
            SensorType::from($row['sensor_type']),
            $row['description'],
            $row['id'],
            $row['location_zone'],
            $row['unit_of_measure'],
            $row['normal_min'] !== null ? (float)$row['normal_min'] : null,
            $row['normal_max'] !== null ? (float)$row['normal_max'] : null,
            $row['alarm_low'] !== null ? (float)$row['alarm_low'] : null,
            $row['alarm_high'] !== null ? (float)$row['alarm_high'] : null,
            $row['alert_low'] !== null ? (float)$row['alert_low'] : null,
            $row['alert_high'] !== null ? (float)$row['alert_high'] : null,
            $row['scram_low'] !== null ? (float)$row['scram_low'] : null,
            $row['scram_high'] !== null ? (float)$row['scram_high'] : null
        );
    }
}