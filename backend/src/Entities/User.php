<?php

class User { 
    private ?string $id; 
    private string $name; 
    private string $email; 
    private string $passwordHash; 

    public function __construct(string $name, string $email, string $passwordHash, ?string $id = null) {
        $this->name = $name; 
        $this->email = $email;
        $this->passwordHash = $passwordHash; 
        $this->id = $id; 
    }

    //Getters
    public function getId(): ?string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    
    // Setters
    public function setId(string $id): void { $this->id = $id; }
    public function setName(string $name) : void { $this->name = $name; }
}