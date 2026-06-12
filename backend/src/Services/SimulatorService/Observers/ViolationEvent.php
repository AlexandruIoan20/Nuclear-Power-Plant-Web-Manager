<?php

require_once __DIR__ . '/../../../Entities/ReactorSensor.php';

class ViolationEvent {
    private string $severity;
    private float $value;
    private ReactorSensor $sensor;
    private string $reactorId;
    private string $plantId;
    private ?float $threshold;
    private string $timestamp;

    public function __construct(
        string $severity,
        float $value,
        ReactorSensor $sensor,
        string $reactorId,
        string $plantId,
        ?float $threshold = null
    ) {
        $this->severity = $severity;
        $this->value = $value;
        $this->sensor = $sensor;
        $this->reactorId = $reactorId;
        $this->plantId = $plantId;
        $this->threshold = $threshold;
        $this->timestamp = date('Y-m-d H:i:s');
    }

    public function getSeverity(): string { return $this->severity; }
    public function getValue(): float { return $this->value; }
    public function getSensor(): ReactorSensor { return $this->sensor; }
    public function getReactorId(): string { return $this->reactorId; }
    public function getPlantId(): string { return $this->plantId; }
    public function getThreshold(): ?float { return $this->threshold; }
    public function getTimestamp(): string { return $this->timestamp; }

    public function toAlertData(): array {
        $severityToType = [
            'WARNING' => 'ALERT',
            'ALERT' => 'ALARM',
            'EMERGENCY' => 'SCRAM',
        ];

        $sensor = $this->sensor;
        $type = $severityToType[$this->severity] ?? 'ALERT';

        $sensorTypeLabel = $sensor->getSensorType()->value;
        $unit = $sensor->getUnitOfMeasure() ?? '';
        $thresholdStr = $this->threshold !== null ? " (prag {$this->severity}: {$this->threshold}{$unit})" : '';

        return [
            'reactor_id'  => $this->reactorId,
            'plant_id'    => $this->plantId,
            'type'        => $type,
            'severity'    => $this->severity,
            'sensor_type' => $sensorTypeLabel,
            'value'       => $this->value,
            'threshold'   => $this->threshold,
            'message'     => "Senzor {$sensorTypeLabel} pe reactorul {$this->reactorId}: valoare {$this->value}{$unit}{$thresholdStr}",
        ];
    }
}
