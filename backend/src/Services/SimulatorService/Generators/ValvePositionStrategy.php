<?php

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php';

class ValvePositionStrategy extends AbstractSensorGeneratorStrategy {
    private const STEP_SIZE = 1.0;
    private const MOVE_PROBABILITY = 0.05;
    private const MAX_MOVE_STEPS = 3;

    public function generate(float $currentValue, ReactorSensor $sensor): float {
        $min = $sensor->getNormalMin();
        $max = $sensor->getNormalMax();

        if ((mt_rand(1, 100) / 100) > self::MOVE_PROBABILITY) {
            return $currentValue;
        }

        $steps = mt_rand(1, self::MAX_MOVE_STEPS);
        $direction = mt_rand(0, 1) === 1 ? 1 : -1;

        $setpoint = ($min + $max) / 2;
        if (abs($setpoint - $currentValue) > 10) {
            $direction = $currentValue < $setpoint ? 1 : -1;
        }

        $newValue = $currentValue + ($direction * $steps * self::STEP_SIZE);

        return max(0, $newValue);
    }
}
