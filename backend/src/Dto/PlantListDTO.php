<?php

require_once __DIR__ . '/BaseDTO.php';

class PlantListDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $country = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly string $status = 'DRAFT',
        public readonly ?string $createdBy = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromDbArray(array $row): self {
        return new self(
            id: $row['id'],
            name: $row['name'] ?? 'Fără nume',
            country: $row['country'] ?? null,
            latitude: $row['latitude'] !== null ? (float) $row['latitude'] : null,
            longitude: $row['longitude'] !== null ? (float) $row['longitude'] : null,
            status: $row['status'],
            createdBy: $row['created_by'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }
}
