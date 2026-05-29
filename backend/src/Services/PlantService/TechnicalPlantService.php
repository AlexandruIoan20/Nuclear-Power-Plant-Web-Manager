<?php 

require_once __DIR__ . '/../../Entities/TechnicalPlantData.php'; 
require_once __DIR__ . '/../../Entities/ReactorSchema.php'; 
require_once __DIR__ . '/../../Entities/ReactorType.php'; 
require_once __DIR__ . '/../../Entities/CoolingType.php'; 

class TechnicalPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId): ?TechnicalPlantData { 
        return $this->plantRepositoryFacade->getTechnicalDataByPlantId($plantId); 
    }

    public function save(array $data, string $plantId): void { 
        $existingData = $this->plantRepositoryFacade->getTechnicalDataByPlantId($plantId);
    
        if ($existingData !== null) {
            throw new Exception("Există deja date tehnice pentru această centrală. Te rugăm să folosești metoda de UPDATE (PUT/PATCH).");
        }

        $numberOfReactors = (isset($data['number_of_reactors']) && $data['number_of_reactors'] !== '') 
        ? (int) $data['number_of_reactors'] 
        : null;

        $estimatedEfficiency = (isset($data['estimated_efficiency']) && $data['estimated_efficiency'] !== '') 
            ? (float) $data['estimated_efficiency'] 
            : null;

        $operationalRiskLevel = (isset($data['operational_risk_level']) && $data['operational_risk_level'] !== '') 
            ? (float) $data['operational_risk_level'] 
            : null;

        $reactorConfigurations = (isset($data['reactor_configurations']) && is_array($data['reactor_configurations'])) 
            ? $data['reactor_configurations'] 
            : [];

        $technicalPlantData = new TechnicalPlantData($plantId, null, $numberOfReactors, $estimatedEfficiency, $operationalRiskLevel); 
        foreach($reactorConfigurations as $config) { 
            $currentReactorSchema = $this->plantRepositoryFacade->getReactorSchemaByDetails(
                ReactorType::from($config['reactor_type'])->value,
                 CoolingType::from($config['cooling_type'])->value
            ); 
            $technicalPlantData->addReactorConfiguration($currentReactorSchema); 
        }

        $this->plantRepositoryFacade->saveTechnicalData($technicalPlantData); 
    }

    public function update(array $data, string $plantId): void { 
        $currentPlantData = $this->plantRepositoryFacade->getTechnicalDataByPlantId($plantId); 

        $numberOfReactors = $data['number_of_reactors'] ?? ''; 
        $numberOfReactors = ($numberOfReactors !== '') ? $numberOfReactors : null; 

        $estimatedEfficiency = $data['estimated_efficiency'] ?? ''; 
        $estimatedEfficiency = ($estimatedEfficiency !== '') ? $estimatedEfficiency : null; 

        $operationalRiskLevel = $data['operational_risk_level'] ?? ''; 
        $operationalRiskLevel = ($operationalRiskLevel !== '') ? $operationalRiskLevel : null; 

        $reactorConfigurations = $data['reactor_configurations'] ?? []; 

        $technicalPlantData = new TechnicalPlantData($plantId, $currentPlantData->getId(), $numberOfReactors, $estimatedEfficiency, $operationalRiskLevel); 
        foreach($reactorConfigurations as $config) { 
            $currentReactorSchema = new ReactorSchema(
                generateUUID(), 
                ReactorType::from($config['reactor_type']), 
                CoolingType::from($config['cooling_type']), 
            ); 

            $technicalPlantData->addReactorConfiguration($currentReactorSchema); 
        }

        $this->plantRepositoryFacade->updateTechnicalData($technicalPlantData); 
    }
}