<?php

require_once __DIR__ . '/BaseDTO.php';
require_once __DIR__ . '/GeoLocationPreviewDTO.php';

class CoordinatesPreviewResponseDTO extends BaseDTO {
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly string $coordinatesLabel,
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
        public readonly string $message = 'Locație validată rapid.',
    ) {}

    public static function fromGeoPreview(
        float $latitude,
        float $longitude,
        string $coordinatesLabel,
        ?string $country,
        GeoLocationPreviewDTO $geoPreview
    ): self {
        return new self(
            latitude: $latitude,
            longitude: $longitude,
            coordinatesLabel: $coordinatesLabel,
            country: $country,
            soilType: $geoPreview->soilType,
            waterSourceType: $geoPreview->waterSourceType,
            seismicStability: $geoPreview->seismicStability,
            floodRisk: $geoPreview->floodRisk,
            groundwaterLevel: $geoPreview->groundwaterLevel,
            waterProximity: $geoPreview->waterProximity,
            waterFlowRate: $geoPreview->waterFlowRate,
            populationDensity: $geoPreview->populationDensity,
            transportInfrastructureScore: $geoPreview->transportInfrastructureScore,
        );
    }
}
