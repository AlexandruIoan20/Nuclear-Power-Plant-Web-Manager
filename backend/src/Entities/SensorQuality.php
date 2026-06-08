<?php 

enum SensorQuality: string {
    case GOOD = 'GOOD';
    case SUSPECT = 'SUSPECT';
    case BAD = 'BAD';
    case MAINTENANCE = 'MAINTENANCE';
    case SIMULATED = 'SIMULATED';

    public function label(): string {
        return match($this) {
            self::GOOD => 'Bună',
            self::SUSPECT => 'Suspectă',
            self::BAD => 'Rea',
            self::MAINTENANCE => 'În mentenanță',
            self::SIMULATED => 'Simulată',
        };
    }
}