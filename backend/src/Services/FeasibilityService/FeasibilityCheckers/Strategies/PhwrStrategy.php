<?php 

require_once __DIR__ . '/ScoringStrategy.php'; 
require_once __DIR__ . '/ConfigHelper.php';

class PhwrStrategy implements ScoringStrategy { 
    public function calculate (array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data']; 

        $cfg = FeasibilityConfigHelper::get()['PHWR'];

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        $check = $cfg['groundwater_level'];
        $groundWater = $geologicalData->getGroundwaterLevel(); 
        if($groundWater !== null && $groundWater < $check['threshold']) { 
            $penalty = $check['penalty'];
            $deductions[] = [ 
                'parameter' => 'groundwater_level', 
                'penalty' => -$penalty, 
                'reason' => "Reactorul PHWR generează Tritiu. Pânza freatică ridicată ({$groundWater}m) necesită sisteme de captare izotopică extrem de costisitoare."
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
                'reason' => "Durata de {$duration} ani atrage costuri masive de capitalizare."
            ]; 

            $totalPenalty += $penalty; 
        }

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

        $check = $cfg['seismic_stability'];
        $seismicStability = $geologicalData->getSeismicStability(); 
        if($seismicStability !== null && $seismicStability < $check['threshold']) { 
            $penalty = ($check['threshold'] - $seismicStability) * $check['multiplier']; 
            $deductions[] = [  
                'parameter' => 'seismic_stability', 
                'penalty' => -$penalty, 
                'reason' => "Stabilitatea seismică de {$seismicStability}/10 prezintă risc pentru arhitectura PHWR. Tancul orizontal (Calandria) este extrem de vulnerabil la forfecarea seismică laterală."
            ];
            
            $totalPenalty += $penalty;
        }

        $finalScore = max(0, $baseScore - $totalPenalty); 
 
        return [ 
            'type' => 'PHWR', 
            'base_score' => $baseScore, 
            'final_score' => round($finalScore, 2),
            'total_penalty' => round($totalPenalty, 2), 
            'deficiencies' => $deductions 
        ]; 
    }
}