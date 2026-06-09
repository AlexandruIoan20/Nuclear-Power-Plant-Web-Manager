<?php 

require_once __DIR__ . '/ScoringStrategy.php'; 
require_once __DIR__ . '/ConfigHelper.php';

class PwrStrategy implements ScoringStrategy { 
    public function calculate(array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data'];

        $cfg = FeasibilityConfigHelper::get()['PWR'];

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        $check = $cfg['estimated_efficiency'];
        $efficiency = $technicalData->getEstimatedEfficiency(); 
        if($efficiency !== null && $efficiency < $check['threshold']) { 
            $penalty = (($check['base'] ?? $check['threshold']) - $efficiency) * $check['multiplier']; 
            $deductions[] = [ 
                'parameter' => 'estimated_efficiency', 
                'penalty' => -$penalty, 
                'reason' => "Eficienta estimata ({$efficiency}) este sub media tehnologica de {$check['threshold']}%."
            ]; 
            
            $totalPenalty += $penalty; 
        }

        $check = $cfg['water_proximity'];
        $waterProximity = $geologicalData->getWaterProximity(); 
        if($waterProximity !== null && $waterProximity > $check['threshold']) { 
            $penalty = ($waterProximity - $check['threshold']) * $check['multiplier'];
            $deductions[] = [ 
                'parameter' => 'water_proximity', 
                'penalty' => -$penalty, 
                'reason' => "Distanta de {$waterProximity} km fata de sursa de apa necesita statii de pompare intermediare."
            ]; 

            $totalPenalty += $penalty; 
        }

        $check = $cfg['water_flow_rate'];
        $waterFlow = $geologicalData->getWaterFlowRate();
        if($waterFlow !== null && $waterFlow < $check['threshold']) { 
            $penalty = $check['penalty']; 
            $deductions[] = [ 
                'parameter' => 'water_flow_rate', 
                'penalty' => -$penalty, 
                'reason' => "Debitul de {$waterFlow} m³/s este la limită. Va forța reducerea puterii comerciale pe timpul verii pentru a preveni poluarea termică a râului."
            ]; 
            $totalPenalty += $penalty; 
        }

        $check = $cfg['construction_duration_years'];
        $duration = $basicData->getConstructionDurationYears(); 
        if($duration !== null && $duration > $check['threshold']) { 
            $penalty = ($duration - $check['threshold']) * $check['multiplier']; 
            $deductions[] = [ 
                'parameter' => 'construction_duration_years', 
                'penalty' => -$penalty, 
                'reason' => "Durata de constructie estimata la {$duration} ani atrage costuri ridicate de finantare."
            ]; 

            $totalPenalty += $penalty; 
        }

        $check = $cfg['geological_risk_score'];
        $geologicalRisk = $geologicalData->getGeologicalRiskScore(); 
        if($geologicalRisk !== null && $geologicalRisk > $check['threshold']) { 
            $penalty = $geologicalRisk * $check['multiplier']; 
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