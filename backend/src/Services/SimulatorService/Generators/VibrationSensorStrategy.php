<?php

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class VibrationSensorStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.02; 
    private const MECHANICAL_EVENT_PROB = 0.01;  
    private const EVENT_MAGNITUDE = 0.25;  
    private const EVENT_DECAY = 0.08; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin(); 
        $max = $sensor->getNormalMax();
 
        $range = $max - $min;
        $maxStep = $range * self::MAX_STEP_PERCENT;

        $backgroundNoise = $this->gaussianRandom(0, $maxStep * 0.5);
 
        $delta = $backgroundNoise;

        if ((mt_rand(1, 1000) / 1000) < self::MECHANICAL_EVENT_PROB) {
            $delta += $range * self::EVENT_MAGNITUDE;
        }

        $baseline = $min + $range * 0.1; 
        $damping = ($baseline - $currentValue) * self::EVENT_DECAY;
 
        $newValue = $currentValue + $delta + $damping;

        return max(0, $newValue);
    }
}