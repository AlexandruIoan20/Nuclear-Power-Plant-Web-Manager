<?php

require_once __DIR__ . '/BaseDTO.php';

class ReactorDetailsDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $powerPlantId,
        public readonly string $reactorCode,
        public readonly string $reactorType,
        public readonly string $coolingType,
        public readonly string $operationalStatus,
        public readonly ?float $thermalPowerMw = null,
        public readonly ?float $electricalPowerMw = null,
        public readonly ?int $fuelCycleDays = null,
        public readonly ?int $currentCycleDay = null,
        public readonly ?float $wearIndex = null,
        public readonly ?int $designLifetimeYr = null,
        public readonly ?string $commissioningDate = null,
        public readonly ?string $firstCriticality = null,
        public readonly ?string $lastInspectionAt = null,
        public readonly ?string $nextPlannedOutage = null,
        public readonly ?string $description = null,
        public readonly ?string $createdAt = null,
    ) {}

    public static function fromEntity(Reactor $r): self {
        return new self(
            id: $r->getId(),
            powerPlantId: $r->getPowerPlantId(),
            reactorCode: $r->getReactorCode(),
            reactorType: $r->getReactorType()->value,
            coolingType: $r->getCoolingType()->value,
            operationalStatus: $r->getOperationalStatus()->value,
            thermalPowerMw: $r->getThermalPowerMw(),
            electricalPowerMw: $r->getElectricalPowerMw(),
            fuelCycleDays: $r->getFuelCycleDays(),
            currentCycleDay: $r->getCurrentCycleDay(),
            wearIndex: $r->getWearIndex(),
            designLifetimeYr: $r->getDesignLifetimeYr(),
            commissioningDate: $r->getCommissioningDate(),
            firstCriticality: $r->getFirstCriticality(),
            lastInspectionAt: $r->getLastInspectionAt(),
            nextPlannedOutage: $r->getNextPlannedOutage(),
            description: $r->getDescription(),
            createdAt: $r->getCreatedAt(),
        );
    }
}