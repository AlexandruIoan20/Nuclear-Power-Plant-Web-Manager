<?php 

require_once __DIR__ . '/../Entities/ReactorSensor.php'; 

class StreamSensorDTO { 
    public string $id; 
    public string $code; 
    public string $type; 
    public ?string $description; 
    public ?string $location; 
    public ?string $unit; 
    public ?float $value; 
    public ?float $normalMin; 
    public ?float $normalMax; 
    public ?float $alarmLow; 
    public ?float $alarmHigh; 
    public ?float $alertLow; 
    public ?float $alertHigh; 
    public ?float $scramLow; 
    public ?float $scramHigh; 
    public string $status; 

    public static function fromEntity(ReactorSensor $s): self { 
        $dto = new self(); 
        
        $dto->id = $s->getId(); 
        $dto->code = $s->getSensorCode();
        $dto->type = $s->getSensorType()->value; 
        $dto->description = $s->getDescription(); 
        $dto->location = $s->getLocationZone(); 
        $dto->unit = $s->getUnitOfMeasure(); 
        $dto->value = $s->getCurrentValue(); 
        $dto->normalMin = $s->getNormalMin(); 
        $dto->normalMax = $s->getNormalMax(); 
        $dto->alarmLow = $s->getAlarmLow(); 
        $dto->alarmHigh = $s->getAlarmHigh(); 
        $dto->alertLow = $s->getAlertLow(); 
        $dto->alertHigh = $s->getAlertHigh(); 
        $dto->scramLow = $s->getScramLow(); 
        $dto->scramHigh = $s->getScramHigh(); 
        $dto->status = $s->getStatus()->value; 
        
        return $dto;
    }
}

class ReactorStreamDTO {
    public string $timestamp;
    public string $reactorId; 

    public array $sensors = []; 

    public static function create(string $reactorId, array $sensorEntities): self { 
        $dto = new self(); 
        $dto->timestamp = date('Y-m-d H:i:s'); 
        $dto->reactorId = $reactorId; 

        $dto->sensors = array_map(fn($s) => StreamSensorDTO::fromEntity($s), $sensorEntities); 

        return $dto; 
    }
}