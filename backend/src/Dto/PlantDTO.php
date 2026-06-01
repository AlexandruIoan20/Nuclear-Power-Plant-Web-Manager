<?php

class PlantDTO {
    public string $id;
    public string $name;
    public string $country;
    public ?float $latitude;
    public ?float $longitude;
    public string $status;

    public function __construct(
        string $id,
        string $name,
        string $country,
        ?float $latitude,
        ?float $longitude,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->country = $country;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->status = $status;
    }

    public static function fromEntity(Plant $plant): self {
        return new self(
            $plant->getId(),
            $plant->getName(),
            $plant->getCountry(),
            $plant->getLatitude(),
            $plant->getLongitude(),
            $plant->getStatus()->name 
        );
    }
}