<?php 

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
        $this->id = $id ?? $this->generateUUID(); 
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
        $this->id = $this->generateUUID(); 
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

    private function generateUUID(): string {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}