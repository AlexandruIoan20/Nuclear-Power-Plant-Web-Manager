<?php 

require_once __DIR__ . '/AbstractSensorGeneratorStrategy.php'; 
require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

class PumpSpeedStrategy extends AbstractSensorGeneratorStrategy { 
    private const MAX_STEP_PERCENT = 0.004;
    private const TRIP_PROBABILITY = 0.002; 
    private const RUNUP_RATE = 0.015; 

    public function generate(float $currentValue, ReactorSensor $sensor): float { 
        $min = $sensor->getNormalMin(); 
        $max = $sensor->getNormalMax();

        $range = $max - $min; 
        $maxStep = $range * self::MAX_STEP_PERCENT; 

        $delta = $this->gaussianRandom(0, $maxStep); 

        if((mt_rand(1, 1000) / 1000)  < self::TRIP_PROBABILITY) $delta -= $currentValue * 0.20; 

        $nominalSpeed = ($min + $max) / 2; 
        if($currentValue < $nominalSpeed * 0.3) { 
            $delta += $nominalSpeed * self::RUNUP_RATE; 
        } else { 
            $pullToNominal = ($nominalSpeed - $currentValue) * 0.006; 
            $delta += $pullToNominal; 
        }

        $newValue = $currentValue + $delta; 

        return max(0, $newValue); 
    }
}