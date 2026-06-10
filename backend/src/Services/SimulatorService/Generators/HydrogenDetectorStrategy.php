<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 


class HydrogenDetectorStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.003;
    private const ACCUMULATION_PROB = 0.006; 
    private const ACCUMULATION_RATE = 0.015; 
    private const RECOMBINATION_RATE = 0.005; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin();
        $max = $sensor->getNormalMax();

        $range   = $max - $min;
        $maxStep = $range * self::MAX_STEP_PERCENT;

        $delta = $this->gaussianRandom(0, $maxStep * 0.3);

        if ((mt_rand(1, 1000) / 1000) < self::ACCUMULATION_PROB) $delta += $range * self::ACCUMULATION_RATE;

        $safeBaseline = $min + $range * 0.05;
        $recombination = ($safeBaseline - $currentValue) * self::RECOMBINATION_RATE;

        $newValue = $currentValue + $delta + $recombination;

        return max(0, $newValue);
    }
}