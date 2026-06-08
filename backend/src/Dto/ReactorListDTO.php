<?php

class ReactorListDTO {
    public string $id;
    public string $reactorCode;
    public string $reactorType;
    public string $coolingType;
    public string $operationalStatus;
    public ?float $thermalPowerMw;
    public ?float $electricalPowerMw;

    public static function fromEntity(Reactor $r): self {
        $dto = new self();
        $dto->id = $r->getId();
        $dto->reactorCode = $r->getReactorCode();
        $dto->reactorType = $r->getReactorType()->value;
        $dto->coolingType = $r->getCoolingType()->value;
        $dto->operationalStatus = $r->getOperationalStatus()->value;
        $dto->thermalPowerMw = $r->getThermalPowerMw();
        $dto->electricalPowerMw = $r->getElectricalPowerMw();
        
        return $dto;
    }
}