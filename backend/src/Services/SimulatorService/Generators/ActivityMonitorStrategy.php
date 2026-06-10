<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class ActivityMonitorStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.005;
    private const SPIKE_PROBABILITY = 0.004; 
    private const SPIKE_MAGNITUDE = 0.20;  
    private const DECAY_RATE = 0.008; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin();
        $max = $sensor->getNormalMax();
 
        $range   = $max - $min;
        $maxStep = $range * self::MAX_STEP_PERCENT;

        $delta = $this->gaussianRandom(0, $maxStep);
        if ((mt_rand(1, 1000) / 1000) < self::SPIKE_PROBABILITY)  $delta += $range * self::SPIKE_MAGNITUDE;

        $baseline = $min + $range * 0.08;
        $pullToBaseline = ($baseline - $currentValue) * self::DECAY_RATE;
 
        $newValue = $currentValue + $delta + $pullToBaseline;

        return max(0, $newValue); 
    }
}