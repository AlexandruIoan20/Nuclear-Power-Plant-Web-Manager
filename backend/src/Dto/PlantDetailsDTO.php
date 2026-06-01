<?php

class PlantDetailsDTO {
    public string $id;
    public string $name;
    public string $country;
    public ?float $latitude;
    public ?float $longitude;

    public function __construct(
        string $id,
        string $name,
        string $country,
        ?float $latitude,
        ?float $longitude
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->country = $country;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function fromEntity(Plant $plant): self {
        return new self(
            $plant->getId(),
            $plant->getName(),
            $plant->getCountry(),
            $plant->getLatitude(),
            $plant->getLongitude()
        );
    }
    
    public static function fromRequest(array $data, string $id = ''): self {
        return new self(
            $id,
            $data['name'] ?? '',
            $data['country'] ?? '',
            isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null
        );
    }
}