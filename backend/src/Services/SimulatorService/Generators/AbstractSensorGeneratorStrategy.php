<?php

require_once __DIR__ . '/SensorGeneratorStrategy.php';
require_once __DIR__ . '/../../../Entities/ReactorSensor.php';

abstract class AbstractSensorGeneratorStrategy implements SensorGeneratorStrategy {
    protected function gaussianRandom(float $mean, float $stdDev): float {
        $u1 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
        $u2 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
        $z = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);

        return $mean + $stdDev * $z;
    }
}
