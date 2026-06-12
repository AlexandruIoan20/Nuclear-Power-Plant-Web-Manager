<?php 

require_once __DIR__ . '/../Entities/SensorTemplate.php';
require_once __DIR__ . '/../Entities/SensorType.php';
require_once __DIR__ . '/../Entities/ReactorSensor.php';
require_once __DIR__ . '/../Entities/SensorQuality.php'; 

class SensorRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function insertBulk(string $reactorId, array $templates): void {
        if (empty($templates)) {
            return;
        }

        $sensors = [];
        foreach ($templates as $t) {
            $sensors[] = new ReactorSensor(
                $reactorId,
                $t->getSensorCode(),
                $t->getSensorType(),
                null,
                $t->getDescription(),
                $t->getLocationZone(),
                $t->getUnitOfMeasure(),
                $t->getMeasurementField(),
                $t->getNormalMin(),
                $t->getNormalMax(),
                $t->getAlarmLow(),
                $t->getAlarmHigh(),
                $t->getAlertLow(),
                $t->getAlertHigh(),
                $t->getScramLow(),
                $t->getScramHigh()
            );
        }

        $columns = [
            'id', 'reactor_id', 'sensor_code', 'sensor_type',
            'description', 'location_zone', 'unit_of_measure', 'measurement_field',
            'normal_min', 'normal_max', 'alarm_low', 'alarm_high',
            'alert_low', 'alert_high', 'scram_low', 'scram_high',
        ];

        $placeholders = [];
        $values = [];

        foreach ($sensors as $s) {
            $valueGroup = [
                $s->getId(),
                $s->getReactorId(),
                $s->getSensorCode(),
                $s->getSensorType()->value,
                $s->getDescription(),
                $s->getLocationZone(),
                $s->getUnitOfMeasure(),
                $s->getMeasurementField(),
                $s->getNormalMin(),
                $s->getNormalMax(),
                $s->getAlarmLow(),
                $s->getAlarmHigh(),
                $s->getAlertLow(),
                $s->getAlertHigh(),
                $s->getScramLow(),
                $s->getScramHigh(),
            ];

            $placeholders[] = '(' . implode(', ', array_fill(0, count($valueGroup), '?')) . ')';
            $values = array_merge($values, $valueGroup);
        }

        $sql = 'INSERT INTO reactor_sensors (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $placeholders);

        $statement = $this->db->prepare($sql);
        $statement->execute($values);
    }

    public function findById(string $id): ?ReactorSensor {
        $statement = $this->db->prepare("SELECT * FROM reactor_sensors WHERE id = :id");
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if(!$row) return null;

        return $this->mapRowToEntity($row);
    }

    public function findByReactorId(string $reactorId): array { 
        $statement = $this->db->prepare("SELECT * FROM reactor_sensors WHERE reactor_id = :reactor_id"); 
        $statement->execute(["reactor_id" => $reactorId]); 
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapRowToEntity($row), $rows);
    }

    private function mapRowToEntity(array $row): ReactorSensor {
        return new ReactorSensor(
            $row['reactor_id'],
            $row['sensor_code'],
            SensorType::from($row['sensor_type']),
            $row['id'],
            $row['description'],
            $row['location_zone'],
            $row['unit_of_measure'],
            $row['measurement_field'],
            $row['normal_min'] !== null ? (float)$row['normal_min'] : null,
            $row['normal_max'] !== null ? (float)$row['normal_max'] : null,
            $row['alarm_low'] !== null ? (float)$row['alarm_low'] : null,
            $row['alarm_high'] !== null ? (float)$row['alarm_high'] : null,
            $row['alert_low'] !== null ? (float)$row['alert_low'] : null,
            $row['alert_high'] !== null ? (float)$row['alert_high'] : null,
            $row['scram_low'] !== null ? (float)$row['scram_low'] : null,
            $row['scram_high'] !== null ? (float)$row['scram_high'] : null,
            isset($row['status']) ? SensorQuality::from($row['status']) : SensorQuality::GOOD,
            $row['is_active'] ?? true,
            $row['last_calibration'],
            $row['calibration_due'],
            $row['current_value'] !== null ? (float)$row['current_value'] : null,
            $row['last_reading_at']
        );
    }

    public function updateCurrentValue(string $sensorId, float $value) { 
        $statement = $this->db->prepare("UPDATE reactor_sensors SET current_value = :current_value, last_reading_at = CURRENT_TIMESTAMP WHERE id = :sensor_id"); 
        $statement->execute([
            "current_value" => $value, 
            "sensor_id" => $sensorId
        ]); 
    }

    public function update(ReactorSensor $s): void {
        $sql = "UPDATE reactor_sensors SET 
                    reactor_id = :reactor_id,
                    sensor_code = :sensor_code,
                    sensor_type = :sensor_type,
                    description = :description,
                    location_zone = :location_zone,
                    unit_of_measure = :unit_of_measure,
                    measurement_field = :measurement_field,
                    normal_min = :normal_min,
                    normal_max = :normal_max,
                    alarm_low = :alarm_low,
                    alarm_high = :alarm_high,
                    alert_low = :alert_low,
                    alert_high = :alert_high,
                    scram_low = :scram_low,
                    scram_high = :scram_high,
                    status = :status,
                    is_active = :is_active,
                    last_calibration = :last_calibration,
                    calibration_due = :calibration_due,
                    current_value = :current_value,
                    last_reading_at = :last_reading_at
                WHERE id = :id";

        $params = $this->extractParameters($s);
        unset($params['created_at']);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);
    }

    public function save(ReactorSensor $s): void {
        $sql = "INSERT INTO reactor_sensors (
                    id, reactor_id, sensor_code, sensor_type,
                    description, location_zone, unit_of_measure,
                    measurement_field,
                    normal_min, normal_max, alarm_low, alarm_high,
                    alert_low, alert_high, scram_low, scram_high,
                    status, is_active, last_calibration, calibration_due,
                    current_value, last_reading_at, created_at
                ) VALUES (
                    :id, :reactor_id, :sensor_code, :sensor_type,
                    :description, :location_zone, :unit_of_measure,
                    :measurement_field,
                    :normal_min, :normal_max, :alarm_low, :alarm_high,
                    :alert_low, :alert_high, :scram_low, :scram_high,
                    :status, :is_active, :last_calibration, :calibration_due,
                    :current_value, :last_reading_at, :created_at
                )";

        $statement = $this->db->prepare($sql);
        $statement->execute($this->extractParameters($s));
    }

    public function delete(string $id): void {
        $stmt = $this->db->prepare("DELETE FROM reactor_sensors WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function extractParameters(ReactorSensor $s): array {
        return [
            'id' => $s->getId(),
            'reactor_id' => $s->getReactorId(),
            'sensor_code' => $s->getSensorCode(),
            'sensor_type' => $s->getSensorType()->value,
            'description' => $s->getDescription(),
            'location_zone' => $s->getLocationZone(),
            'unit_of_measure' => $s->getUnitOfMeasure(),
            'measurement_field' => $s->getMeasurementField(),
            'normal_min' => $s->getNormalMin(),
            'normal_max' => $s->getNormalMax(),
            'alarm_low' => $s->getAlarmLow(),
            'alarm_high' => $s->getAlarmHigh(),
            'alert_low' => $s->getAlertLow(),
            'alert_high' => $s->getAlertHigh(),
            'scram_low' => $s->getScramLow(),
            'scram_high' => $s->getScramHigh(),
            'status' => $s->getStatus()->value,
            'is_active' => $s->getIsActive() ? 1 : 0,
            'last_calibration' => $s->getLastCalibration(),
            'calibration_due' => $s->getCalibrationDue(),
            'current_value' => $s->getCurrentValue(),
            'last_reading_at' => $s->getLastReadingAt(),
            'created_at' => $s->getCreatedAt(),
        ];
    }
}