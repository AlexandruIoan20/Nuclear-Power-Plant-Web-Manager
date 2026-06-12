<?php

require_once __DIR__ . '/BaseDTO.php';

class ReactorListDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $reactorCode,
        public readonly string $reactorType,
        public readonly string $coolingType,
        public readonly string $operationalStatus,
        public readonly ?float $thermalPowerMw = null,
        public readonly ?float $electricalPowerMw = null,
    ) {}

    public static function fromEntity(Reactor $r): self {
        return new self(
            id: $r->getId(),
            reactorCode: $r->getReactorCode(),
            reactorType: $r->getReactorType()->value,
            coolingType: $r->getCoolingType()->value,
            operationalStatus: $r->getOperationalStatus()->value,
            thermalPowerMw: $r->getThermalPowerMw(),
            electricalPowerMw: $r->getElectricalPowerMw(),
        );
    }
}