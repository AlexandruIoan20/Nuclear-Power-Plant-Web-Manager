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

    public function update(array $data, string $plantId) { 
        $currentPlantData = $this->basicPlantRepository->findByPlantId($plantId); 
        $capacity = $data['capacity'] ?? ''; 
        $capacity = ($capacity !== '') ? $capacity : null; 

        $constructionDurationYears = $data['constructionDurationYears'] ?? ''; 
        $constructionDurationYears = ($constructionDurationYears !== '') ? $constructionDurationYears : null; 

        $description = $data['description'] ?? ''; 
        $description = ($description !== '') ? $description : null; 

        $basicPlantData = new BasicPlantData($plantId, $currentPlantData->getId(), $capacity, $constructionDurationYears, $description); 
        $this->basicPlantRepository->update($basicPlantData); 
    }
}