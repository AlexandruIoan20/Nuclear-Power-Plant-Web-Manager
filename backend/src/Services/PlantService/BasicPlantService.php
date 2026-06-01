<?php 

require_once __DIR__ . '/../../Repositories/PlantRepository/BasicPlantRepository.php'; 

class BasicPlantService { 
    private PlantRepositoryFacade $plantRepositoryFacade; 

    public function __construct(PlantRepositoryFacade $plantRepositoryFacade) { 
        $this->plantRepositoryFacade = $plantRepositoryFacade; 
    }

    public function findByPlantId(string $plantId) { 
        return $this->plantRepositoryFacade->getBasicDataByPlantId($plantId); 
    }

    public function save(array $data, string $plantId) { 
        $capacity = $data['capacity'] ?? ''; 
        $capacity = ($capacity !== '') ? $capacity : null; 

        $constructionDurationYears = $data['constructionDurationYears'] ?? ''; 
        $constructionDurationYears = ($constructionDurationYears !== '') ? $constructionDurationYears : null; 

        $description = $data['description'] ?? ''; 
        $description = ($description !== '') ? $description : null; 

        $basicPlantData = new BasicPlantData($plantId, null, $capacity, $constructionDurationYears, $description); 
        $this->plantRepositoryFacade->saveBasicData($basicPlantData); 
    }

    public function update(array $data, string $plantId) { 
        $currentPlantData = $this->plantRepositoryFacade->getBasicDataByPlantId($plantId); 
        $capacity = $data['capacity'] ?? ''; 
        $capacity = ($capacity !== '') ? $capacity : null; 

        $constructionDurationYears = $data['constructionDurationYears'] ?? ''; 
        $constructionDurationYears = ($constructionDurationYears !== '') ? $constructionDurationYears : null; 

        $description = $data['description'] ?? ''; 
        $description = ($description !== '') ? $description : null; 

        $basicPlantData = new BasicPlantData($plantId, $currentPlantData->getId(), $capacity, $constructionDurationYears, $description); 
        $this->plantRepositoryFacade->updateBasicData($basicPlantData); 
    }
}