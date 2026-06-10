<?php 

require_once __DIR__ . '/../Entities/Measurements.php'; 

class MeasurementsRepository { 
    private PDO $db; 

    public function __construct (PDO $db) { 
        $this->db = $db; 
    }

    public function save(Measurement $m): void {
        $sql = "INSERT INTO measurements (
                    id, reactor_id, timestamp,
                    power_percent, neutron_flux, reactivity_pcm, reactor_period_sec,
                    temp_fuel_center, temp_coolant_in, temp_coolant_out, temp_moderator,
                    pressure, flow_rate_primary, flow_rate_secondary,
                    steam_pressure, steam_flow_rate, feedwater_temp,
                    radiation, activity_primary, dose_rate_control_room,
                    dose_rate_reactor_bldg, airborne_activity,
                    fuel_burnup_mwd_t, efficiency, wear_delta,
                    level_reactor_vessel
                ) VALUES (
                    :id, :reactor_id, :timestamp,
                    :power_percent, :neutron_flux, :reactivity_pcm, :reactor_period_sec,
                    :temp_fuel_center, :temp_coolant_in, :temp_coolant_out, :temp_moderator,
                    :pressure, :flow_rate_primary, :flow_rate_secondary,
                    :steam_pressure, :steam_flow_rate, :feedwater_temp,
                    :radiation, :activity_primary, :dose_rate_control_room,
                    :dose_rate_reactor_bldg, :airborne_activity,
                    :fuel_burnup_mwd_t, :efficiency, :wear_delta,
                    :level_reactor_vessel
                )";
 
        $statement = $this->db->prepare($sql);
        $statement->execute($this->extractParameters($m));
    }

    public function findLatestByReactorId(string $reactorId): ?Measurement {
        $statement = $this->db->prepare("
            SELECT * FROM measurements
            WHERE reactor_id = :reactor_id
            ORDER BY timestamp DESC
            LIMIT 1
        ");
        $statement->execute(['reactor_id' => $reactorId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
 
        if (!$row) return null;
 
        return $this->mapRowToEntity($row);
    }

    public function findByReactorIdSince(string $reactorId, string $since): array {
        $statement = $this->db->prepare("
            SELECT * FROM measurements
            WHERE reactor_id = :reactor_id
              AND timestamp >= :since
            ORDER BY timestamp ASC
        ");
        $statement->execute(['reactor_id' => $reactorId, 'since' => $since]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
 
        return array_map(fn($row) => $this->mapRowToEntity($row), $rows);
    }


    private function mapRowToEntity(array $row): Measurement {
        return new Measurement(
            $row['reactor_id'],
            $row['id'],
            $row['timestamp'],
            $row['power_percent']          !== null ? (float)$row['power_percent']          : null,
            $row['neutron_flux']           !== null ? (float)$row['neutron_flux']            : null,
            $row['reactivity_pcm']         !== null ? (float)$row['reactivity_pcm']          : null,
            $row['reactor_period_sec']     !== null ? (float)$row['reactor_period_sec']      : null,
            $row['temp_fuel_center']       !== null ? (float)$row['temp_fuel_center']        : null,
            $row['temp_coolant_in']        !== null ? (float)$row['temp_coolant_in']         : null,
            $row['temp_coolant_out']       !== null ? (float)$row['temp_coolant_out']        : null,
            $row['temp_moderator']         !== null ? (float)$row['temp_moderator']          : null,
            $row['pressure']               !== null ? (float)$row['pressure']                : null,
            $row['flow_rate_primary']      !== null ? (float)$row['flow_rate_primary']       : null,
            $row['flow_rate_secondary']    !== null ? (float)$row['flow_rate_secondary']     : null,
            $row['steam_pressure']         !== null ? (float)$row['steam_pressure']          : null,
            $row['steam_flow_rate']        !== null ? (float)$row['steam_flow_rate']         : null,
            $row['feedwater_temp']         !== null ? (float)$row['feedwater_temp']          : null,
            $row['radiation']              !== null ? (float)$row['radiation']               : null,
            $row['activity_primary']       !== null ? (float)$row['activity_primary']        : null,
            $row['dose_rate_control_room'] !== null ? (float)$row['dose_rate_control_room']  : null,
            $row['dose_rate_reactor_bldg'] !== null ? (float)$row['dose_rate_reactor_bldg']  : null,
            $row['airborne_activity']      !== null ? (float)$row['airborne_activity']       : null,
            $row['fuel_burnup_mwd_t']      !== null ? (float)$row['fuel_burnup_mwd_t']       : null,
            $row['efficiency']             !== null ? (float)$row['efficiency']              : null,
            $row['wear_delta']             !== null ? (float)$row['wear_delta']              : null,
            $row['level_reactor_vessel']   !== null ? (float)$row['level_reactor_vessel']    : null,
        );
    }
 
    private function extractParameters(Measurement $m): array {
        return [
            'id'                    => $m->getId(),
            'reactor_id'            => $m->getReactorId(),
            'timestamp'             => $m->getTimestamp(),
            'power_percent'         => $m->getPowerPercent(),
            'neutron_flux'          => $m->getNeutronFlux(),
            'reactivity_pcm'        => $m->getReactivityPcm(),
            'reactor_period_sec'    => $m->getReactorPeriodSec(),
            'temp_fuel_center'      => $m->getTempFuelCenter(),
            'temp_coolant_in'       => $m->getTempCoolantIn(),
            'temp_coolant_out'      => $m->getTempCoolantOut(),
            'temp_moderator'        => $m->getTempModerator(),
            'pressure'              => $m->getPressure(),
            'flow_rate_primary'     => $m->getFlowRatePrimary(),
            'flow_rate_secondary'   => $m->getFlowRateSecondary(),
            'steam_pressure'        => $m->getSteamPressure(),
            'steam_flow_rate'       => $m->getSteamFlowRate(),
            'feedwater_temp'        => $m->getFeedwaterTemp(),
            'radiation'             => $m->getRadiation(),
            'activity_primary'      => $m->getActivityPrimary(),
            'dose_rate_control_room'=> $m->getDoseRateControlRoom(),
            'dose_rate_reactor_bldg'=> $m->getDoseRateReactorBldg(),
            'airborne_activity'     => $m->getAirborneActivity(),
            'fuel_burnup_mwd_t'     => $m->getFuelBurnupMwdT(),
            'efficiency'            => $m->getEfficiency(),
            'wear_delta'            => $m->getWearDelta(),
            'level_reactor_vessel'  => $m->getLevelReactorVessel(),
        ];
    }
}