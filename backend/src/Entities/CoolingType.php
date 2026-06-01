<?php

enum CoolingType: string {
    case ONCE_THROUGH_FRESH = 'ONCE_THROUGH_FRESH';
    case ONCE_THROUGH_SALT = 'ONCE_THROUGH_SALT';
    case NATURAL_DRAFT_WET = 'NATURAL_DRAFT_WET';
    case MECHANICAL_DRAFT_WET = 'MECHANICAL_DRAFT_WET';
    case DRY_COOLING = 'DRY_COOLING';
    case HYBRID = 'HYBRID';
    case COOLING_POND = 'COOLING_POND';

    public function label(): string {
        return match($this) {
            self::ONCE_THROUGH_FRESH => 'Circuit deschis (Apă dulce)',
            self::ONCE_THROUGH_SALT => 'Circuit deschis (Apă sărată)',
            self::NATURAL_DRAFT_WET => 'Turn natural umed',
            self::MECHANICAL_DRAFT_WET => 'Turn mecanic umed',
            self::DRY_COOLING => 'Răcire uscată',
            self::HYBRID => 'Hibrid (Wet/Dry)',
            self::COOLING_POND => 'Iaz de răcire',
        };
    }
}