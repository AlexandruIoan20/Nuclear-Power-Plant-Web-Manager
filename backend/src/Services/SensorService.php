<?php

require_once __DIR__ . '/../Repositories/SensorRepository.php'; 
require_once __DIR__ . '/../Repositories/SensorTemplateRepository.php'; 

require_once __DIR__ . '/../Dto/SensorDetailsDTO.php'; 
require_once __DIR__ . '/../Dto/SensorListDTO.php'; 

require_once __DIR__ . '/../Entities/ReactorType.php'; 
require_once __DIR__ . '/../Entities/SensorType.php'; 

class SensorService { 
    private SensorRepository $sensorRepository; 
    private SensorTemplateRepository $sensorTemplateRepository; 

    public function __construct(SensorRepository $sensorRepository, SensorTemplateRepository $sensorTemplateRepository) { 
        $this->sensorRepository = $sensorRepository; 
        $this->sensorTemplateRepository = $sensorTemplateRepository; 
    }

    public function populateSensorsForReactor(string $reactorId, string $reactorTypeString) { 
        try { 
            $reactorType = ReactorType::tryFrom($reactorTypeString); 
            if($reactorType == null) throw new Exception("Nu exista reactoare cu tipul: {$reactorTypeString}"); 

            $templates = $this->sensorTemplateRepository->findByReactorType($reactorType); 

            if(empty($templates)) { 
                LogService::instance()->warning("[WARNING] Reactor Sensor Service: Nu au fost gasite template-uri pentru senzorii reactorului");
                return; 
            }

            $this->sensorRepository->insertBulk($reactorId, $templates);
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] Populate Reactor Sensors: " . $e->getMessage());
            throw new Exception("Eroare la generarea senzorilor pentru reactor: " . $e->getMessage()); 
        }
    }

    public function getSensor(string $id): ?SensorDetailsDTO { 
        $sensor = $this->sensorRepository->findById($id); 
        if(!$sensor) throw new Exception("Senzorul cu ID-ul {$id} nu a fost gasit"); 

        return SensorDetailsDTO::fromEntity($sensor); 
    }

    public function getSensorsByReactor(string $reactorId): array { 
        $sensors = $this->sensorRepository->findByReactorId($reactorId); 
        return array_map(fn($s) => SensorListDTO::fromEntity($s), $sensors); 
    }

    public function createSensor(array $data): string { 
        $this->validateCreationData($data); 

        $sensorType = SensorType::tryFrom($data['sensorType']); 
        
        $status = isset($data['status']) ? (SensorQuality::tryFrom($data['status'])) ?? SensorQuality::GOOD 
            : SensorQuality::GOOD;

            $sensor = new ReactorSensor(
                $data['reactorId'],
                $data['sensorCode'],
                $sensorType,
                null, // id se genereaza automat
                $data['description'] ?? null,
                $data['locationZone'] ?? null,
                $data['unitOfMeasure'] ?? null,
                $data['measurementField'] ?? null,
                $data['normalMin'] ?? null,
                $data['normalMax'] ?? null,
                $data['alarmLow'] ?? null,
                $data['alarmHigh'] ?? null,
                $data['alertLow'] ?? null,
                $data['alertHigh'] ?? null,
                $data['scramLow'] ?? null,
                $data['scramHigh'] ?? null,
                $status,
                isset($data['isActive']) ? (bool)$data['isActive'] : true,
                $data['lastCalibration'] ?? null,
                $data['calibrationDue'] ?? null,
                $data['currentValue'] ?? null,
                $data['lastReadingAt'] ?? null
            );
    
            $this->sensorRepository->save($sensor);
    
            return $sensor->getId();
    }

    public function updateSensor(string $id, array $data): void { 
        $sensor = $this->sensorRepository->findById($id); 
        if(!$sensor) throw new Exception("Senzorul cu ID-ul $id nu a fost gasit pentru actualizare."); 

        if(isset($data['sensorCode'])) { 
            if(empty(trim($data['sensorCode']))) throw new Exception("sensorCode nu poate fi gol"); 
            $sensor->setSensorCode($data['sensorCode']); 
        }

        if(isset($data['sensorType'])) { 
            $type = SensorType::tryFrom($data['sensorType']); 
            if(!$type) throw new Exception("sensorType invalid."); 
            $sensor->setSensorType($type); 
        }

        if (isset($data['status'])) {
            $status = SensorQuality::tryFrom($data['status']);
            if (!$status) throw new Exception("status invalid.");
            $sensor->setStatus($status);
        }

        if (array_key_exists('description', $data)) $sensor->setDescription($data['description']);
        if (array_key_exists('locationZone', $data)) $sensor->setLocationZone($data['locationZone']);
        if (array_key_exists('unitOfMeasure', $data)) $sensor->setUnitOfMeasure($data['unitOfMeasure']);
        if (array_key_exists('measurementField', $data)) $sensor->setMeasurementField($data['measurementField']);
        if (array_key_exists('normalMin', $data)) $sensor->setNormalMin($data['normalMin']);
        if (array_key_exists('normalMax', $data)) $sensor->setNormalMax($data['normalMax']);
        if (array_key_exists('alarmLow', $data)) $sensor->setAlarmLow($data['alarmLow']);
        if (array_key_exists('alarmHigh', $data)) $sensor->setAlarmHigh($data['alarmHigh']);
        if (array_key_exists('alertLow', $data)) $sensor->setAlertLow($data['alertLow']);
        if (array_key_exists('alertHigh', $data)) $sensor->setAlertHigh($data['alertHigh']);
        if (array_key_exists('scramLow', $data)) $sensor->setScramLow($data['scramLow']);
        if (array_key_exists('scramHigh', $data)) $sensor->setScramHigh($data['scramHigh']);
        if (array_key_exists('isActive', $data)) $sensor->setIsActive((bool)$data['isActive']);
        if (array_key_exists('lastCalibration', $data)) $sensor->setLastCalibration($data['lastCalibration']);
        if (array_key_exists('calibrationDue', $data)) $sensor->setCalibrationDue($data['calibrationDue']);

        $this->sensorRepository->update($sensor); 
    }

    private function validateCreationData(array $data): void { 
        if (empty($data['reactorId']) || !$this->isValidUuid($data['reactorId'])) {
            throw new Exception("Eroare de validare: reactorId lipsește sau are un format UUID invalid.");
        }

        if (empty($data['sensorCode']) || !is_string($data['sensorCode'])) {
            throw new Exception("Eroare de validare: sensorCode este obligatoriu și trebuie să fie string.");
        }

        if (empty($data['sensorType']) || !SensorType::tryFrom($data['sensorType'])) {
            throw new Exception("Eroare de validare: sensorType lipsește sau este invalid.");
        }
    }

    private function isValidUuid(string $uuid): bool {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }
}