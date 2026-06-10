<?php  

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class LevelSensorStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.002; 
    private const LEAK_PROBABILITY = 0.003; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin();
        $max = $sensor->getNormalMax(); 
 
        $range   = $max - $min;
        $maxStep = $range * self::MAX_STEP_PERCENT;

        $delta = $this->gaussianRandom(0, $maxStep * 0.3);

        if ((mt_rand(1, 1000) / 1000) < self::LEAK_PROBABILITY) $delta -= $range * 0.005;
        
        $setpoint     = ($min + $max) / 2;
        $controlForce = ($setpoint - $currentValue) * 0.01;
 
        $newValue = $currentValue + $delta + $controlForce;

        return max(0, $newValue); 
    }

}