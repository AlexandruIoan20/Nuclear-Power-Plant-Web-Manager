<?php

class GeologicalPlantController { 
    private GeologicalPlantService $geologicalPlantService; 

    public function __construct(GeologicalPlantService $geologicalPlantService) { 
        $this->geologicalPlantService = $geologicalPlantService; 
    }
}