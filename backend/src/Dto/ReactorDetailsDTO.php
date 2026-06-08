<?php

class ReactorDetailsDTO {
    public string $id;
    public string $powerPlantId;
    public string $reactorCode;
    public string $reactorType;
    public string $coolingType;
    public string $operationalStatus;
    public ?float $thermalPowerMw;
    public ?float $electricalPowerMw;
    public ?int $fuelCycleDays;
    public ?int $currentCycleDay;
    public ?float $wearIndex;
    public ?int $designLifetimeYr;
    public ?string $commissioningDate;
    public ?string $firstCriticality;
    public ?string $lastInspectionAt;
    public ?string $nextPlannedOutage;
    public ?string $description;
    public ?string $createdAt;

    public static function fromEntity(Reactor $r): self {
        $dto = new self();
        $dto->id = $r->getId();
        $dto->powerPlantId = $r->getPowerPlantId();
        $dto->reactorCode = $r->getReactorCode();
        $dto->reactorType = $r->getReactorType()->value;
        $dto->coolingType = $r->getCoolingType()->value;
        $dto->operationalStatus = $r->getOperationalStatus()->value;
        $dto->thermalPowerMw = $r->getThermalPowerMw();
        $dto->electricalPowerMw = $r->getElectricalPowerMw();
        $dto->fuelCycleDays = $r->getFuelCycleDays();
        $dto->currentCycleDay = $r->getCurrentCycleDay();
        $dto->wearIndex = $r->getWearIndex();
        $dto->designLifetimeYr = $r->getDesignLifetimeYr();
        $dto->commissioningDate = $r->getCommissioningDate();
        $dto->firstCriticality = $r->getFirstCriticality();
        $dto->lastInspectionAt = $r->getLastInspectionAt();
        $dto->nextPlannedOutage = $r->getNextPlannedOutage();
        $dto->description = $r->getDescription();
        $dto->createdAt = $r->getCreatedAt();
        
        return $dto;
    }
}