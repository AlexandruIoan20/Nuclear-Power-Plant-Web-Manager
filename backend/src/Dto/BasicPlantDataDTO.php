<?php

require_once __DIR__ . '/../Entities/BasicPlantData.php'; 

class BasicPlantDataDTO { 
    public function __construct( 
        public readonly string $id, 
        public readonly string $powerPlantId, 
        public readonly ?float $capacity, 
        public readonly ?int $constructionDurationYears, 
        public readonly ?string $description
    ) {}

    public static function fromEntity(BasicPlantData $basicPlantData): self { 
        return new self ( 
            id: $basicPlantData->getId(), 
            powerPlantId: $basicPlantData->getPowerPlantId(), 
            capacity: $basicPlantData->getCapacity(), 
            constructionDurationYears: $basicPlantData->getConstructionDurationYears(), 
            description: $basicPlantData->getDescription()
        ); 
    }
}