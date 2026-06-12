<?php 
require_once __DIR__ . '/../../Entities/TechnicalPlantData.php'; 
require_once __DIR__ . '/../../Entities/ReactorSchema.php'; 
require_once __DIR__ . '/../../Entities/ReactorType.php'; 
require_once __DIR__ . '/../../Entities/CoolingType.php'; 

require_once __DIR__ . '/../../Dto/CreateDataResponseDTO.php'; 

class TechnicalPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId): ?TechnicalPlantData { 
        return $this->plantRepositoryFacade->getTechnicalDataByPlantId($plantId); 
    }

    public function save(array $data, string $plantId): CreateDataResponseDTO { 
        $existingData = $this->plantRepositoryFacade->getTechnicalDataByPlantId($plantId);

        if ($existingData !== null) {
            throw new Exception("Există deja date tehnice pentru această centrală. Te rugăm să folosești metoda de UPDATE (PUT/PATCH).");
        }

        $estimatedEfficiency = (isset($data['estimatedEfficiency']) && $data['estimatedEfficiency'] !== '') 
            ? (float) $data['estimatedEfficiency'] 
            : null;

        $operationalRiskLevel = (isset($data['operationalRiskLevel']) && $data['operationalRiskLevel'] !== '') 
            ? (float) $data['operationalRiskLevel'] 
            : null;

        $reactorConfigurations = (isset($data['reactorConfigurations']) && is_array($data['reactorConfigurations'])) 
            ? $data['reactorConfigurations'] 
            : [];

        $numberOfReactors = count($reactorConfigurations);

        $technicalPlantData = new TechnicalPlantData($plantId, null, $numberOfReactors, $estimatedEfficiency, $operationalRiskLevel); 

        foreach ($reactorConfigurations as $config) { 
            $reactorType = ReactorType::tryFrom($config['reactorType']);
            if (!$reactorType) throw new Exception("Tip reactor invalid: " . ($config['reactorType'] ?? '?'));
            $coolingType = CoolingType::tryFrom($config['coolingType']);
            if (!$coolingType) throw new Exception("Tip răcire invalid: " . ($config['coolingType'] ?? '?'));
            $currentReactorSchema = $this->plantRepositoryFacade->getReactorSchemaByDetails(
                $reactorType->value,
                $coolingType->value
            ); 
            $technicalPlantData->addReactorConfiguration($currentReactorSchema); 
        }

        $this->plantRepositoryFacade->saveTechnicalData($technicalPlantData); 
        return new CreateDataResponseDTO($technicalPlantData->getId()); 
    }

    public function update(array $data, string $plantId): void { 
        $currentPlantData = $this->plantRepositoryFacade->getTechnicalDataByPlantId($plantId); 

        $estimatedEfficiency = $data['estimatedEfficiency'] ?? ''; 
        $estimatedEfficiency = ($estimatedEfficiency !== '') ? $estimatedEfficiency : null; 

        $operationalRiskLevel = $data['operationalRiskLevel'] ?? ''; 
        $operationalRiskLevel = ($operationalRiskLevel !== '') ? $operationalRiskLevel : null; 

        $reactorConfigurations = $data['reactorConfigurations'] ?? []; 

        $numberOfReactors = count($reactorConfigurations);

        $technicalPlantData = new TechnicalPlantData($plantId, $currentPlantData->getId(), $numberOfReactors, $estimatedEfficiency, $operationalRiskLevel); 

        foreach ($reactorConfigurations as $config) { 
            $reactorType = ReactorType::tryFrom($config['reactorType']);
            if (!$reactorType) throw new Exception("Tip reactor invalid: " . ($config['reactorType'] ?? '?'));
            $coolingType = CoolingType::tryFrom($config['coolingType']);
            if (!$coolingType) throw new Exception("Tip răcire invalid: " . ($config['coolingType'] ?? '?'));
            $currentReactorSchema = new ReactorSchema(
                generateUUID(), 
                $reactorType, 
                $coolingType, 
            ); 
            $technicalPlantData->addReactorConfiguration($currentReactorSchema); 
        }

        $this->plantRepositoryFacade->updateTechnicalData($technicalPlantData); 
    }
}