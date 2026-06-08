<?php 

require_once __DIR__ . '/../Helpers/generateUUID.php'; 

class Plant { 
    private string $id; 
    private string $name; 
    private PlantStatus $status; 

    public function __construct(
        ?string $id = null, 
        string $name = '',
        PlantStatus $status = PlantStatus::DRAFT
    ) { 
        $this->id = $id ?? generateUUID(); 
        $this->name = $name; 
        $this->status = $status; 
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
}