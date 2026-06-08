<?php 

require_once __DIR__ . '/../Repositories/ReactorRepository.php'; 

require_once __DIR__ . '/../Dto/ReactorDetailsDTO.php'; 
require_once __DIR__ . '/../Dto/ReactorListDTO.php'; 

require_once __DIR__ . '/../Entities/ReactorType.php'; 
require_once __DIR__ . '/../Entities/CoolingType.php'; 
require_once __DIR__  . '/../Entities/ReactorOperationalStatus.php'; 

class ReactorService { 
    private ReactorRepository $reactorRepository; 

    public function __construct(ReactorRepository $reactorRepository) { 
        $this->reactorRepository = $reactorRepository; 
    }

    public function getReactor(string $id): ?ReactorDetailsDTO { 
        $reactor = $this->reactorRepository->findById($id); 
        if(!$reactor) throw new Exception("Reactorul cu ID-ul $id nu a fost gasit.");

        return ReactorDetailsDTO::fromEntity($reactor); 
    }

    public function getReactorsByPlant(string $plantId): array { 
        $reactors = $this->reactorRepository->findByPlantId($plantId); 

        return array_map(fn($r) => ReactorListDTO::fromEntity($r), $reactors); 
    }

    public function getAllReactors(): array { 
        $reactors = $this->reactorRepository->findAll(); 

        return array_map(fn($r) => ReactorListDTO::fromEntity($r), $reactors); 
    }

    public function createReactor(array $data): string { 
        $this->validateCreationData($data); 

        $reactorType = ReactorType::tryFrom($data['reactorType']); 
        $coolingType = CoolingType::tryFrom($data['coolingType']); 

        $operationalStatus = isset($data['operationalStatus']) ? (ReactorOperationalStatus::tryFrom($data['operationalStatus'])) ?? ReactorOperationalStatus::SHUTDOWN 
            : ReactorOperationalStatus::SHUTDOWN; 
            
        $reactor = new Reactor(
            $data['powerPlantId'],
            $data['reactorCode'],
            $reactorType,
            $coolingType,
            null, // id (se va genera automat în constructor)
            $operationalStatus,
            $data['thermalPowerMw'] ?? null,
            $data['electricalPowerMw'] ?? null,
            $data['fuelCycleDays'] ?? 365,
            $data['currentCycleDay'] ?? 0,
            $data['wearIndex'] ?? 0.0000,
            $data['designLifetimeYr'] ?? 40,
            $data['commissioningDate'] ?? null,
            $data['firstCriticality'] ?? null,
            $data['lastInspectionAt'] ?? null,
            $data['nextPlannedOutage'] ?? null,
            $data['description'] ?? null
        );

        $this->reactorRepository->save($reactor);

        return $reactor->getId();
    }

    public function updateReactor(string $id, array $data): void { 
        $reactor = $this->reactorRepository->findById($id); 
        if(!$reactor) throw new Exception("Reactorul cu ID-ul $id nu a fost gasit pentru actualizare."); 

        if(isset($data['reactorCode'])) { 
            if(empty(trim($data['reactorCode']))) throw new Exception("reactorCode nu poate fi gol"); 
            $reactor->setReactorCode($data['reactorCode']); 
        }

        if(isset($data['reactorType'])) { 
            $type = ReactorType::tryFrom($data['reactorType']); 
            if(!$type) throw new Exception("reactorType invalid."); 
            $reactor->setReactorType($type); 
        }

        if (isset($data['coolingType'])) {
            $cooling = CoolingType::tryFrom($data['coolingType']);
            if (!$cooling) throw new Exception("coolingType invalid.");
            $reactor->setCoolingType($cooling);
        }

        if (isset($data['operationalStatus'])) {
            $status = ReactorOperationalStatus::tryFrom($data['operationalStatus']);
            if (!$status) throw new Exception("operationalStatus invalid.");
            $reactor->setOperationalStatus($status);
        }

        if (array_key_exists('thermalPowerMw', $data)) $reactor->setThermalPowerMw($data['thermalPowerMw']);
        if (array_key_exists('electricalPowerMw', $data)) $reactor->setElectricalPowerMw($data['electricalPowerMw']);
        if (array_key_exists('fuelCycleDays', $data)) $reactor->setFuelCycleDays($data['fuelCycleDays']);
        if (array_key_exists('currentCycleDay', $data)) $reactor->setCurrentCycleDay($data['currentCycleDay']);
        if (array_key_exists('wearIndex', $data)) $reactor->setWearIndex($data['wearIndex']);
        if (array_key_exists('designLifetimeYr', $data)) $reactor->setDesignLifetimeYr($data['designLifetimeYr']);
        if (array_key_exists('commissioningDate', $data)) $reactor->setCommissioningDate($data['commissioningDate']);
        if (array_key_exists('firstCriticality', $data)) $reactor->setFirstCriticality($data['firstCriticality']);
        if (array_key_exists('lastInspectionAt', $data)) $reactor->setLastInspectionAt($data['lastInspectionAt']);
        if (array_key_exists('nextPlannedOutage', $data)) $reactor->setNextPlannedOutage($data['nextPlannedOutage']);
        if (array_key_exists('description', $data)) $reactor->setDescription($data['description']);

        $this->reactorRepository->update($reactor); 
    } 

    public function deleteReactor(string $id): void { 
        $reactor = $this->reactorRepository->findById($id); 
        if(!$reactor) throw new Exception("Reactorul cu ID-ul $id nu a fost gasit pentru stergere"); 

        $this->reactorRepository->delete($id); 
    }

    private function validateCreationData(array $data): void {
        if (empty($data['powerPlantId']) || !$this->isValidUuid($data['powerPlantId'])) {
            throw new \Exception("Eroare de validare: powerPlantId lipsește sau are un format UUID invalid.");
        }

        if (empty($data['reactorCode']) || !is_string($data['reactorCode'])) {
            throw new \Exception("Eroare de validare: reactorCode este obligatoriu și trebuie să fie string.");
        }

        if (empty($data['reactorType']) || !ReactorType::tryFrom($data['reactorType'])) {
            throw new \Exception("Eroare de validare: reactorType lipsește sau este invalid.");
        }

        if (empty($data['coolingType']) || !CoolingType::tryFrom($data['coolingType'])) {
            throw new \Exception("Eroare de validare: coolingType lipsește sau este invalid.");
        }
    }

    private function isValidUuid(string $uuid): bool {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }
}