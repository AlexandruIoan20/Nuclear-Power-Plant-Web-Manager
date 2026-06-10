<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class SeismicSensorStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.001;
    private const SEISMIC_EVENT_PROB = 0.0005; 
    private const EVENT_MAGNITUDE = 0.40;  
    private const AFTERSHOCK_DECAY = 0.12;   

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin();
        $max = $sensor->getNormalMax();
 
        $range = $max - $min;
        $maxStep = $range * self::MAX_STEP_PERCENT;

        $delta = $this->gaussianRandom(0, $maxStep * 0.4); 

        if ((mt_rand(1, 1000000) / 1000000) < self::SEISMIC_EVENT_PROB) $delta += $range * self::EVENT_MAGNITUDE;

        $baseline   = $min;
        $aftershock = ($baseline - $currentValue) * self::AFTERSHOCK_DECAY;
 
        $newValue = $currentValue + $delta + $aftershock;

        return max(0, $newValue); 
    }
}