<?php

require_once __DIR__ . '/PlantListDTO.php';

class PlantMapDTO extends PlantListDTO {
    public function __construct(
        string $id,
        string $name,
        ?string $country = null,
        ?float $latitude = null,
        ?float $longitude = null,
        string $status = 'DRAFT',
        ?string $createdBy = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        public readonly bool $hasCoordinates = false,
        public readonly ?string $coordinatesLabel = null,
        public readonly ?string $popupTitle = null,
        public readonly ?string $popupSubtitle = null,
        public readonly ?string $editUrl = null,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            country: $country,
            latitude: $latitude,
            longitude: $longitude,
            status: $status,
            createdBy: $createdBy,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public static function fromDbArray(array $row): self {
        $hasCoordinates = $row['latitude'] !== null && $row['longitude'] !== null;
        $lat = $row['latitude'] !== null ? (float) $row['latitude'] : null;
        $lon = $row['longitude'] !== null ? (float) $row['longitude'] : null;

        return new self(
            id: $row['id'],
            name: $row['name'] ?? 'Fără nume',
            country: $row['country'] ?? null,
            latitude: $lat,
            longitude: $lon,
            status: $row['status'],
            createdBy: $row['created_by'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
            hasCoordinates: $hasCoordinates,
            coordinatesLabel: $hasCoordinates
                ? number_format((float) $row['latitude'], 6, '.', '') . ', ' . number_format((float) $row['longitude'], 6, '.', '')
                : null,
            popupTitle: $row['name'] ?: 'Centrală',
            popupSubtitle: $row['country'] ?: 'Țară nespecificată',
            editUrl: '/pages/power-plants/finish.html?id=' . urlencode($row['id']),
        );
    }
}
