<?php

require_once __DIR__ . '/BaseDTO.php';
require_once __DIR__ . '/../Entities/Alert.php';

class AlertListDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $plantId,
        public readonly string $type,
        public readonly string $message,
        public readonly ?string $createdAt = null,
    ) {}

    public static function fromEntity(Alert $a): self {
        return new self(
            id: $a->getId(),
            plantId: $a->getPlantId(),
            type: $a->getType(),
            message: $a->getMessage(),
            createdAt: $a->getCreatedAt(),
        );
    }
}
