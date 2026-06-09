<?php 

require_once __DIR__ . '/../Helpers/generateUUID.php'; 

class BasicPlantData { 
    private string $id; 
    private string $powerPlantId; 
    private ?float $capacity; 
    private ?int $constructionDurationYears; 
    private string $description; 
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $powerPlantId,
        ?string $id = null, 
        ?float $capacity = null,
        ?int $constructionDurationYears = null,
        ?string $description = '',
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) { 
        $this->id = $id ?? generateUUID(); 
        $this->powerPlantId = $powerPlantId;
        $this->capacity = $capacity;
        $this->constructionDurationYears = $constructionDurationYears;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string { 
        return $this->id; 
    }

    public function setId(string $id): void { 
        $this->id = $id; 
    }

    public function getPowerPlantId(): string {
        return $this->powerPlantId;
    }

    public function setPowerPlantId(string $powerPlantId): void {
        $this->powerPlantId = $powerPlantId;
    }

    public function getCapacity(): ?float {
        return $this->capacity;
    }

    public function setCapacity(?float $capacity): void {
        $this->capacity = $capacity;
    }

    public function getConstructionDurationYears(): ?int {
        return $this->constructionDurationYears;
    }

    public function setConstructionDurationYears(?int $constructionDurationYears): void {
        $this->constructionDurationYears = $constructionDurationYears;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}
