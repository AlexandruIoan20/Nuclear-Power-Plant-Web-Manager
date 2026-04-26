<?php 

class TechnicalPlantService { 
    private TechnicalPlantRepository $technicalPlantRepository; 

    public function __construct(TechnicalPlantRepository $technicalPlantRepository) { 
        $this->technicalPlantRepository = $technicalPlantRepository; 
    }
}