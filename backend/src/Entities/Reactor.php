<?php

class Reactor {
    private string $id;
    private string $powerPlantId;
    private string $reactorCode;
    private ReactorType $reactorType;
    private CoolingType $coolingType;
    private ReactorOperationalStatus $operationalStatus;
    private ?float $thermalPowerMw;
    private ?float $electricalPowerMw;
    private ?int $fuelCycleDays;
    private ?int $currentCycleDay;
    private ?float $wearIndex;
    private ?int $designLifetimeYr;
    private ?string $commissioningDate;
    private ?string $firstCriticality;
    private ?string $lastInspectionAt;
    private ?string $nextPlannedOutage;
    private ?string $description;
    private ?string $createdAt;

    public function __construct(
        string $powerPlantId,
        string $reactorCode,
        ReactorType $reactorType,
        CoolingType $coolingType,
        ?string $id = null,
        ReactorOperationalStatus $operationalStatus = ReactorOperationalStatus::SHUTDOWN,
        ?float $thermalPowerMw = null,
        ?float $electricalPowerMw = null,
        ?int $fuelCycleDays = 365,
        ?int $currentCycleDay = 0,
        ?float $wearIndex = 0.0000,
        ?int $designLifetimeYr = 40,
        ?string $commissioningDate = null,
        ?string $firstCriticality = null,
        ?string $lastInspectionAt = null,
        ?string $nextPlannedOutage = null,
        ?string $description = null,
        ?string $createdAt = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16));
        $this->powerPlantId = $powerPlantId;
        $this->reactorCode = $reactorCode;
        $this->reactorType = $reactorType;
        $this->coolingType = $coolingType;
        $this->operationalStatus = $operationalStatus;
        $this->thermalPowerMw = $thermalPowerMw;
        $this->electricalPowerMw = $electricalPowerMw;
        $this->fuelCycleDays = $fuelCycleDays;
        $this->currentCycleDay = $currentCycleDay;
        $this->wearIndex = $wearIndex;
        $this->designLifetimeYr = $designLifetimeYr;
        $this->commissioningDate = $commissioningDate;
        $this->firstCriticality = $firstCriticality;
        $this->lastInspectionAt = $lastInspectionAt;
        $this->nextPlannedOutage = $nextPlannedOutage;
        $this->description = $description;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getPowerPlantId(): string { return $this->powerPlantId; }
    public function setPowerPlantId(string $powerPlantId): void { $this->powerPlantId = $powerPlantId; }

    public function getReactorCode(): string { return $this->reactorCode; }
    public function setReactorCode(string $reactorCode): void { $this->reactorCode = $reactorCode; }

    public function getReactorType(): ReactorType { return $this->reactorType; }
    public function setReactorType(ReactorType $reactorType): void { $this->reactorType = $reactorType; }

    public function getCoolingType(): CoolingType { return $this->coolingType; }
    public function setCoolingType(CoolingType $coolingType): void { $this->coolingType = $coolingType; }

    public function getOperationalStatus(): ReactorOperationalStatus { return $this->operationalStatus; }
    public function setOperationalStatus(ReactorOperationalStatus $operationalStatus): void { $this->operationalStatus = $operationalStatus; }

    public function getThermalPowerMw(): ?float { return $this->thermalPowerMw; }
    public function setThermalPowerMw(?float $thermalPowerMw): void { $this->thermalPowerMw = $thermalPowerMw; }

    public function getElectricalPowerMw(): ?float { return $this->electricalPowerMw; }
    public function setElectricalPowerMw(?float $electricalPowerMw): void { $this->electricalPowerMw = $electricalPowerMw; }

    public function getFuelCycleDays(): ?int { return $this->fuelCycleDays; }
    public function setFuelCycleDays(?int $fuelCycleDays): void { $this->fuelCycleDays = $fuelCycleDays; }

    public function getCurrentCycleDay(): ?int { return $this->currentCycleDay; }
    public function setCurrentCycleDay(?int $currentCycleDay): void { $this->currentCycleDay = $currentCycleDay; }

    public function getWearIndex(): ?float { return $this->wearIndex; }
    public function setWearIndex(?float $wearIndex): void { $this->wearIndex = $wearIndex; }

    public function getDesignLifetimeYr(): ?int { return $this->designLifetimeYr; }
    public function setDesignLifetimeYr(?int $designLifetimeYr): void { $this->designLifetimeYr = $designLifetimeYr; }

    public function getCommissioningDate(): ?string { return $this->commissioningDate; }
    public function setCommissioningDate(?string $commissioningDate): void { $this->commissioningDate = $commissioningDate; }

    public function getFirstCriticality(): ?string { return $this->firstCriticality; }
    public function setFirstCriticality(?string $firstCriticality): void { $this->firstCriticality = $firstCriticality; }

    public function getLastInspectionAt(): ?string { return $this->lastInspectionAt; }
    public function setLastInspectionAt(?string $lastInspectionAt): void { $this->lastInspectionAt = $lastInspectionAt; }

    public function getNextPlannedOutage(): ?string { return $this->nextPlannedOutage; }
    public function setNextPlannedOutage(?string $nextPlannedOutage): void { $this->nextPlannedOutage = $nextPlannedOutage; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }
}