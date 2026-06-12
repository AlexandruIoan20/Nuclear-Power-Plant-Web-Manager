<?php 

require_once __DIR__ . '/../Entities/ReactorSensor.php'; 
require_once __DIR__ . '/BaseDTO.php'; 

class StreamSensorDTO extends BaseDTO { 
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $type,
        public readonly ?string $description,
        public readonly ?string $location,
        public readonly ?string $unit,
        public readonly ?float $value,
        public readonly ?float $normalMin,
        public readonly ?float $normalMax,
        public readonly ?float $alarmLow,
        public readonly ?float $alarmHigh,
        public readonly ?float $alertLow,
        public readonly ?float $alertHigh,
        public readonly ?float $scramLow,
        public readonly ?float $scramHigh,
        public readonly string $status,
    ) {}

    public static function fromEntity(ReactorSensor $s): self { 
        return new self(
            id: $s->getId(),
            code: $s->getSensorCode(),
            type: $s->getSensorType()->value,
            description: $s->getDescription(),
            location: $s->getLocationZone(),
            unit: $s->getUnitOfMeasure(),
            value: $s->getCurrentValue(),
            normalMin: $s->getNormalMin(),
            normalMax: $s->getNormalMax(),
            alarmLow: $s->getAlarmLow(),
            alarmHigh: $s->getAlarmHigh(),
            alertLow: $s->getAlertLow(),
            alertHigh: $s->getAlertHigh(),
            scramLow: $s->getScramLow(),
            scramHigh: $s->getScramHigh(),
            status: $s->getStatus()->value,
        );
    }
}

class ReactorStreamDTO extends BaseDTO {
    public function __construct(
        public readonly string $timestamp,
        public readonly string $reactorId,
        public readonly array $sensors = [],
    ) {}

    public static function create(string $reactorId, array $sensorEntities): self { 
        return new self(
            timestamp: date('Y-m-d H:i:s'),
            reactorId: $reactorId,
            sensors: array_map(fn($s) => StreamSensorDTO::fromEntity($s), $sensorEntities),
        );
    }
}