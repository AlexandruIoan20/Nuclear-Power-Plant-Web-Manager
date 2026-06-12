<?php

require_once __DIR__ . '/../../../../Entities/Measurements.php';
require_once __DIR__ . '/../../../../Entities/Reactor.php';
require_once __DIR__ . '/../../../../Entities/ReactorSensor.php';
require_once __DIR__ . '/../../../../Entities/SensorType.php';

class MeasurementBuilder {

    public function build(array $newValues, array $sensors, Reactor $reactor): Measurement {
        $measurement = new Measurement($reactor->getId());

        $powerPercent = null;

        foreach ($sensors as $sensor) {
            $value = $newValues[$sensor->getId()] ?? null;
            if ($value === null) continue;

            $field = $sensor->getMeasurementField();
            if ($field === null) continue;

            $setter = $this->fieldToSetter($field);
            $measurement->$setter($value);

            if ($field === 'power_percent') {
                $powerPercent = $value;
            }
        }

        $efficiency = $this->calculateEfficiency($reactor, $powerPercent);
        if ($efficiency !== null) {
            $measurement->setEfficiency($efficiency);
        }

        $wearDelta = $this->calculateWearDelta($powerPercent ?? 0, $reactor->getDesignLifetimeYr() ?? 40);
        $measurement->setWearDelta($wearDelta);

        return $measurement;
    }

    public function calculateWearDelta(float $powerPercent, int $designLifetimeYr): float {
        $totalLifetimeSeconds = $designLifetimeYr * 365 * 24 * 3600;
        $loadFactor = 0.30 + 0.70 * ($powerPercent / 100);
        return $loadFactor / $totalLifetimeSeconds * 5;
    }

    private function fieldToSetter(string $field): string {
        $parts = explode('_', $field);
        $camel = '';
        
        foreach ($parts as $part) {
            $camel .= ucfirst(strtolower($part));
        }
        
        return 'set' . $camel;
    }

    private function calculateEfficiency(Reactor $reactor, ?float $powerPercent): ?float {
        $thermalMw = $reactor->getThermalPowerMw();
        $electricalMw = $reactor->getElectricalPowerMw();

        if ($thermalMw === null || $electricalMw === null || $powerPercent === null) {
            return null;
        }

        $thermalAtCurrent = $thermalMw * ($powerPercent / 100);
        $electricalAtCurrent = $electricalMw * ($powerPercent / 100);

        if ($thermalAtCurrent <= 0) return null;

        return $electricalAtCurrent / $thermalAtCurrent;
    }
}
