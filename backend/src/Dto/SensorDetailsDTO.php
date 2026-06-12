<?php

require_once __DIR__ . '/BaseDTO.php';

class SensorDetailsDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $reactorId,
        public readonly string $sensorCode,
        public readonly string $sensorType,
        public readonly ?string $description = null,
        public readonly ?string $locationZone = null,
        public readonly ?string $unitOfMeasure = null,
        public readonly ?float $normalMin = null,
        public readonly ?float $normalMax = null,
        public readonly ?float $alarmLow = null,
        public readonly ?float $alarmHigh = null,
        public readonly ?float $alertLow = null,
        public readonly ?float $alertHigh = null,
        public readonly ?float $scramLow = null,
        public readonly ?float $scramHigh = null,
        public readonly string $status,
        public readonly bool $isActive = false,
        public readonly ?string $lastCalibration = null,
        public readonly ?string $calibrationDue = null,
        public readonly ?float $currentValue = null,
        public readonly ?string $lastReadingAt = null,
        public readonly ?string $createdAt = null,
    ) {}

    public static function fromEntity(ReactorSensor $s): self {
        return new self(
            id: $s->getId(),
            reactorId: $s->getReactorId(),
            sensorCode: $s->getSensorCode(),
            sensorType: $s->getSensorType()->value,
            description: $s->getDescription(),
            locationZone: $s->getLocationZone(),
            unitOfMeasure: $s->getUnitOfMeasure(),
            normalMin: $s->getNormalMin(),
            normalMax: $s->getNormalMax(),
            alarmLow: $s->getAlarmLow(),
            alarmHigh: $s->getAlarmHigh(),
            alertLow: $s->getAlertLow(),
            alertHigh: $s->getAlertHigh(),
            scramLow: $s->getScramLow(),
            scramHigh: $s->getScramHigh(),
            status: $s->getStatus()->value,
            isActive: $s->getIsActive(),
            lastCalibration: $s->getLastCalibration(),
            calibrationDue: $s->getCalibrationDue(),
            currentValue: $s->getCurrentValue(),
            lastReadingAt: $s->getLastReadingAt(),
            createdAt: $s->getCreatedAt(),
        );
    }
}