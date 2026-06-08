<?php 

class SensorReading {
    private string $id;
    private string $sensorId;
    private string $timestamp;
    private float $value;
    private SensorQuality $quality;
    private ?float $rawValue;

    public function __construct(
        string $sensorId,
        float $value,
        ?string $id = null,
        ?string $timestamp = null,
        SensorQuality $quality = SensorQuality::GOOD,
        ?float $rawValue = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16));
        $this->sensorId = $sensorId;
        $this->value = $value;
        $this->timestamp = $timestamp ?? date('Y-m-d H:i:s');
        $this->quality = $quality;
        $this->rawValue = $rawValue;
    }

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }
    public function getSensorId(): string { return $this->sensorId; }
    public function setSensorId(string $sensorId): void { $this->sensorId = $sensorId; }
    public function getTimestamp(): string { return $this->timestamp; }
    public function setTimestamp(string $timestamp): void { $this->timestamp = $timestamp; }
    public function getValue(): float { return $this->value; }
    public function setValue(float $value): void { $this->value = $value; }
    public function getQuality(): SensorQuality { return $this->quality; }
    public function setQuality(SensorQuality $quality): void { $this->quality = $quality; }
    public function getRawValue(): ?float { return $this->rawValue; }
    public function setRawValue(?float $rawValue): void { $this->rawValue = $rawValue; }
}