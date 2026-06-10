<?php  

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class ThermocoupleStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.003;

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin(); 
        $max = $sensor->getNormalMax();

        $range = $max - $min; 
        $maxStep = $range * self::MAX_STEP_PERCENT; 

        $delta = $this->gaussianRandom(0, $maxStep); 

        $center = ($min + $max) / 2; 
        $pullToCenter = ($center - $currentValue) * 0.002;

        $newValue = $currentValue + $delta + $pullToCenter; 

        return max(-273.15, $newValue); 
    }
}