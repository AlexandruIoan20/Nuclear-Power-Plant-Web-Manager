<?php 

require_once __DIR__ . '/../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../Entities/SoilType.php'; 

class GeologicalPlantData { 
    private string $id; 
    private string $powerPlantId; 
    private ?SoilType $soilType; 
    private ?float $seismicStability; 
    private ?float $groundwaterLevel; 
    private ?float $waterProximity; 
    private ?float $geologicalRiskScore; 

    public function __construct(
        string $powerPlantId,
        ?string $id = null, 
        ?SoilType $soilType = null, 
        ?float $seismicStability = null, 
        ?float $groundwaterLevel = null, 
        ?float $waterProximity = null, 
        ?float $geologicalRiskScore = null
    ) { 
        $this->id = $id ?? generateUUID(); 
        $this->powerPlantId = $powerPlantId;
        $this->soilType = $soilType;
        $this->seismicStability = $seismicStability;
        $this->groundwaterLevel = $groundwaterLevel;
        $this->waterProximity = $waterProximity;
        $this->geologicalRiskScore = $geologicalRiskScore;
    }

    public function getId(): string { 
        return $this->id; 
    }

    public function setId(string $id): void { 
        $this->id = $id; 
    }

    public function getPowerPlantId(): string {
        return $this->powerPlantId;
    }

    public function setPowerPlantId(string $powerPlantId): void {
        $this->powerPlantId = $powerPlantId;
    }

    public function getSoilType(): ?SoilType {
        return $this->soilType;
    }

    public function setSoilType(?SoilType $soilType): void {
        $this->soilType = $soilType;
    }

    public function getSeismicStability(): ?float {
        return $this->seismicStability;
    }

    public function setSeismicStability(?float $seismicStability): void {
        $this->seismicStability = $seismicStability;
    }

    public function getGroundwaterLevel(): ?float {
        return $this->groundwaterLevel;
    }

    public function setGroundwaterLevel(?float $groundwaterLevel): void {
        $this->groundwaterLevel = $groundwaterLevel;
    }

    public function getWaterProximity(): ?float {
        return $this->waterProximity;
    }

    public function setWaterProximity(?float $waterProximity): void {
        $this->waterProximity = $waterProximity;
    }

    public function getGeologicalRiskScore(): ?float {
        return $this->geologicalRiskScore;
    }

    public function setGeologicalRiskScore(?float $geologicalRiskScore): void {
        $this->geologicalRiskScore = $geologicalRiskScore;
    }
}