<?php

class FeasibilityConfigHelper {
    private static ?array $config = null;

    public static function get(): array {
        if (self::$config === null) {
            self::$config = json_decode(
                file_get_contents(__DIR__ . '/../../../../../config/feasibility-params.json'),
                true
            );
        }
        return self::$config;
    }
}
