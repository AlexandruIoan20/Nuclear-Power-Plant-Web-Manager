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
        $existingData = $this->plantRepositoryFacade->getBasicDataByPlantId($plantId); 
        if($existingData !== null) { 
            throw new Exception("Există deja date pentru această centrală. Te rugăm să folosești metoda de UPDATE (PUT/PATCH).");
        }

        $capacity = (isset($data['capacity']) && $data['capacity'] !== '') ? (float) $data['capacity'] : null;
        $constructionDurationYears = (isset($data['constructionDurationYears']) && $data['constructionDurationYears'] !== '') 
            ? (int) $data['constructionDurationYears'] : null;
        $description = $data['description'] ?? ''; 

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