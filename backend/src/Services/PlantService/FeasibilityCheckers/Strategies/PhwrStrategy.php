<?php 

class PhwrStrategy implements ScoringStrategy { 
    public function calculate (array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data']; 

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        // Verificare panza freatica
        $groundWater = $geologicalData['groundwater_level']; 
        if($groundWater >= 2.0 && $groundWater < 5.0) { 
            $penalty = 12.0;
            $deductions[] = [ 
                'parameter' => 'groundwater_level', 
                'penalty' => -$penalty, 
                'reason' => "Reactorul PHWR generează Tritiu. Pânza freatică ridicată ({$groundWater}m) necesită sisteme de captare izotopică extrem de costisitoare."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificari financiare
        $duration = $basicData['construction_duration_years']; 
        if($duration > 5) { 
            $penalty = ($duration - 5) * 6.0; 
            $deductions[] = [ 
                'parameter' => 'construction_duration_years', 
                'penalty' => -$penalty, 
                'reason' => "Durata de {$duration} ani atrage costuri masive de capitalizare."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificari de eficienta 
        $efficiency = $technicalData['estimated_efficiency']; 
        if($efficiency < 30.0) { 
            $penalty = (30.0 - $efficiency) * 2.0; 
            $deductions[] = [ 
                'parameter' => 'estimated_efficiency', 
                'penalty' => -$penalty, 
                'reason' => "Eficienta estimata ({$efficiency}) este sub media tehnologica de 30%."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare deficienta Calandria (stabilitatea seismica)
        $seismicStability = $geologicalData['seismic_stability']; 
        if($seismicStability >= 4.0 && $seismicStability < 6.0) { 
            $penalty = (6.0 - $seismicStability) * 3.5; 
            $deductions[] = [  
                'parameter' => 'seismic_stability', 
                'penalty' => -$penalty, 
                'reason' => "Stabilitatea seismică de {$seismicStability}/10 prezintă risc pentru arhitectura PHWR. Tancul orizontal (Calandria) este extrem de vulnerabil la forfecarea seismică laterală."
            ];
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