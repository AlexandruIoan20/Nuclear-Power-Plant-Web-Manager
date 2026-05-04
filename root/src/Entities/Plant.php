<?php 

require_once __DIR__ . '/../Helpers/generateUUID.php'; 

class Plant { 
    private string $id; 
    private string $name; 
    private string $country; 
    private ?float $latitude; 
    private ?float $longitude; 
    private PlantStatus $status; 

    public function __construct(
        string $country, 
        ?string $id = null, 
        string $name = '',
        PlantStatus $status = PlantStatus::DRAFT, 
        ?float $latitude = null, 
        ?float $longitude = null
    ) { 
        $this->id = $id ?? generateUUID(); 
        $this->name = $name; 
        $this->country = $country; 
        $this->status = $status; 
        $this->latitude = $latitude; 
        $this->longitude = $longitude;
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

    public function getCountry(): string { 
        return $this->country; 
    }

    public function setCountry(string $country): void { 
        $this->country = $country;
    }

    public function getStatus(): PlantStatus { 
        return $this->status; 
    }

    public function setStatus(PlantStatus $status): void { 
        $this->status = $status; 
    }

    public function getLongitude(): ?float { 
        return $this->longitude; 
    }

    public function setLongitude(?float $longitude): void { 
        $this->longitude = $longitude; 
    }
    
    public function getLatitude(): ?float { 
        return $this->latitude; 
    }

    public function setLatitude(?float $latitude): void { 
        $this->latitude = $latitude; 
    }
}