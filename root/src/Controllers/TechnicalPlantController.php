<?php

class TechnicalPlantController { 
    private TechnicalPlantService $technicalPlantService; 

    public function __construct(TechnicalPlantService $technicalPlantService) { 
        $this->technicalPlantService = $technicalPlantService; 
    }
}