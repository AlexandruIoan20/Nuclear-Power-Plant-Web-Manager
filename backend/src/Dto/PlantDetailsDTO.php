<?php

class PlantDetailsDTO {
    public string $id;
    public string $name;
    public ?string $createdBy;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        string $id,
        string $name,
        ?string $createdBy = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromEntity(Plant $plant): self {
        return new self(
            $plant->getId(),
            $plant->getName(),
            $plant->getCreatedBy(),
            $plant->getCreatedAt(),
            $plant->getUpdatedAt()
        );
    }
    
    public static function fromRequest(array $data, string $id = ''): self {
        return new self(
            $id,
            $data['name'] ?? ''
        );
    }
}
