<?php

require_once __DIR__ . '/../../Repositories/PlantRepository/DetailsPlantRepository.php';
require_once __DIR__ . '/../../Repositories/PlantRepository/BasicPlantRepository.php';
require_once __DIR__ . '/../../Repositories/PlantRepository/GeologicalPlantRepository.php';
require_once __DIR__ . '/../../Repositories/PlantRepository/TechnicalPlantRepository.php';
require_once __DIR__ . '/../../Repositories/ReactorRepository.php';
require_once __DIR__ . '/../../Repositories/SensorRepository.php';
require_once __DIR__ . '/../../Helpers/generateUUID.php';

class PlantExportImportService {
    private DetailsPlantRepository $detailsPlantRepo;
    private BasicPlantRepository $basicPlantRepo;
    private GeologicalPlantRepository $geologicalPlantRepo;
    private TechnicalPlantRepository $technicalPlantRepo;
    private ReactorRepository $reactorRepo;
    private SensorRepository $sensorRepo;
    public function __construct(private PDO $db) {
        $this->detailsPlantRepo = new DetailsPlantRepository($this->db);
        $this->basicPlantRepo = new BasicPlantRepository($this->db);
        $this->geologicalPlantRepo = new GeologicalPlantRepository($this->db);
        $this->technicalPlantRepo = new TechnicalPlantRepository($this->db);
        $this->reactorRepo = new ReactorRepository($this->db);
        $this->sensorRepo = new SensorRepository($this->db);
    }

    // 

    public function exportPlantJson(string $plantId): array {
        $plant = $this->detailsPlantRepo->findById($plantId);
        if (!$plant) {
            throw new Exception("Plant not found: $plantId");
        }
        return $this->buildPlantArray($plant);
    }

    public function exportPlantsJson(?array $plantIds = null): array {
        $plants = [];
        if ($plantIds !== null && !empty($plantIds)) {
            foreach ($plantIds as $id) {
                $plant = $this->detailsPlantRepo->findById($id);
                if ($plant) {
                    $plants[] = $this->buildPlantArray($plant);
                }
            }
        } else {
            $stmt = $this->db->query("SELECT id FROM power_plants ORDER BY created_at DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $plant = $this->detailsPlantRepo->findById($row['id']);
                if ($plant) {
                    $plants[] = $this->buildPlantArray($plant);
                }
            }
        }
        return [
            'version' => '1.0',
            'exported_at' => date('Y-m-d\TH:i:sP'),
            'plants' => $plants,
        ];
    }

    private function buildPlantArray(Plant $plant): array {
        $basic = $this->basicPlantRepo->findByPlantId($plant->getId());
        $geo = $this->geologicalPlantRepo->findByPlantId($plant->getId());
        $tech = $this->technicalPlantRepo->findByPlantId($plant->getId());

        $reactors = [];
        $reactorEntities = $this->reactorRepo->findByPlantId($plant->getId());
        foreach ($reactorEntities as $r) {
            $sensors = [];
            $sensorEntities = $this->sensorRepo->findByReactorId($r->getId());
            foreach ($sensorEntities as $s) {
                $sensors[] = [
                    'sensor_code' => $s->getSensorCode(),
                    'sensor_type' => $s->getSensorType()->value,
                    'unit_of_measure' => $s->getUnitOfMeasure(),
                    'description' => $s->getDescription(),
                    'location_zone' => $s->getLocationZone(),
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
                    'is_active' => $s->getIsActive(),
                    'last_calibration' => $s->getLastCalibration(),
                    'calibration_due' => $s->getCalibrationDue(),
                ];
            }

            $reactors[] = [
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
                'sensors' => $sensors,
            ];
        }

        $reactorConfigs = [];
        if ($tech) {
            foreach ($tech->getReactorConfigurations() as $rc) {
                $reactorConfigs[] = [
                    'reactor_type' => $rc->getType()->value,
                    'cooling_type' => $rc->getCooling()->value,
                ];
            }
        }

        return [
            'name' => $plant->getName(),
            'status' => $plant->getStatus()->value,
            'basic_data' => $basic ? [
                'capacity' => $basic->getCapacity(),
                'construction_duration_years' => $basic->getConstructionDurationYears(),
                'description' => $basic->getDescription(),
            ] : null,
            'geological_data' => $geo ? [
                'country' => $geo->getCountry(),
                'latitude' => $geo->getLatitude(),
                'longitude' => $geo->getLongitude(),
                'soil_type' => $geo->getSoilType()?->value,
                'water_source_type' => $geo->getWaterSourceType()?->value,
                'seismic_stability' => $geo->getSeismicStability(),
                'flood_risk' => $geo->getFloodRisk(),
                'groundwater_level' => $geo->getGroundwaterLevel(),
                'water_proximity' => $geo->getWaterProximity(),
                'water_flow_rate' => $geo->getWaterFlowRate(),
                'population_density' => $geo->getPopulationDensity(),
                'transport_infrastructure_score' => $geo->getTransportInfrastructureScore(),
                'geological_risk_score' => $geo->getGeologicalRiskScore(),
            ] : null,
            'technical_data' => $tech ? [
                'number_of_reactors' => $tech->getNumberOfReactors(),
                'estimated_efficiency' => $tech->getEstimatedEfficiency(),
                'operational_risk_level' => $tech->getOperationalRiskLevel(),
                'safety_systems' => $tech->getSafetySystems(),
                'reactor_configurations' => $reactorConfigs,
            ] : null,
            'reactors' => $reactors,
        ];
    }

    // ==================== EXPORT CSV (ZIP) ====================

    public function exportPlantCsv(string $plantId): string {
        $data = $this->exportPlantJson($plantId);
        return $this->generateCsvZip($data['plants']);
    }

    public function exportPlantsCsv(?array $plantIds = null): string {
        $data = $this->exportPlantsJson($plantIds);
        return $this->generateCsvZip($data['plants']);
    }

    private function generateCsvZip(array $plants): string {
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive is required for CSV export');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'npp_export_');

        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Failed to create ZIP archive');
        }

        
        $plantsHandle = fopen('php://temp', 'r+');
        fputcsv($plantsHandle, ['name', 'status'], escape: '\\');
        foreach ($plants as $p) {
            fputcsv($plantsHandle, [
                $p['name'],
                $p['status'] ?? 'REVIEW',
            ], escape: '\\');
        }
        rewind($plantsHandle);
        $zip->addFromString('plants.csv', stream_get_contents($plantsHandle));
        fclose($plantsHandle);

        // --- reactors.csv ---
        $reactorsHandle = fopen('php://temp', 'r+');
        $reactorColumns = [
            'plant_name',
            'reactor_code',
            'reactor_type',
            'cooling_type',
            'operational_status',
            'thermal_power_mw',
            'electrical_power_mw',
            'fuel_cycle_days',
            'current_cycle_day',
            'wear_index',
            'design_lifetime_yr',
            'commissioning_date',
            'first_criticality',
            'last_inspection_at',
            'next_planned_outage',
            'description',
        ];
        fputcsv($reactorsHandle, $reactorColumns, escape: '\\');
        foreach ($plants as $p) {
            foreach ($p['reactors'] ?? [] as $r) {
                fputcsv($reactorsHandle, [
                    $p['name'],
                    $r['reactor_code'] ?? '',
                    $r['reactor_type'] ?? '',
                    $r['cooling_type'] ?? '',
                    $r['operational_status'] ?? '',
                    $r['thermal_power_mw'] ?? '',
                    $r['electrical_power_mw'] ?? '',
                    $r['fuel_cycle_days'] ?? '',
                    $r['current_cycle_day'] ?? '',
                    $r['wear_index'] ?? '',
                    $r['design_lifetime_yr'] ?? '',
                    $r['commissioning_date'] ?? '',
                    $r['first_criticality'] ?? '',
                    $r['last_inspection_at'] ?? '',
                    $r['next_planned_outage'] ?? '',
                    $r['description'] ?? '',
                ], escape: '\\');
            }
        }
        rewind($reactorsHandle);
        $zip->addFromString('reactors.csv', stream_get_contents($reactorsHandle));
        fclose($reactorsHandle);

        // --- sensors.csv ---
        $sensorsHandle = fopen('php://temp', 'r+');
        $sensorColumns = [
            'reactor_plant_name',
            'reactor_code',
            'sensor_code',
            'sensor_type',
            'unit_of_measure',
            'description',
            'location_zone',
            'measurement_field',
            'normal_min',
            'normal_max',
            'alarm_low',
            'alarm_high',
            'alert_low',
            'alert_high',
            'scram_low',
            'scram_high',
            'status',
            'is_active',
            'last_calibration',
            'calibration_due',
        ];
        fputcsv($sensorsHandle, $sensorColumns, escape: '\\');
        foreach ($plants as $p) {
            foreach ($p['reactors'] ?? [] as $r) {
                foreach ($r['sensors'] ?? [] as $s) {
                    fputcsv($sensorsHandle, [
                        $p['name'],
                        $r['reactor_code'] ?? '',
                        $s['sensor_code'] ?? '',
                        $s['sensor_type'] ?? '',
                        $s['unit_of_measure'] ?? '',
                        $s['description'] ?? '',
                        $s['location_zone'] ?? '',
                        $s['measurement_field'] ?? '',
                        $s['normal_min'] ?? '',
                        $s['normal_max'] ?? '',
                        $s['alarm_low'] ?? '',
                        $s['alarm_high'] ?? '',
                        $s['alert_low'] ?? '',
                        $s['alert_high'] ?? '',
                        $s['scram_low'] ?? '',
                        $s['scram_high'] ?? '',
                        $s['status'] ?? 'GOOD',
                        isset($s['is_active']) ? ($s['is_active'] ? '1' : '0') : '1',
                        $s['last_calibration'] ?? '',
                        $s['calibration_due'] ?? '',
                    ], escape: '\\');
                }
            }
        }
        rewind($sensorsHandle);
        $zip->addFromString('sensors.csv', stream_get_contents($sensorsHandle));
        fclose($sensorsHandle);

        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $content;
    }

    // ==================== IMPORT JSON ====================

    public function importPlant(array $data): string {
        $this->db->beginTransaction();
        try {
            $result = $this->doImportPlant($data);
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function importPlants(array $data): array {
        $ids = [];
        $this->db->beginTransaction();
        try {
            foreach ($data as $plantData) {
                $ids[] = $this->doImportPlant($plantData);
            }
            $this->db->commit();
            return $ids;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function doImportPlant(array $data): string {
        // --- Validate required fields ---
        if (empty($data['name'])) {
            throw new Exception('Plant name is required');
        }

        // --- Create plant ---
        $plantId = generateUUID();
        $plant = new Plant(
            $plantId,
            $data['name'],
            PlantStatus::REVIEW,
            AuthHelper::getCurrentUserId()
        );
        $this->detailsPlantRepo->save($plant);

        // --- Import basic data ---
        if (!empty($data['basic_data'])) {
            $b = $data['basic_data'];
            $basicData = new BasicPlantData(
                $plantId,
                null,
                isset($b['capacity']) ? (float)$b['capacity'] : null,
                isset($b['construction_duration_years']) ? (int)$b['construction_duration_years'] : null,
                $b['description'] ?? '',
            );
            $this->basicPlantRepo->save($basicData);
        }

        // --- Import geological data ---
        if (!empty($data['geological_data'])) {
            $g = $data['geological_data'];
            $soilType = !empty($g['soil_type']) ? SoilType::tryFrom($g['soil_type']) : null;
            $waterSourceType = !empty($g['water_source_type']) ? WaterSourceType::tryFrom($g['water_source_type']) : null;

            $geoData = new GeologicalPlantData(
                $plantId,
                null,
                $g['country'] ?? null,
                isset($g['latitude']) ? (float)$g['latitude'] : null,
                isset($g['longitude']) ? (float)$g['longitude'] : null,
                $soilType,
                $waterSourceType,
                isset($g['seismic_stability']) ? (float)$g['seismic_stability'] : null,
                isset($g['flood_risk']) ? (float)$g['flood_risk'] : null,
                isset($g['groundwater_level']) ? (float)$g['groundwater_level'] : null,
                isset($g['water_proximity']) ? (float)$g['water_proximity'] : null,
                isset($g['water_flow_rate']) ? (float)$g['water_flow_rate'] : null,
                isset($g['population_density']) ? (float)$g['population_density'] : null,
                isset($g['transport_infrastructure_score']) ? (float)$g['transport_infrastructure_score'] : null,
                isset($g['geological_risk_score']) ? (float)$g['geological_risk_score'] : null,
            );
            $this->geologicalPlantRepo->save($geoData);
        }

        // --- Import technical data ---
        if (!empty($data['technical_data'])) {
            $t = $data['technical_data'];
            $techData = new TechnicalPlantData(
                $plantId,
                null,
                isset($t['number_of_reactors']) ? (int)$t['number_of_reactors'] : null,
                isset($t['estimated_efficiency']) ? (float)$t['estimated_efficiency'] : null,
                isset($t['operational_risk_level']) ? (float)$t['operational_risk_level'] : null,
                $t['safety_systems'] ?? [],
            );

            if (!empty($t['reactor_configurations'])) {
                foreach ($t['reactor_configurations'] as $rc) {
                    $schema = $this->technicalPlantRepo->getReactorSchemaByDetails(
                        $rc['reactor_type'],
                        $rc['cooling_type']
                    );
                    $techData->addReactorConfiguration($schema);
                }
            }

            $this->technicalPlantRepo->save($techData);
        }

        // --- Import reactors ---
        $reactorCodeMap = [];
        if (!empty($data['reactors'])) {
            foreach ($data['reactors'] as $rData) {
                $reactorType = ReactorType::tryFrom($rData['reactor_type']);
                if (!$reactorType) {
                    throw new Exception("Invalid reactor_type: {$rData['reactor_type']}");
                }

                $coolingType = CoolingType::tryFrom($rData['cooling_type']);
                if (!$coolingType) {
                    throw new Exception("Invalid cooling_type: {$rData['cooling_type']}");
                }

                $operationalStatus = !empty($rData['operational_status'])
                    ? (ReactorOperationalStatus::tryFrom($rData['operational_status']) ?? ReactorOperationalStatus::SHUTDOWN)
                    : ReactorOperationalStatus::SHUTDOWN;

                $reactorId = bin2hex(random_bytes(16));
                $reactor = new Reactor(
                    $plantId,
                    $rData['reactor_code'],
                    $reactorType,
                    $coolingType,
                    $reactorId,
                    $operationalStatus,
                    isset($rData['thermal_power_mw']) ? (float)$rData['thermal_power_mw'] : null,
                    isset($rData['electrical_power_mw']) ? (float)$rData['electrical_power_mw'] : null,
                    isset($rData['fuel_cycle_days']) ? (int)$rData['fuel_cycle_days'] : 365,
                    isset($rData['current_cycle_day']) ? (int)$rData['current_cycle_day'] : 0,
                    isset($rData['wear_index']) ? (float)$rData['wear_index'] : 0.0000,
                    isset($rData['design_lifetime_yr']) ? (int)$rData['design_lifetime_yr'] : 40,
                    $rData['commissioning_date'] ?? null,
                    $rData['first_criticality'] ?? null,
                    $rData['last_inspection_at'] ?? null,
                    $rData['next_planned_outage'] ?? null,
                    $rData['description'] ?? null,
                );

                $this->reactorRepo->save($reactor);
                $reactorCodeMap[$rData['reactor_code']] = $reactorId;

                // --- Import sensors for this reactor ---
                if (!empty($rData['sensors'])) {
                    $templates = [];
                    foreach ($rData['sensors'] as $sData) {
                        $sensorType = SensorType::tryFrom($sData['sensor_type']);
                        if (!$sensorType) {
                            throw new Exception("Invalid sensor_type: {$sData['sensor_type']}");
                        }

                        $sensorStatus = !empty($sData['status'])
                            ? (SensorQuality::tryFrom($sData['status']) ?? SensorQuality::GOOD)
                            : SensorQuality::GOOD;

                        $sensor = new ReactorSensor(
                            $reactorId,
                            $sData['sensor_code'],
                            $sensorType,
                            null,
                            $sData['description'] ?? null,
                            $sData['location_zone'] ?? null,
                            $sData['unit_of_measure'] ?? null,
                            $sData['measurement_field'] ?? null,
                            isset($sData['normal_min']) ? (float)$sData['normal_min'] : null,
                            isset($sData['normal_max']) ? (float)$sData['normal_max'] : null,
                            isset($sData['alarm_low']) ? (float)$sData['alarm_low'] : null,
                            isset($sData['alarm_high']) ? (float)$sData['alarm_high'] : null,
                            isset($sData['alert_low']) ? (float)$sData['alert_low'] : null,
                            isset($sData['alert_high']) ? (float)$sData['alert_high'] : null,
                            isset($sData['scram_low']) ? (float)$sData['scram_low'] : null,
                            isset($sData['scram_high']) ? (float)$sData['scram_high'] : null,
                            $sensorStatus,
                            isset($sData['is_active']) ? (bool)$sData['is_active'] : true,
                            $sData['last_calibration'] ?? null,
                            $sData['calibration_due'] ?? null,
                        );

                        $this->sensorRepo->save($sensor);
                    }
                }
            }
        }

        return $plantId;
    }
}
