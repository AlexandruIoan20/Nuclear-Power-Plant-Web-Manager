<?php 

require_once __DIR__ . '/../Helpers/generateUUID.php'; 

class Plant { 
    private string $id; 
    private string $name; 
    private PlantStatus $status; 
    private ?string $createdBy;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?string $id = null, 
        string $name = '',
        PlantStatus $status = PlantStatus::DRAFT,
        ?string $createdBy = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) { 
        $this->id = $id ?? generateUUID(); 
        $this->name = $name; 
        $this->status = $status; 
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string { 
        return $this->id; 
    }
    
    public function setId(): void { 
        $this->id = generateUUID(); 
    }

    public function getName(): string { 
        return $this->name; 
    }

    public function setName(string $name): void { 
        $this->name = $name; 
    }

    public function getStatus(): PlantStatus { 
        return $this->status; 
    }

    public function setStatus(PlantStatus $status): void { 
        $this->status = $status; 
    }

    public function getCreatedBy(): ?string {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): void {
        $this->createdBy = $createdBy;
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
