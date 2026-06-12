<?php 

require_once __DIR__ . '/../Entities/TechnicalPlantData.php'; 
require_once __DIR__ . '/BaseDTO.php';

class TechnicalPlantDataDTO extends BaseDTO { 
    public function __construct ( 
        public readonly string $id, 
        public readonly string $powerPlantId, 
        public readonly ?int $numberOfReactors, 
        public readonly ?float $estimatedEfficiency, 
        public readonly ?float $operationalRiskLevel, 
        public readonly ?array $safetySystems, 
        public readonly ?array $reactorConfigs,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt
    ) {}

    public static function fromEntity(TechnicalPlantData $t, array $configs = []) { 
        $formattedReactorConfigs = []; 
        foreach($configs as $config) { 
            $formattedReactorConfigs[] = [ 
                'reactorType' => $config->getType()->value, 
                'coolingType' => $config->getCooling()->value
            ]; 
        }
        
        return new self ( 
            id: $t->getId(), 
            powerPlantId: $t->getPowerPlantId(), 
            numberOfReactors: $t->getNumberOfReactors(), 
            estimatedEfficiency: $t->getEstimatedEfficiency(), 
            operationalRiskLevel: $t->getOperationalRiskLevel(), 
            safetySystems: $t->getSafetySystems(), 
            reactorConfigs: $formattedReactorConfigs,
            createdAt: $t->getCreatedAt(),
            updatedAt: $t->getUpdatedAt()
        ); 
    }
}
