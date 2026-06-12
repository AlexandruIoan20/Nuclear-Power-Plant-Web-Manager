<?php 

require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

interface SensorGeneratorStrategy { 
    public function generate(float $currentValue, ReactorSensor $sensor): float; 
}