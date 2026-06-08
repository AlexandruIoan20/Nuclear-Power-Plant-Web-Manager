<?php

class Alert {
    private ?string $id;
    private string $plantId;
    private string $type;
    private string $message;
    private bool $isRead;
    private string $createdAt;

    public function __construct(string $plantId, string $type, string $message, bool $isRead = false, ?string $id = null, string $createdAt = '') {
        $this->plantId = $plantId;
        $this->type = $type;
        $this->message = $message;
        $this->isRead = $isRead;
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?string { return $this->id; }
    public function getPlantId(): string { return $this->plantId; }
    public function getType(): string { return $this->type; }
    public function getMessage(): string { return $this->message; }
    public function isRead(): bool { return $this->isRead; }
    public function getCreatedAt(): string { return $this->createdAt; }
}