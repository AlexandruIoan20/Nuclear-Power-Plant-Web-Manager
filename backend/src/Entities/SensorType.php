<?php 

enum SensorType: string {
    case THERMOCOUPLE = 'THERMOCOUPLE';
    case PRESSURE_TRANSDUCER = 'PRESSURE_TRANSDUCER';
    case NEUTRON_DETECTOR = 'NEUTRON_DETECTOR';
    case FLOW_METER = 'FLOW_METER';
    case RADIATION_MONITOR = 'RADIATION_MONITOR';
    case VIBRATION_SENSOR = 'VIBRATION_SENSOR';
    case LEVEL_SENSOR = 'LEVEL_SENSOR';
    case ACTIVITY_MONITOR = 'ACTIVITY_MONITOR';
    case SEISMIC_SENSOR = 'SEISMIC_SENSOR';
    case HYDROGEN_DETECTOR = 'HYDROGEN_DETECTOR';
    case VALVE_POSITION = 'VALVE_POSITION';
    case PUMP_SPEED = 'PUMP_SPEED';

    public function label(): string {
        return match($this) {
            self::THERMOCOUPLE => 'Termocuplu',
            self::PRESSURE_TRANSDUCER => 'Senzor de presiune',
            self::NEUTRON_DETECTOR => 'Detector de neutroni',
            self::FLOW_METER => 'Debitmetru',
            self::RADIATION_MONITOR => 'Monitor de radiații',
            self::VIBRATION_SENSOR => 'Senzor de vibrații',
            self::LEVEL_SENSOR => 'Senzor de nivel',
            self::ACTIVITY_MONITOR => 'Monitor de activitate',
            self::SEISMIC_SENSOR => 'Senzor seismic',
            self::HYDROGEN_DETECTOR => 'Detector de hidrogen',
            self::VALVE_POSITION => 'Poziție valvă',
            self::PUMP_SPEED => 'Viteză pompă',
        };
    }
}