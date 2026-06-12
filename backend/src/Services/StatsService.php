<?php

class StatsService {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        return [
            'plants'   => $this->plantStats(),
            'reactors' => $this->reactorStats(),
            'sensors'  => $this->sensorStats(),
            'alerts'   => $this->alertStats(),
        ];
    }

    private function plantStats(): array {
        $total = $this->db->query("SELECT COUNT(*) FROM power_plants")->fetchColumn();

        $byStatus = $this->db->query("SELECT status, COUNT(*) as cnt FROM power_plants GROUP BY status")->fetchAll();
        $statusMap = [];
        foreach ($byStatus as $row) {
            $statusMap[$row['status']] = (int)$row['cnt'];
        }

        $byCountry = $this->db->query("
            SELECT COALESCE(g.country, 'N/A') as country, COUNT(*) as cnt
            FROM power_plants p
            LEFT JOIN geological_data g ON g.power_plant_id = p.id
            GROUP BY g.country
            ORDER BY cnt DESC
        ")->fetchAll();

        $monthly = $this->db->query("
            SELECT TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as cnt
            FROM power_plants
            GROUP BY month
            ORDER BY month
        ")->fetchAll();

        $avgs = $this->db->query("
            SELECT
                AVG(estimated_efficiency) as avg_efficiency,
                AVG(operational_risk_level) as avg_risk
            FROM technical_data
        ")->fetch();

        return [
            'total'          => (int)$total,
            'byStatus'       => $statusMap,
            'byCountry'      => $byCountry,
            'createdByMonth' => $monthly,
            'avgEfficiency'  => $avgs ? round((float)$avgs['avg_efficiency'], 2) : null,
            'avgRisk'        => $avgs ? round((float)$avgs['avg_risk'], 2) : null,
        ];
    }

    private function reactorStats(): array {
        $total = $this->db->query("SELECT COUNT(*) FROM reactor")->fetchColumn();

        $byType = $this->db->query("SELECT reactor_type, COUNT(*) as cnt FROM reactor GROUP BY reactor_type ORDER BY cnt DESC")->fetchAll();

        $byCooling = $this->db->query("SELECT cooling_type, COUNT(*) as cnt FROM reactor GROUP BY cooling_type ORDER BY cnt DESC")->fetchAll();

        $byStatus = $this->db->query("SELECT operational_status, COUNT(*) as cnt FROM reactor GROUP BY operational_status ORDER BY cnt DESC")->fetchAll();

        $avgs = $this->db->query("
            SELECT
                AVG(wear_index) as avg_wear,
                AVG(thermal_power_mw) as avg_thermal_mw,
                AVG(electrical_power_mw) as avg_electrical_mw
            FROM reactor
        ")->fetch();

        return [
            'total'             => (int)$total,
            'byType'            => $byType,
            'byCooling'         => $byCooling,
            'byStatus'          => $byStatus,
            'avgWear'           => $avgs ? round((float)$avgs['avg_wear'], 4) : null,
            'avgThermalMw'      => $avgs ? round((float)$avgs['avg_thermal_mw'], 2) : null,
            'avgElectricalMw'   => $avgs ? round((float)$avgs['avg_electrical_mw'], 2) : null,
        ];
    }

    private function sensorStats(): array {
        $total = $this->db->query("SELECT COUNT(*) FROM reactor_sensors")->fetchColumn();

        $byType = $this->db->query("SELECT sensor_type, COUNT(*) as cnt FROM reactor_sensors GROUP BY sensor_type ORDER BY cnt DESC")->fetchAll();

        $byStatus = $this->db->query("SELECT status, COUNT(*) as cnt FROM reactor_sensors GROUP BY status ORDER BY cnt DESC")->fetchAll();

        $active = $this->db->query("SELECT COUNT(*) FROM reactor_sensors WHERE is_active = TRUE")->fetchColumn();

        return [
            'total'       => (int)$total,
            'byType'      => $byType,
            'byStatus'    => $byStatus,
            'activeCount' => (int)$active,
        ];
    }

    private function alertStats(): array {
        $total = $this->db->query("SELECT COUNT(*) FROM reactor_alerts")->fetchColumn();

        $bySeverity = $this->db->query("SELECT severity, COUNT(*) as cnt FROM reactor_alerts GROUP BY severity ORDER BY cnt DESC")->fetchAll();

        $byType = $this->db->query("SELECT type, COUNT(*) as cnt FROM reactor_alerts GROUP BY type ORDER BY cnt DESC")->fetchAll();

        $daily = $this->db->query("
            SELECT TO_CHAR(created_at, 'YYYY-MM-DD') as day, COUNT(*) as cnt
            FROM reactor_alerts
            WHERE created_at >= NOW() - INTERVAL '30 days'
            GROUP BY day
            ORDER BY day
        ")->fetchAll();

        return [
            'total'      => (int)$total,
            'bySeverity' => $bySeverity,
            'byType'     => $byType,
            'last30days' => $daily,
        ];
    }

    public function getMeasurements(?string $reactorId = null, int $hours = 24): array {
        $interval = $hours . ' hours';
        if ($reactorId) {
            $stmt = $this->db->prepare("
                SELECT hour, reactor_id, samples_count,
                       power_percent_avg, temp_coolant_out_avg,
                       pressure_avg, neutron_flux_avg, efficiency_avg
                FROM measurements_hourly
                WHERE reactor_id = :reactor_id
                  AND hour >= NOW() - :interval::INTERVAL
                ORDER BY hour
            ");
            $stmt->execute(['reactor_id' => $reactorId, 'interval' => $interval]);
        } else {
            $stmt = $this->db->prepare("
                SELECT hour, reactor_id, samples_count,
                       power_percent_avg, temp_coolant_out_avg,
                       pressure_avg, neutron_flux_avg, efficiency_avg
                FROM measurements_hourly
                WHERE hour >= NOW() - :interval::INTERVAL
                ORDER BY hour
            ");
            $stmt->execute(['interval' => $interval]);
        }
        return $stmt->fetchAll();
    }
}
