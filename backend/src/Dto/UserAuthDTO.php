<?php

require_once __DIR__ . '/UserDTO.php';

class UserAuthDTO extends UserDTO {
    public function __construct(
        string $id,
        string $username,
        string $email,
        string $role,
        ?string $firstName = null,
        ?string $lastName = null,
        public readonly string $passwordHash = '',
    ) {
        parent::__construct(
            id: $id,
            username: $username,
            email: $email,
            role: $role,
            firstName: $firstName,
            lastName: $lastName,
        );
    }

    public static function fromEntity(User $user): self {
        return new self(
            id: $user->getId(),
            username: $user->getUsername(),
            email: $user->getEmail(),
            role: $user->getRole(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            passwordHash: $user->getPasswordHash(),
        );
    }
}
