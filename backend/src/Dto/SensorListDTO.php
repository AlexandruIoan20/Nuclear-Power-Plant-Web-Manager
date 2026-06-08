<?php 

class SensorListDTO {
    public string $id;
    public string $reactorId;
    public string $sensorCode;
    public string $sensorType;
    public string $status;
    public ?float $currentValue;
    public ?string $unitOfMeasure;
    public bool $isActive;
    public ?string $lastReadingAt;

    public static function fromEntity(ReactorSensor $s): self {
        $dto = new self();
        $dto->id = $s->getId();
        $dto->reactorId = $s->getReactorId();
        $dto->sensorCode = $s->getSensorCode();
        $dto->sensorType = $s->getSensorType()->value;
        $dto->status = $s->getStatus()->value;
        $dto->currentValue = $s->getCurrentValue();
        $dto->unitOfMeasure = $s->getUnitOfMeasure();
        $dto->isActive = $s->getIsActive();
        $dto->lastReadingAt = $s->getLastReadingAt();
        
        return $dto;
    }
}