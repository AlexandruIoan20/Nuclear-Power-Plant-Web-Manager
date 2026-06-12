<?php

enum ReactorOperationalStatus: string {
    case SHUTDOWN = 'SHUTDOWN';
    case COLD_STANDBY = 'COLD_STANDBY';
    case HOT_STANDBY = 'HOT_STANDBY';
    case STARTUP = 'STARTUP';
    case POWER_ASCENT = 'POWER_ASCENT';
    case FULL_POWER = 'FULL_POWER';
    case PARTIAL_POWER = 'PARTIAL_POWER';
    case PLANNED_OUTAGE = 'PLANNED_OUTAGE';
    case UNPLANNED_OUTAGE = 'UNPLANNED_OUTAGE';
    case EMERGENCY_SHUTDOWN = 'EMERGENCY_SHUTDOWN';

    public function label(): string {
        return match($this) {
            self::SHUTDOWN => 'Oprit',
            self::COLD_STANDBY => 'Rezervă rece',
            self::HOT_STANDBY => 'Rezervă caldă',
            self::STARTUP => 'În curs de pornire',
            self::POWER_ASCENT => 'Creștere în putere',
            self::FULL_POWER => 'Putere nominală (100%)',
            self::PARTIAL_POWER => 'Putere parțială',
            self::PLANNED_OUTAGE => 'Oprire planificată',
            self::UNPLANNED_OUTAGE => 'Oprire neplanificată',
            self::EMERGENCY_SHUTDOWN => 'Oprire de urgență',
        };
    }
}