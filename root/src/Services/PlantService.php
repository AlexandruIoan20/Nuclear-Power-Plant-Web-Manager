<?php

require_once __DIR__ . '/../Helpers/generateUUID.php'; 
require_once __DIR__ . '/../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../Entities/Plant.php'; 

class PlantService { 
    private PlantRepository $plantRepository; 

    public function __construct(PlantRepository $plantRepository) { 
        $this->plantRepository = $plantRepository; 
    }

    public function savePlantDetails(array $data) { 
        $name = $data['name'] ?? ''; 
        $name = ($name !== '') ? $name : null; 

        $country = $data['country'] ?? ''; 
        $country = ($country !== '') ? $country : null; 

        $latitude = $data['latitude'] ?? ''; 
        $latitude = ($latitude !== '') ? $latitude : null; 

        $longitude = $data['longitude'] ?? ''; 
        $longitude = ($longitude !== '') ? $longitude : null;

        $status = PlantStatus::DRAFT; 
        $id = generateUUID(); 

        $plant = new Plant($country, $id, $name, $status, $latitude, $longitude); 
        $this->plantRepository->save($plant); 
    }
}