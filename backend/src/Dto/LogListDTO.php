<?php

require_once __DIR__ . '/BaseDTO.php';
require_once __DIR__ . '/../Entities/Log.php';

class LogListDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $level,
        public readonly string $message,
        public readonly ?array $context = null,
        public readonly ?string $userId = null,
        public readonly ?string $plantId = null,
        public readonly ?string $reactorId = null,
        public readonly ?string $source = null,
        public readonly ?string $requestUri = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $createdAt = null,
    ) {}

    public static function fromEntity(Log $log): self {
        return new self(
            id: $log->getId(),
            level: $log->getLevel(),
            message: $log->getMessage(),
            context: $log->getContext(),
            userId: $log->getUserId(),
            plantId: $log->getPlantId(),
            reactorId: $log->getReactorId(),
            source: $log->getSource(),
            requestUri: $log->getRequestUri(),
            ipAddress: $log->getIpAddress(),
            createdAt: $log->getCreatedAt(),
        );
    }
}
