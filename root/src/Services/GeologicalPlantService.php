<?php

class GeologicalPlantService { 
    private GeologicalPlantRepository $geologicalPlantRepository; 

    public function __contruct(GeologicalPlantRepository $geologicalPlantRepository) { 
        $this->geologicalPlantRepository = $geologicalPlantRepository; 
    }
}