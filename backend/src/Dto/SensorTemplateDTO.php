<?php

class SensorTemplateDTO {
    public string $id;
    public string $reactorType;
    public string $sensorCode;
    public string $sensorType;
    public string $description;
    public ?string $locationZone;
    public ?string $unitOfMeasure;
    public ?float $normalMin;
    public ?float $normalMax;
    public ?float $alarmLow;
    public ?float $alarmHigh;
    public ?float $alertLow;
    public ?float $alertHigh;
    public ?float $scramLow;
    public ?float $scramHigh;

    public static function fromEntity(SensorTemplate $t): self {
        $dto = new self();
        $dto->id = $t->getId();
        $dto->reactorType = $t->getReactorType()->value;
        $dto->sensorCode = $t->getSensorCode();
        $dto->sensorType = $t->getSensorType()->value;
        $dto->description = $t->getDescription();
        $dto->locationZone = $t->getLocationZone();
        $dto->unitOfMeasure = $t->getUnitOfMeasure();
        $dto->normalMin = $t->getNormalMin();
        $dto->normalMax = $t->getNormalMax();
        $dto->alarmLow = $t->getAlarmLow();
        $dto->alarmHigh = $t->getAlarmHigh();
        $dto->alertLow = $t->getAlertLow();
        $dto->alertHigh = $t->getAlertHigh();
        $dto->scramLow = $t->getScramLow();
        $dto->scramHigh = $t->getScramHigh();
        return $dto;
    }
}
