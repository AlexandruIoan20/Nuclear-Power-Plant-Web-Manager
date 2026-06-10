<?php

require_once __DIR__ . '/../../../Entities/ReactorSensor.php';

class ViolationEvent {
    private string $severity;
    private float $value;
    private ReactorSensor $sensor;
    private string $reactorId;
    private string $timestamp;

    public function __construct(
        string $severity,
        float $value,
        ReactorSensor $sensor,
        string $reactorId
    ) {
        $this->severity = $severity;
        $this->value = $value;
        $this->sensor = $sensor;
        $this->reactorId = $reactorId;
        $this->timestamp = date('Y-m-d H:i:s');
    }

    public function getSeverity(): string { return $this->severity; }
    public function getValue(): float { return $this->value; }
    public function getSensor(): ReactorSensor { return $this->sensor; }
    public function getReactorId(): string { return $this->reactorId; }
    public function getTimestamp(): string { return $this->timestamp; }
}
