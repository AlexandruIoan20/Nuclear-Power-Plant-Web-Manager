<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class PressureTransducerStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.004; 
    private const PRESSURE_DROP_PROBABILITY = 0.005; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin(); 
        $max = $sensor->getNormalMax(); 

        $range = $max - $min; 
        $maxStep = $range * self::MAX_STEP_PERCENT; 

        $delta = $this->gaussianRandom(0, $maxStep);    

        if((mt_rand(1, 1000) / 1000) < self::PRESSURE_DROP_PROBABILITY) $delta -= $range * 0.02; 
        
        $center = ($min + $max) / 2; 
        $pullToCenter = ($center - $currentValue) * 0.005;

        $newValue = $currentValue + $delta + $pullToCenter; 

        return max(0, $newValue); 
    }
}