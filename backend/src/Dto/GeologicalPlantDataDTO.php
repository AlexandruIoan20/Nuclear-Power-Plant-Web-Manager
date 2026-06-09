<?php 

require_once __DIR__ . '/../Entities/GeologicalPlantData.php'; 

class GeologicalPlantDataDTO { 
    public function __construct(
        public readonly string $id,
        public readonly string $powerPlantId,
        public readonly ?string $country,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?string $soilType,
        public readonly ?string $waterSourceType,
        public readonly ?float $seismicStability,
        public readonly ?float $floodRisk,
        public readonly ?float $groundwaterLevel,
        public readonly ?float $waterProximity,
        public readonly ?float $waterFlowRate,
        public readonly ?float $populationDensity,
        public readonly ?float $transportInfrastructureScore,
        public readonly ?float $geologicalRiskScore,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt
    ) {}

    public static function fromEntity(GeologicalPlantData $g): self { 
        return new self ( 
            id: $g->getId(), 
            powerPlantId: $g->getPowerPlantId(), 
            country: $g->getCountry(), 
            latitude: $g->getLatitude(), 
            longitude: $g->getLongitude(), 
            soilType: $g->getSoilType()?->value, 
            waterSourceType: $g->getWaterSourceType()?->value, 
            seismicStability: $g->getSeismicStability(), 
            floodRisk: $g->getFloodRisk(), 
            groundwaterLevel: $g->getGroundwaterLevel(), 
            waterProximity: $g->getWaterProximity(), 
            waterFlowRate: $g->getWaterFlowRate(), 
            populationDensity: $g->getPopulationDensity(), 
            transportInfrastructureScore: $g->getTransportInfrastructureScore(), 
            geologicalRiskScore: $g->getGeologicalRiskScore(),
            createdAt: $g->getCreatedAt(),
            updatedAt: $g->getUpdatedAt()
        ); 
    }
}
