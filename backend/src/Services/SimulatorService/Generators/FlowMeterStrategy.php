<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class FlowMeterStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.005; 
    private const PUMP_TRIP_PROBABILITY = 0.002; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin();
        $max = $sensor->getNormalMax();
 
        $range   = $max - $min;
        $maxStep = $range * self::MAX_STEP_PERCENT;

        $delta = $this->gaussianRandom(0, $maxStep);

        if((mt_rand(1, 1000) / 1000) < self::PUMP_TRIP_PROBABILITY) $delta -= $range * 0.15;

        $center = ($min + $max) / 2; 
        $pullToCenter = ($center - $currentValue) * 0.008; 

        $newValue = $currentValue + $delta + $pullToCenter; 

        return max(0, $newValue); 
    }
}