<?php

require_once __DIR__ . '/BaseDTO.php';

class GeoLocationPreviewDTO extends BaseDTO {
    public function __construct(
        public readonly ?string $country = null,
        public readonly ?string $soilType = null,
        public readonly ?string $waterSourceType = null,
        public readonly ?float $seismicStability = null,
        public readonly ?float $floodRisk = null,
        public readonly ?float $groundwaterLevel = null,
        public readonly ?float $waterProximity = null,
        public readonly ?float $waterFlowRate = null,
        public readonly ?float $populationDensity = null,
        public readonly ?float $transportInfrastructureScore = null,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            country: $data['country'] ?? null,
            soilType: self::stringify($data['soilType'] ?? null),
            waterSourceType: self::stringify($data['waterSourceType'] ?? null),
            seismicStability: $data['seismicStability'] ?? null,
            floodRisk: $data['floodRisk'] ?? null,
            groundwaterLevel: $data['groundwaterLevel'] ?? null,
            waterProximity: $data['waterProximity'] ?? null,
            waterFlowRate: $data['waterFlowRate'] ?? null,
            populationDensity: $data['populationDensity'] ?? null,
            transportInfrastructureScore: $data['transportInfrastructureScore'] ?? null,
        );
    }

    private static function stringify(mixed $value): ?string {
        if ($value === null || is_string($value)) {
            return $value;
        }
        if (is_object($value) && enum_exists($value::class)) {
            return $value instanceof \BackedEnum ? $value->value : $value->name;
        }
        return (string) $value;
    }
}
