<?php 

require_once __DIR__ . '/../../Entities/TechnicalPlantData.php'; 
require_once __DIR__ . '/../../Entities/ReactorSchema.php'; 
require_once __DIR__ . '/../../Entities/ReactorType.php'; 
require_once __DIR__ . '/../../Entities/CoolingType.php'; 

class TechnicalPlantService { 
    private TechnicalPlantRepository $technicalPlantRepository; 

    public function __construct(TechnicalPlantRepository $technicalPlantRepository) { 
        $this->technicalPlantRepository = $technicalPlantRepository; 
    }

    public function findByPlantId(string $plantId): ?TechnicalPlantData { 
        return $this->technicalPlantRepository->findByPlantId($plantId); 
    }

    public function save(array $data, string $plantId): void { 
        $numberOfReactors = $data['number_of_reactors'] ?? ''; 
        $numberOfReactors = ($numberOfReactors !== '') ? $numberOfReactors : null; 

        $estimatedEfficiency = $data['estimated_efficiency'] ?? ''; 
        $estimatedEfficiency = ($estimatedEfficiency !== '') ? $estimatedEfficiency : null; 

        $operationalRiskLevel = $data['operational_risk_level'] ?? ''; 
        $operationalRiskLevel = ($operationalRiskLevel !== '') ? $operationalRiskLevel : null; 

        $reactorConfigurations = $data['reactor_configurations'] ?? []; 

        $technicalPlantData = new TechnicalPlantData($plantId, null, $numberOfReactors, $estimatedEfficiency, $operationalRiskLevel); 
        foreach($reactorConfigurations as $config) { 
            $currentReactorSchema = new ReactorSchema(
                generateUUID(), 
                ReactorType::from($config['reactor_type']), 
                CoolingType::from($config['cooling_type']), 
            ); 

            $technicalPlantData->addReactorConfiguration($currentReactorSchema); 
        }

        $this->technicalPlantRepository->save($technicalPlantData); 
    }

    public function update(array $data, string $plantId): void { 
        $currentPlantData = $this->technicalPlantRepository->findByPlantId($plantId); 

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

        $this->technicalPlantRepository->update($technicalPlantData); 
    }
}