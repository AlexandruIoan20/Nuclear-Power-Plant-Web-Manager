<?php 

require_once __DIR__ . '/../Helpers/generateUUID.php'; 

class BasicPlantData { 
    private string $id; 
    private string $powerPlantId; 
    private ?float $capacity; 
    private ?int $constructionDurationYears; 
    private string $description; 

    public function __construct(
        string $powerPlantId,
        ?string $id = null, 
        ?float $capacity = null,
        ?int $constructionDurationYears = null,
        ?string $description = ''
    ) { 
        $this->id = $id ?? generateUUID(); 
        $this->powerPlantId = $powerPlantId;
        $this->capacity = $capacity;
        $this->constructionDurationYears = $constructionDurationYears;
        $this->description = $description;
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
}