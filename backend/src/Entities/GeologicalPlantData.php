<?php

require_once __DIR__ . '/../Helpers/generateUUID.php';
require_once __DIR__ . '/../Entities/SoilType.php';
require_once __DIR__ . '/../Entities/WaterSourceType.php';

class GeologicalPlantData {
    private string $id;
    private string $powerPlantId;
    private ?string $country;
    private ?float $latitude;
    private ?float $longitude;
    private ?SoilType $soilType;
    private ?WaterSourceType $waterSourceType;
    private ?float $seismicStability;
    private ?float $floodRisk;
    private ?float $groundwaterLevel; 
    private ?float $waterProximity;
    private ?float $waterFlowRate;
    private ?float $populationDensity;
    private ?float $transportInfrastructureScore;
    private ?float $geologicalRiskScore;

    public function __construct(
        string $powerPlantId,
        ?string $id = null,
        ?string $country = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?SoilType $soilType = null,
        ?WaterSourceType $waterSourceType = null,
        ?float $seismicStability = null,
        ?float $floodRisk = null,
        ?float $groundwaterLevel = null,
        ?float $waterProximity = null,
        ?float $waterFlowRate = null,
        ?float $populationDensity = null,
        ?float $transportInfrastructureScore = null,
        ?float $geologicalRiskScore = null
    ) {
        $this->id = $id ?? generateUUID();
        $this->powerPlantId = $powerPlantId;
        $this->country = $country;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->soilType = $soilType;
        $this->waterSourceType = $waterSourceType;
        $this->seismicStability = $seismicStability;
        $this->floodRisk = $floodRisk;
        $this->groundwaterLevel = $groundwaterLevel;
        $this->waterProximity = $waterProximity;
        $this->waterFlowRate = $waterFlowRate;
        $this->populationDensity = $populationDensity;
        $this->transportInfrastructureScore = $transportInfrastructureScore;
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

    public function getCountry(): ?string {
        return $this->country;
    }

    public function setCountry(?string $country): void {
        $this->country = $country;
    }

    public function getLatitude(): ?float {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void {
        $this->longitude = $longitude;
    }

    public function getSoilType(): ?SoilType {
        return $this->soilType;
    }

    public function setSoilType(?SoilType $soilType): void {
        $this->soilType = $soilType;
    }

    public function getWaterSourceType(): ?WaterSourceType {
        return $this->waterSourceType;
    }

    public function setWaterSourceType(?WaterSourceType $waterSourceType): void {
        $this->waterSourceType = $waterSourceType;
    }

    public function getSeismicStability(): ?float {
        return $this->seismicStability;
    }

    public function setSeismicStability(?float $seismicStability): void {
        $this->seismicStability = $seismicStability;
    }

    public function getFloodRisk(): ?float {
        return $this->floodRisk;
    }

    public function setFloodRisk(?float $floodRisk): void {
        $this->floodRisk = $floodRisk;
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

    public function getWaterFlowRate(): ?float {
        return $this->waterFlowRate;
    }

    public function setWaterFlowRate(?float $waterFlowRate): void {
        $this->waterFlowRate = $waterFlowRate;
    }

    public function getPopulationDensity(): ?float {
        return $this->populationDensity;
    }

    public function setPopulationDensity(?float $populationDensity): void {
        $this->populationDensity = $populationDensity;
    }

    public function getTransportInfrastructureScore(): ?float {
        return $this->transportInfrastructureScore;
    }

    public function setTransportInfrastructureScore(?float $transportInfrastructureScore): void {
        $this->transportInfrastructureScore = $transportInfrastructureScore;
    }

    public function getGeologicalRiskScore(): ?float {
        return $this->geologicalRiskScore;
    }

    public function setGeologicalRiskScore(?float $geologicalRiskScore): void {
        $this->geologicalRiskScore = $geologicalRiskScore;
    }
}