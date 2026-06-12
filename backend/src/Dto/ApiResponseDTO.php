<?php

require_once __DIR__ . '/BaseDTO.php';

class ApiResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly mixed $data = null,
        public readonly ?string $message = null,
    ) {}
}
