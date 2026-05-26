<?php 

require_once __DIR__ . '../../../../Entities/SoilType.php'; 
require_once __DIR__ . '../../../../Entities/CoolingType.php'; 
require_once __DIR__ . '../../../../Entities/WaterSourceType.php'; 

class GeologicalCriticalChecker extends AbstractFeasibilityChecker { 
    public function check(array $plantData): array { 
        $criticalErrors = []; 
        $geologicalData = $plantData['geological_data']; 
        $schema = $plantData['reactor_schema']; 

        $unstableSoils = [ SoilType::PEAT, SoilType::SOFT_CLAY, SoilType::LOOSE_SAND, SoilType::SILT ]; 
        if(in_array($geologicalData['soil_type'], $unstableSoils)) { 
            $criticalErrors[] = "[Eroare Geotehnică] Solul de tip {$geologicalData['soil_type']} nu susține greutatea centralei."; 
        }

        if($schema['cooling_type'] === CoolingType::ONCE_THROUGH_SALT && $geologicalData['water_source_type'] === WaterSourceType::FRESH_WATER) { 
            $criticalErrors[] = "[Eroare Termodinamică] Sistemul de tip " . CoolingType::ONCE_THROUGH_SALT->value . " necesită captare de apă sărată.";
        } 

        if($schema['cooling_type'] === CoolingType::ONCE_THROUGH_FRESH && $geologicalData['water_source_type'] === WaterSourceType::SALT_WATER) { 
            $criticalErrors[] = "[Eroare Termodinamică] Sistemul de tip " . CoolingType::ONCE_THROUGH_FRESH->value . " necesită captare de apă dulce.";
        }

        $wetTowers = [ CoolingType::NATURAL_DRAFT_WET, CoolingType::MECHANICAL_DRAFT_WET ]; 
        if(in_array($schema['cooling_type'], $wetTowers) && $geologicalData['water_source_type'] === WaterSourceType::SALT_WATER) { 
            $criticalErrors[] = "[Eroare de Mediu] Turnurile umede alimentate cu apă sărată generează 'ploaie de sare'.";
        }

        if($schema['cooling_type'] === CoolingType::COOLING_POND && $geologicalData['water_source_type'] === WaterSourceType::SALT_WATER) { 
            $criticalErrors[] = "[Eroare Ecologică] Un iaz de răcire masiv cu apă sărată va contamina ireversibil pânza freatică.";
        }

        if($geologicalData['seismic_stability'] < 4.0) { 
            $criticalErrors[] = "[Eroare risc seismic] Zona selectată are un risc seismic prea mare."; 
        }

        if($geologicalData['population_density'] > 500) { 
            $criticalErrors[] = "[Eroare pentru populație] Densitatea populației este prea ridicată pentru verificarea unei evacuări sigure."; 
        }

        if($geologicalData['transport_infrastructure_score'] < 3.0) { 
            $criticalErrors[] = "[Eroare infrastructură] Scorul de transport de {$geologicalData['transport_infrastructure_score']} nu permite livrarea componentelor grele."; 
        }

        if($geologicalData['water_flow_rate'] < 20.0) { 
            $criticalErrors[] = "[Eroare Hidrologică] Debitul de apă ({$geologicalData['water_flow_rate']}) este insuficient pentru disiparea căldurii reziduale."; 
        }

        if($geologicalData['flood_risk'] > 8.0) { 
            $criticalErrors[] = "[Eroare Hidrologică] Riscul de inundație este prea mare."; 
        }

        if($geologicalData['groundwater_level'] < 2.0) { 
            $criticalErrors[] = "[Eroare Freatică] Pânza freatică este prea aproape de suprafață ({$geologicalData['groundwater_level']}m), risc de contaminare rapidă.";
        }

        if (!empty($criticalErrors)) {
            return [
                'status' => 'REJECTED',
                'errors' => $criticalErrors
            ];
        }

        return parent::check($plantData); 
    }
}