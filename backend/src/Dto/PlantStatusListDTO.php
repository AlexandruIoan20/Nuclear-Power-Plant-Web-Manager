<?php

require_once __DIR__ . '/BaseDTO.php';

class PlantStatusListDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $createdBy = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromDbArray(array $row): self {
        return new self(
            id: $row['id'],
            name: $row['name'],
            status: $row['status'],
            createdBy: $row['created_by'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }
}
