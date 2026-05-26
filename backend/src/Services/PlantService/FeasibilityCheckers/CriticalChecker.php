<?php 

require_once __DIR__ . '../../../../Entities/SoilType.php'; 
require_once __DIR__ . '../../../../Entities/CoolingType.php'; 
require_once __DIR__ . '../../../../Entities/WaterSourceType.php'; 

class CriticalChecker extends AbstractFeasibilityChecker { 
    public function check(array $plantData): array { 
        $criticalErrors = []; 
        $geologicalData = $plantData['geological_data']; 
        $schema = $plantData['reactor_schema']; 

        if($schema['cooling_type'] === CoolingType::ONCE_THROUGH_SALT && $geologicalData['water_source_type'] === WaterSourceType::FRESH_WATER) { 
            $criticalErrors[] = "[Eroare Incompatibilitate Termodinamica] Sistemul de tip {CoolingType::ONCE_TROUGH_SALT} necesita captare de apa sarata"; 
        } 


        return parent::check($plantData); 
    }
}