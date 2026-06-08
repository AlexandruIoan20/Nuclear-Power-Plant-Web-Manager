<?php

class PlantDetailsDTO {
    public string $id;
    public string $name;

    public function __construct(
        string $id,
        string $name
    ) {
        $this->id = $id;
        $this->name = $name;
    }

    public static function fromEntity(Plant $plant): self {
        return new self(
            $plant->getId(),
            $plant->getName()
        );
    }
    
    public static function fromRequest(array $data, string $id = ''): self {
        return new self(
            $id,
            $data['name'] ?? ''
        );
    }
}