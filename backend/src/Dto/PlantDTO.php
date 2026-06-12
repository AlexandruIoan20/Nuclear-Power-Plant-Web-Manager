<?php

require_once __DIR__ . '/BaseDTO.php';

class PlantDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $createdBy = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromEntity(Plant $plant): self {
        return new self(
            id: $plant->getId(),
            name: $plant->getName(),
            status: $plant->getStatus()->name,
            createdBy: $plant->getCreatedBy(),
            createdAt: $plant->getCreatedAt(),
            updatedAt: $plant->getUpdatedAt()
        );
    }
}
