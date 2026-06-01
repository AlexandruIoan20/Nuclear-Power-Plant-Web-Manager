<?php 

require_once __DIR__ . '/ScoringStrategy.php'; 

class PwrStrategy implements ScoringStrategy { 
    public function calculate(array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data'];

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        // Eficienta optima practica PWR ~ 35%
        $efficiency = $technicalData->getEstimatedEfficiency(); 
        if($efficiency < 35.0) { 
            $penalty = (35.0 - $efficiency) * 2.0; 
            $deductions[] = [ 
                'parameter' => 'estimated_efficiency', 
                'penalty' => -$penalty, 
                'reason' => "Eficienta estimata ({$efficiency}) este sub media tehnologica de 35%."
            ]; 
            
            $totalPenalty += $penalty; 
        }

        // Lipsa apei din apropiere 
        $waterProximity = $geologicalData->getWaterProximity(); 
        if($waterProximity > 2.0) { 
            $penalty = ($waterProximity - 2.0) * 3.0;
            $deductions[] = [ 
                'parameter' => 'water_proximity', 
                'penalty' => -$penalty, 
                // Corectat in ghilimele duble pentru a functiona corect interpolarea variabilei
                'reason' => "Distanta de {$waterProximity} km fata de sursa de apa necesita statii de pompare intermediare."
            ]; 

            $totalPenalty += $penalty; 
        }

        $waterFlow = $geologicalData->getWaterFlowRate();
        if($waterFlow < 50.0) { 
            $penalty = 10.0; 
            $deductions[] = [ 
                'parameter' => 'water_flow_rate', 
                'penalty' => -$penalty, 
                'reason' => "Debitul de {$waterFlow} m³/s este la limită. Va forța reducerea puterii comerciale pe timpul verii pentru a preveni poluarea termică a râului."
            ]; 
            $totalPenalty += $penalty; 
        }

        // Deficiente financiare
        $duration = $basicData->getConstructionDurationYears(); 
        if($duration > 5) { 
            $penalty = ($duration - 5) * 5.0; 
            $deductions[] = [ 
                'parameter' => 'construction_duration_years', 
                'penalty' => -$penalty, 
                'reason' => "Durata de constructie estimata la {$duration} ani atrage costuri ridicate de finantare."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Deficiente Geologice 
        $geologicalRisk = $geologicalData->getGeologicalRiskScore(); 
        if($geologicalRisk > 2.0) { 
            $penalty = $geologicalRisk * 2.5; 
            $deductions[] = [ 
                'parameter' => 'geological_risk_score', 
                'penalty' => -$penalty, 
                'reason' => "Scorul de risc geologic ({$geologicalRisk}) indica necesitatea unor fundatii complexe." 
            ]; 

            $totalPenalty += $penalty; 
        }

        $finalScore = max(0, $baseScore - $totalPenalty);

        return [
            'type' => 'PWR',
            'base_score' => $baseScore,
            'final_score' => round($finalScore, 2),
            'total_penalty' => round($totalPenalty, 2),
            'deficiencies' => $deductions
        ];
        
    }   
}