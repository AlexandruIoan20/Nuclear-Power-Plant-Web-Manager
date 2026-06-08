<?php

class ReactorSensor {
    private string $id;
    private string $reactorId;
    private string $sensorCode;
    private SensorType $sensorType;
    private ?string $description;
    private ?string $locationZone;
    private ?string $unitOfMeasure;
    private ?float $normalMin;
    private ?float $normalMax;
    private ?float $alarmLow;
    private ?float $alarmHigh;
    private ?float $alertLow;
    private ?float $alertHigh;
    private ?float $scramLow;
    private ?float $scramHigh;
    private SensorQuality $status;
    private bool $isActive;
    private ?string $lastCalibration;
    private ?string $calibrationDue;
    private ?float $currentValue;
    private ?string $lastReadingAt;
    private ?string $createdAt;

    public function __construct(
        string $reactorId,
        string $sensorCode,
        SensorType $sensorType,
        ?string $id = null,
        ?string $description = null,
        ?string $locationZone = null,
        ?string $unitOfMeasure = null,
        ?float $normalMin = null,
        ?float $normalMax = null,
        ?float $alarmLow = null,
        ?float $alarmHigh = null,
        ?float $alertLow = null,
        ?float $alertHigh = null,
        ?float $scramLow = null,
        ?float $scramHigh = null,
        SensorQuality $status = SensorQuality::GOOD,
        bool $isActive = true,
        ?string $lastCalibration = null,
        ?string $calibrationDue = null,
        ?float $currentValue = null,
        ?string $lastReadingAt = null,
        ?string $createdAt = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16));
        $this->reactorId = $reactorId;
        $this->sensorCode = $sensorCode;
        $this->sensorType = $sensorType;
        $this->description = $description;
        $this->locationZone = $locationZone;
        $this->unitOfMeasure = $unitOfMeasure;
        $this->normalMin = $normalMin;
        $this->normalMax = $normalMax;
        $this->alarmLow = $alarmLow;
        $this->alarmHigh = $alarmHigh;
        $this->alertLow = $alertLow;
        $this->alertHigh = $alertHigh;
        $this->scramLow = $scramLow;
        $this->scramHigh = $scramHigh;
        $this->status = $status;
        $this->isActive = $isActive;
        $this->lastCalibration = $lastCalibration;
        $this->calibrationDue = $calibrationDue;
        $this->currentValue = $currentValue;
        $this->lastReadingAt = $lastReadingAt;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function getReactorId(): string { return $this->reactorId; }
    public function getSensorCode(): string { return $this->sensorCode; }
    public function getSensorType(): SensorType { return $this->sensorType; }
    public function getDescription(): ?string { return $this->description; }
    public function getLocationZone(): ?string { return $this->locationZone; }
    public function getUnitOfMeasure(): ?string { return $this->unitOfMeasure; }
    public function getNormalMin(): ?float { return $this->normalMin; }
    public function getNormalMax(): ?float { return $this->normalMax; }
    public function getAlarmLow(): ?float { return $this->alarmLow; }
    public function getAlarmHigh(): ?float { return $this->alarmHigh; }
    public function getAlertLow(): ?float { return $this->alertLow; }
    public function getAlertHigh(): ?float { return $this->alertHigh; }
    public function getScramLow(): ?float { return $this->scramLow; }
    public function getScramHigh(): ?float { return $this->scramHigh; }
    public function getStatus(): SensorQuality { return $this->status; }
    public function getIsActive(): bool { return $this->isActive; }
    public function getLastCalibration(): ?string { return $this->lastCalibration; }
    public function getCalibrationDue(): ?string { return $this->calibrationDue; }
    public function getCurrentValue(): ?float { return $this->currentValue; }
    public function getLastReadingAt(): ?string { return $this->lastReadingAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(string $id): void { $this->id = $id; }
    public function setReactorId(string $reactorId): void { $this->reactorId = $reactorId; }
    public function setSensorCode(string $sensorCode): void { $this->sensorCode = $sensorCode; }
    public function setSensorType(SensorType $sensorType): void { $this->sensorType = $sensorType; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setLocationZone(?string $locationZone): void { $this->locationZone = $locationZone; }
    public function setUnitOfMeasure(?string $unitOfMeasure): void { $this->unitOfMeasure = $unitOfMeasure; }
    public function setNormalMin(?float $normalMin): void { $this->normalMin = $normalMin; }
    public function setNormalMax(?float $normalMax): void { $this->normalMax = $normalMax; }
    public function setAlarmLow(?float $alarmLow): void { $this->alarmLow = $alarmLow; }
    public function setAlarmHigh(?float $alarmHigh): void { $this->alarmHigh = $alarmHigh; }
    public function setAlertLow(?float $alertLow): void { $this->alertLow = $alertLow; }
    public function setAlertHigh(?float $alertHigh): void { $this->alertHigh = $alertHigh; }
    public function setScramLow(?float $scramLow): void { $this->scramLow = $scramLow; }
    public function setScramHigh(?float $scramHigh): void { $this->scramHigh = $scramHigh; }
    public function setStatus(SensorQuality $status): void { $this->status = $status; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    public function setLastCalibration(?string $lastCalibration): void { $this->lastCalibration = $lastCalibration; }
    public function setCalibrationDue(?string $calibrationDue): void { $this->calibrationDue = $calibrationDue; }
    public function setCurrentValue(?float $currentValue): void { $this->currentValue = $currentValue; }
    public function setLastReadingAt(?string $lastReadingAt): void { $this->lastReadingAt = $lastReadingAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }
}