<?php

class SensorDetailsDTO {
    public string $id;
    public string $reactorId;
    public string $sensorCode;
    public string $sensorType;
    public ?string $description;
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
    public string $status;
    public bool $isActive;
    public ?string $lastCalibration;
    public ?string $calibrationDue;
    public ?float $currentValue;
    public ?string $lastReadingAt;
    public ?string $createdAt;

    public static function fromEntity(ReactorSensor $s): self {
        $dto = new self();
        $dto->id = $s->getId();
        $dto->reactorId = $s->getReactorId();
        $dto->sensorCode = $s->getSensorCode();
        $dto->sensorType = $s->getSensorType()->value;
        $dto->description = $s->getDescription();
        $dto->locationZone = $s->getLocationZone();
        $dto->unitOfMeasure = $s->getUnitOfMeasure();
        $dto->normalMin = $s->getNormalMin();
        $dto->normalMax = $s->getNormalMax();
        $dto->alarmLow = $s->getAlarmLow();
        $dto->alarmHigh = $s->getAlarmHigh();
        $dto->alertLow = $s->getAlertLow();
        $dto->alertHigh = $s->getAlertHigh();
        $dto->scramLow = $s->getScramLow();
        $dto->scramHigh = $s->getScramHigh();
        $dto->status = $s->getStatus()->value;
        $dto->isActive = $s->getIsActive();
        $dto->lastCalibration = $s->getLastCalibration();
        $dto->calibrationDue = $s->getCalibrationDue();
        $dto->currentValue = $s->getCurrentValue();
        $dto->lastReadingAt = $s->getLastReadingAt();
        $dto->createdAt = $s->getCreatedAt();
        
        return $dto;
    }
}
?>