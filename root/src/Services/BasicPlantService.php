<?php 

require_once __DIR__ . '/../Repositories/BasicPlantRepository.php'; 

class BasicPlantService { 
    private BasicPlantRepository $basicPlantRepository; 

    public function __construct(BasicPlantRepository $basicPlantRepository) { 
        $this->basicPlantRepository = $basicPlantRepository; 
    }

    public function findByPlantId(string $plantId) { 
        return $this->basicPlantRepository->findByPlantId($plantId); 
    }
}