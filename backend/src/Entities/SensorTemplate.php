<?php 

class SensorTemplate {
    private string $id; 
    private ReactorType $reactorType;
    private string $sensorCode;
    private SensorType $sensorType;
    private string $description;
    private ?string $locationZone;
    private ?string $unitOfMeasure;
    private ?string $measurementField;
    private ?float $normalMin;
    private ?float $normalMax;
    private ?float $alarmLow;
    private ?float $alarmHigh;
    private ?float $alertLow;
    private ?float $alertHigh;
    private ?float $scramLow;
    private ?float $scramHigh;

    public function __construct(
        ReactorType $reactorType,
        string $sensorCode,
        SensorType $sensorType,
        string $description,
        ?string $id = null,
        ?string $locationZone = null,
        ?string $unitOfMeasure = null,
        ?string $measurementField = null,
        ?float $normalMin = null,
        ?float $normalMax = null,
        ?float $alarmLow = null,
        ?float $alarmHigh = null,
        ?float $alertLow = null,
        ?float $alertHigh = null,
        ?float $scramLow = null,
        ?float $scramHigh = null
    ) {
        $this->id = $id;
        $this->reactorType = $reactorType;
        $this->sensorCode = $sensorCode;
        $this->sensorType = $sensorType;
        $this->description = $description;
        $this->locationZone = $locationZone;
        $this->unitOfMeasure = $unitOfMeasure;
        $this->measurementField = $measurementField;
        $this->normalMin = $normalMin;
        $this->normalMax = $normalMax;
        $this->alarmLow = $alarmLow;
        $this->alarmHigh = $alarmHigh;
        $this->alertLow = $alertLow;
        $this->alertHigh = $alertHigh;
        $this->scramLow = $scramLow;
        $this->scramHigh = $scramHigh;
    }

    public function getId(): string { return $this->id; }
    public function getReactorType(): ReactorType { return $this->reactorType; }
    public function getSensorCode(): string { return $this->sensorCode; }
    public function getSensorType(): SensorType { return $this->sensorType; }
    public function getDescription(): string { return $this->description; }
    public function getLocationZone(): ?string { return $this->locationZone; }
    public function getUnitOfMeasure(): ?string { return $this->unitOfMeasure; }
    public function getMeasurementField(): ?string { return $this->measurementField; }
    public function getNormalMin(): ?float { return $this->normalMin; }
    public function getNormalMax(): ?float { return $this->normalMax; }
    public function getAlarmLow(): ?float { return $this->alarmLow; }
    public function getAlarmHigh(): ?float { return $this->alarmHigh; }
    public function getAlertLow(): ?float { return $this->alertLow; }
    public function getAlertHigh(): ?float { return $this->alertHigh; }
    public function getScramLow(): ?float { return $this->scramLow; }
    public function getScramHigh(): ?float { return $this->scramHigh; }

    public function setId(string $id): void { $this->id = $id; }
    public function setReactorType(ReactorType $reactorType): void { $this->reactorType = $reactorType; }
    public function setSensorCode(string $sensorCode): void { $this->sensorCode = $sensorCode; }
    public function setSensorType(SensorType $sensorType): void { $this->sensorType = $sensorType; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setLocationZone(?string $locationZone): void { $this->locationZone = $locationZone; }
    public function setUnitOfMeasure(?string $unitOfMeasure): void { $this->unitOfMeasure = $unitOfMeasure; }
    public function setMeasurementField(?string $measurementField): void { $this->measurementField = $measurementField; }
    public function setNormalMin(?float $normalMin): void { $this->normalMin = $normalMin; }
    public function setNormalMax(?float $normalMax): void { $this->normalMax = $normalMax; }
    public function setAlarmLow(?float $alarmLow): void { $this->alarmLow = $alarmLow; }
    public function setAlarmHigh(?float $alarmHigh): void { $this->alarmHigh = $alarmHigh; }
    public function setAlertLow(?float $alertLow): void { $this->alertLow = $alertLow; }
    public function setAlertHigh(?float $alertHigh): void { $this->alertHigh = $alertHigh; }
    public function setScramLow(?float $scramLow): void { $this->scramLow = $scramLow; }
    public function setScramHigh(?float $scramHigh): void { $this->scramHigh = $scramHigh; }
}