<?php

class PlantDTO {
    public string $id;
    public string $name;
    public string $status;

    public function __construct(
        string $id,
        string $name,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
    }

    public static function fromEntity(Plant $plant): self {
        return new self(
            $plant->getId(),
            $plant->getName(),
            $plant->getStatus()->name 
        );
    }
}