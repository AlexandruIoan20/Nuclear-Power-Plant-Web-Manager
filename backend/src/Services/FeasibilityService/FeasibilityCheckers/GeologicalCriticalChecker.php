<?php 

require_once __DIR__ . '../../../../Entities/SoilType.php'; 
require_once __DIR__ . '../../../../Entities/CoolingType.php'; 
require_once __DIR__ . '../../../../Entities/WaterSourceType.php'; 
require_once __DIR__ . '/AbstractFeasibilityChecker.php'; 

class GeologicalCriticalChecker extends AbstractFeasibilityChecker { 
    public function check(array $plantData): array { 
        $criticalErrors = []; 
        $geologicalData = $plantData['geological_data'] ?? null; 
        $schemas = $plantData['reactor_schemas'] ?? []; 

        if (!$geologicalData) {
            return [
                'status' => 'REJECTED',
                'errors' => ['Datele geologice lipsesc.']
            ];
        }

        $soilType = $geologicalData->getSoilType();
        $unstableSoils = [SoilType::PEAT, SoilType::SOFT_CLAY, SoilType::LOOSE_SAND, SoilType::SILT]; 
        
        if ($soilType !== null && in_array($soilType, $unstableSoils, true)) { 
            $criticalErrors[] = "[Eroare Geotehnică] Solul de tip {$soilType->value} nu susține greutatea centralei."; 
        }

        $waterSource = $geologicalData->getWaterSourceType();
        $wetTowers = [CoolingType::NATURAL_DRAFT_WET, CoolingType::MECHANICAL_DRAFT_WET]; 

        foreach ($schemas as $schema) {
            $coolingType = $schema->getCooling();

            if ($coolingType === CoolingType::ONCE_THROUGH_SALT && $waterSource === WaterSourceType::FRESH_WATER) { 
                $criticalErrors[] = "[Eroare Termodinamică] Sistemul de tip " . CoolingType::ONCE_THROUGH_SALT->value . " necesită captare de apă sărată.";
            } 

            if ($coolingType === CoolingType::ONCE_THROUGH_FRESH && $waterSource === WaterSourceType::SALT_WATER) { 
                $criticalErrors[] = "[Eroare Termodinamică] Sistemul de tip " . CoolingType::ONCE_THROUGH_FRESH->value . " necesită captare de apă dulce.";
            }

            if (in_array($coolingType, $wetTowers, true) && $waterSource === WaterSourceType::SALT_WATER) { 
                $criticalErrors[] = "[Eroare de Mediu] Turnurile umede alimentate cu apă sărată generează 'ploaie de sare'.";
            }

            if ($coolingType === CoolingType::COOLING_POND && $waterSource === WaterSourceType::SALT_WATER) { 
                $criticalErrors[] = "[Eroare Ecologică] Un iaz de răcire masiv cu apă sărată va contamina ireversibil pânza freatică.";
            }
        }

        $seismicStability = $geologicalData->getSeismicStability();
        if ($seismicStability !== null && $seismicStability < 4.0) { 
            $criticalErrors[] = "[Eroare risc seismic] Zona selectată are un risc seismic prea mare."; 
        }

        $populationDensity = $geologicalData->getPopulationDensity();
        if ($populationDensity !== null && $populationDensity > 500) { 
            $criticalErrors[] = "[Eroare pentru populație] Densitatea populației este prea ridicată pentru verificarea unei evacuări sigure."; 
        }

        $transportScore = $geologicalData->getTransportInfrastructureScore();
        if ($transportScore !== null && $transportScore < 3.0) { 
            $criticalErrors[] = "[Eroare infrastructură] Scorul de transport de {$transportScore} nu permite livrarea componentelor grele."; 
        }

        $waterFlow = $geologicalData->getWaterFlowRate();
        if ($waterFlow !== null && $waterFlow < 20.0) { 
            $criticalErrors[] = "[Eroare Hidrologică] Debitul de apă ({$waterFlow}) este insuficient pentru disiparea căldurii reziduale."; 
        }

        $floodRisk = $geologicalData->getFloodRisk();
        if ($floodRisk !== null && $floodRisk > 8.0) { 
            $criticalErrors[] = "[Eroare Hidrologică] Riscul de inundație este prea mare."; 
        }

        $groundwaterLevel = $geologicalData->getGroundwaterLevel();
        if ($groundwaterLevel !== null && $groundwaterLevel < 2.0) { 
            $criticalErrors[] = "[Eroare Freatică] Pânza freatică este prea aproape de suprafață ({$groundwaterLevel}m), risc de contaminare rapidă.";
        }

        $result = parent::check($plantData);

        if (!empty($criticalErrors)) {
            $result['errors'] = array_merge(
                $result['errors'] ?? [],
                array_unique($criticalErrors)
            );
        }

        return $result; 
    }
}