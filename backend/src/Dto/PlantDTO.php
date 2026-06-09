<?php

class PlantDTO {
    public string $id;
    public string $name;
    public string $status;
    public ?string $createdBy;
    public ?string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        string $id,
        string $name,
        string $status,
        ?string $createdBy = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromEntity(Plant $plant): self {
        return new self(
            $plant->getId(),
            $plant->getName(),
            $plant->getStatus()->name,
            $plant->getCreatedBy(),
            $plant->getCreatedAt(),
            $plant->getUpdatedAt()
        );
    }
}
