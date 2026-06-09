<?php

class User { 
    private ?string $id; 
    private string $username; 
    private string $firstName; 
    private string $lastName; 
    private string $email; 
    private string $passwordHash; 
    private string $role; 

    public function __construct(string $username, string $firstName, string $lastName, string $email, string $passwordHash, ?string $id = null, string $role = 'OPERATOR') {
        $this->username = $username; 
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash; 
        $this->id = $id; 
        $this->role = $role; 
    }

    public function getId(): ?string { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getRole(): string { return $this->role; } 

    public function setId(string $id): void { $this->id = $id; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }
    public function setRole(string $role): void { $this->role = $role; } 
}