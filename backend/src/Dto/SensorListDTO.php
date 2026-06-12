<?php 

require_once __DIR__ . '/BaseDTO.php';

class SensorListDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $reactorId,
        public readonly string $sensorCode,
        public readonly string $sensorType,
        public readonly string $status,
        public readonly ?float $currentValue = null,
        public readonly ?string $unitOfMeasure = null,
        public readonly bool $isActive = false,
        public readonly ?string $lastReadingAt = null,
    ) {}

    public static function fromEntity(ReactorSensor $s): self {
        return new self(
            id: $s->getId(),
            reactorId: $s->getReactorId(),
            sensorCode: $s->getSensorCode(),
            sensorType: $s->getSensorType()->value,
            status: $s->getStatus()->value,
            currentValue: $s->getCurrentValue(),
            unitOfMeasure: $s->getUnitOfMeasure(),
            isActive: $s->getIsActive(),
            lastReadingAt: $s->getLastReadingAt(),
        );
    }
}