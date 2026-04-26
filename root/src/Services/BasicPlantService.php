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

    public function save(array $data, string $plantId) { 
        $capacity = $data['capacity'] ?? ''; 
        $capacity = ($capacity !== '') ? $capacity : null; 

        $constructionDurationYears = $data['constructionDurationYears'] ?? ''; 
        $constructionDurationYears = ($constructionDurationYears !== '') ? $constructionDurationYears : null; 

        $description = $data['description'] ?? ''; 
        $description = ($description !== '') ? $description : null; 

        $basicPlantData = new BasicPlantData($plantId, null, $capacity, $constructionDurationYears, $description); 
        $this->basicPlantRepository->save($basicPlantData); 
    }
}