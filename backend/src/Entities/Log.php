<?php

class Log {
    private ?string $id;
    private string $level;
    private string $message;
    private ?array $context;
    private ?string $userId;
    private ?string $plantId;
    private ?string $reactorId;
    private string $source;
    private ?string $requestUri;
    private ?string $ipAddress;
    private string $createdAt;

    public function __construct(
        string $level,
        string $message,
        ?array $context = null,
        ?string $userId = null,
        ?string $plantId = null,
        ?string $reactorId = null,
        string $source = 'backend',
        ?string $requestUri = null,
        ?string $ipAddress = null,
        ?string $id = null,
        string $createdAt = ''
    ) {
        $this->id = $id;
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->userId = $userId;
        $this->plantId = $plantId;
        $this->reactorId = $reactorId;
        $this->source = $source;
        $this->requestUri = $requestUri;
        $this->ipAddress = $ipAddress;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?string { return $this->id; }
    public function getLevel(): string { return $this->level; }
    public function getMessage(): string { return $this->message; }
    public function getContext(): ?array { return $this->context; }
    public function getUserId(): ?string { return $this->userId; }
    public function getPlantId(): ?string { return $this->plantId; }
    public function getReactorId(): ?string { return $this->reactorId; }
    public function getSource(): string { return $this->source; }
    public function getRequestUri(): ?string { return $this->requestUri; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function getCreatedAt(): string { return $this->createdAt; }
}
