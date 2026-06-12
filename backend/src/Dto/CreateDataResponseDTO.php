<?php 

require_once __DIR__ . '/BaseDTO.php';

class CreateDataResponseDTO extends BaseDTO { 
    public function __construct(
        public readonly string $dataId,
        public readonly ?string $plantId = null,
        public readonly string $message = 'Creat cu succes.',
    ) {}
}