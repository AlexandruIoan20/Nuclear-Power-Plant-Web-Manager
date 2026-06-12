<?php 

require_once __DIR__ . '/../Entities/Reactor.php'; 
require_once __DIR__ . '/../Entities/ReactorType.php'; 
require_once __DIR__ . '/../Entities/CoolingType.php'; 
require_once __DIR__ . '/../Entities/ReactorOperationalStatus.php'; 

class ReactorRepository { 
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function findById(string $id): ?Reactor { 
        $statement = $this->db->prepare("SELECT * FROM reactor WHERE id = :id"); 
        $statement->execute(['id' => $id]); 
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if(!$row) return null;

        return $this->mapRowToEntity($row); 
    }

    public function findByPlantId(string $plantId): array { 
        $statement = $this->db->prepare("SELECT * FROM reactor WHERE power_plant_id = :plantId"); 
        $statement->execute(['plantId' => $plantId ]); 
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC); 

        return array_map(fn($row) => $this->mapRowToEntity($row), $rows); 
    }

    public function findAll(): array {
        $statement = $this->db->query("SELECT * FROM reactor ORDER BY created_at DESC");
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->mapRowToEntity($row), $rows);
    }

    public function save(Reactor $r): void {
        $sql = "INSERT INTO reactor (
                    id, power_plant_id, reactor_code, reactor_type, cooling_type, operational_status,
                    thermal_power_mw, electrical_power_mw, fuel_cycle_days, current_cycle_day,
                    wear_index, design_lifetime_yr, commissioning_date, first_criticality,
                    last_inspection_at, next_planned_outage, description, created_at
                ) VALUES (
                    :id, :power_plant_id, :reactor_code, :reactor_type, :cooling_type, :operational_status,
                    :thermal_power_mw, :electrical_power_mw, :fuel_cycle_days, :current_cycle_day,
                    :wear_index, :design_lifetime_yr, :commissioning_date, :first_criticality,
                    :last_inspection_at, :next_planned_outage, :description, :created_at
                )";

        $statement = $this->db->prepare($sql);
        $statement->execute($this->extractParameters($r));
    }

    public function update(Reactor $r): void {
        $sql = "UPDATE reactor SET 
                    power_plant_id = :power_plant_id,
                    reactor_code = :reactor_code,
                    reactor_type = :reactor_type,
                    cooling_type = :cooling_type,
                    operational_status = :operational_status,
                    thermal_power_mw = :thermal_power_mw,
                    electrical_power_mw = :electrical_power_mw,
                    fuel_cycle_days = :fuel_cycle_days,
                    current_cycle_day = :current_cycle_day,
                    wear_index = :wear_index,
                    design_lifetime_yr = :design_lifetime_yr,
                    commissioning_date = :commissioning_date,
                    first_criticality = :first_criticality,
                    last_inspection_at = :last_inspection_at,
                    next_planned_outage = :next_planned_outage,
                    description = :description
                WHERE id = :id";

        $params = $this->extractParameters($r);
        unset($params['created_at']);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);
    }

    public function delete(string $id): void {
        $stmt = $this->db->prepare("DELETE FROM reactor WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function mapRowToEntity(array $row): Reactor { 
        return new Reactor ( 
            $row['power_plant_id'], 
            $row['reactor_code'], 
            ReactorType::from($row['reactor_type']), 
            CoolingType::from($row['cooling_type']), 
            $row['id'], 
            ReactorOperationalStatus::from($row['operational_status']), 
            $row['thermal_power_mw'] !== null ? (float)$row['thermal_power_mw'] : null,
            $row['electrical_power_mw'] !== null ? (float)$row['electrical_power_mw'] : null, 
            $row['fuel_cycle_days'] !== null ? (int)$row['fuel_cycle_days'] : null, 
            $row['current_cycle_day'] !== null ? (int)$row['current_cycle_day'] : null, 
            $row['wear_index'] !== null ? (float)$row['wear_index'] : null, 
            $row['design_lifetime_yr'] !== null ? (int)$row['design_lifetime_yr'] : null,
            $row['commissioning_date'],
            $row['first_criticality'],
            $row['last_inspection_at'],
            $row['next_planned_outage'],
            $row['description'],
            $row['created_at']
        );
    }

    private function extractParameters(Reactor $r): array {
        return [
            'id' => $r->getId(),
            'power_plant_id' => $r->getPowerPlantId(),
            'reactor_code' => $r->getReactorCode(),
            'reactor_type' => $r->getReactorType()->value,
            'cooling_type' => $r->getCoolingType()->value,
            'operational_status' => $r->getOperationalStatus()->value,
            'thermal_power_mw' => $r->getThermalPowerMw(),
            'electrical_power_mw' => $r->getElectricalPowerMw(),
            'fuel_cycle_days' => $r->getFuelCycleDays(),
            'current_cycle_day' => $r->getCurrentCycleDay(),
            'wear_index' => $r->getWearIndex(),
            'design_lifetime_yr' => $r->getDesignLifetimeYr(),
            'commissioning_date' => $r->getCommissioningDate(),
            'first_criticality' => $r->getFirstCriticality(),
            'last_inspection_at' => $r->getLastInspectionAt(),
            'next_planned_outage' => $r->getNextPlannedOutage(),
            'description' => $r->getDescription(),
            'created_at' => $r->getCreatedAt()
        ];
    }
}