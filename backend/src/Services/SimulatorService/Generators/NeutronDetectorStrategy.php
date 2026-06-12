<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class NeutronDetectorStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.015; 
    private const SPIKE_PROBABILITY = 0.02;
    private const SPIKE_MULTIPLIER = 1.08; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin(); 
        $max = $sensor->getNormalMax() ;

        $range = $max - $min; 
        $maxStep = $range * self::MAX_STEP_PERCENT; 

        $statisticalNoise = $this->gaussianRandom(0, $maxStep * 0.6); 
        $drift = $this->gaussianRandom(0, $maxStep * 0.4); 
        
        $newValue = $currentValue + $statisticalNoise + $drift; 

        if((mt_rand(1, 1000) / 1000) < self::SPIKE_PROBABILITY) $newValue += self::SPIKE_MULTIPLIER; 

        $center = ($min + $max) / 2; 
        $pullToCenter = ($center - $currentValue) * 0.003; 
        $newValue += $pullToCenter; 

        return max(0, $newValue); 
    }
}