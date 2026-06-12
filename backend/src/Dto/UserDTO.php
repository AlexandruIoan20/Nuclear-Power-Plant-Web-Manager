<?php

require_once __DIR__ . '/BaseDTO.php';

class UserDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
    ) {}
}
