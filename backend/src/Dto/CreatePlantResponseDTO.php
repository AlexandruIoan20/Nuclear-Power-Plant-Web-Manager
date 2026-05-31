<?php 

class CreatePlantResponseDTO { 
    public readonly string $plantId; 

    public function __construct(string $plantId) { 
        $this->plantId = $plantId; 
    }
}