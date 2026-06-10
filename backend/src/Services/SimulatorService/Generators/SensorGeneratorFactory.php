<?php 

require_once __DIR__ . '/SensorGeneratorStrategy.php';
require_once __DIR__ . '/ThermocoupleStrategy.php';
require_once __DIR__ . '/NeutronDetectorStrategy.php';
require_once __DIR__ . '/PressureTransducerStrategy.php';
require_once __DIR__ . '/FlowMeterStrategy.php';
require_once __DIR__ . '/RadiationMonitorStrategy.php';
require_once __DIR__ . '/VibrationSensorStrategy.php';
require_once __DIR__ . '/LevelSensorStrategy.php';
require_once __DIR__ . '/ActivityMonitorStrategy.php';
require_once __DIR__ . '/SeismicSensorStrategy.php';
require_once __DIR__ . '/HydrogenDetectorStrategy.php';
require_once __DIR__ . '/ValvePositionStrategy.php';
require_once __DIR__ . '/PumpSpeedStrategy.php';
require_once __DIR__ . '/../../../Entities/SensorType.php';

class SensorGeneratorFactory { 
    private array $strategies = []; 

    public function __construct() { 
        $this->strategies = [
            SensorType::THERMOCOUPLE->value => new ThermocoupleStrategy(),
            SensorType::NEUTRON_DETECTOR->value => new NeutronDetectorStrategy(),
            SensorType::PRESSURE_TRANSDUCER->value => new PressureTransducerStrategy(),
            SensorType::FLOW_METER->value => new FlowMeterStrategy(),
            SensorType::RADIATION_MONITOR->value => new RadiationMonitorStrategy(),
            SensorType::VIBRATION_SENSOR->value => new VibrationSensorStrategy(),
            SensorType::LEVEL_SENSOR->value => new LevelSensorStrategy(),
            SensorType::ACTIVITY_MONITOR->value => new ActivityMonitorStrategy(),
            SensorType::SEISMIC_SENSOR->value => new SeismicSensorStrategy(),
            SensorType::HYDROGEN_DETECTOR->value => new HydrogenDetectorStrategy(),
            SensorType::VALVE_POSITION->value => new ValvePositionStrategy(),
            SensorType::PUMP_SPEED->value => new PumpSpeedStrategy(),
        ];
    }

    public function getStrategy(SensorType $type): SensorGeneratorStrategy { 
        $strategy = $this->strategies[$type->value] ?? null; 

        if($strategy === null) throw new InvalidArgumentException("Nu exista nicio strategie de generare pentru tipul de senzor: {$type->value}"); 
        return $strategy; 
    }
}
