<?php 

class FbrStrategy implements ScoringStrategy { 
    public function calculate (array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data'];

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        // Verificare deficienta hidrologica critica (???)
        $floodRisk = $geologicalData['flood_risk']; 
        if($floodRisk > 2.0) { 
            $penalty = ($floodRisk - 2.0) * 6.0; 
            $deductions[] = [ 
                'parameter' => 'flood_risk',
                'penalty' => -$penalty, 
                'reason' => "Riscul de inundație ({$floodRisk}) este inacceptabil de mare pentru un FBR.", 
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare deficienta seismica
        // Necesita stabilitate perfecta. Orice valoare sub 7.0 este penalizata.
        
        $seismicStability = $geologicalData['seismic_stability']; 
        if($seismicStability >= 4.0  && $seismicStability < 7.0) { 
            $penalty = (7.0 - $seismicStability) * 5.0;     
            $deductions[] = [
                'parameter' => 'seismic_stability', 
                'penalty' => -$penalty, 
                'reason' => "Stabilitatea seismică de {$seismicStability}/10 este insuficientă. O ruptură a circuitului primar de sodiu ar provoca un incendiu metalic incontrolabil."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare eficienta
        $efficiency = $technicalData['estimated_efficiency']; 
        if($efficiency < 38.0) { 
            $penalty = (38.0 - $efficiency) * 2.5;
            $deductions[] = [ 
                'parameter' => 'estimated_efficiency', 
                'penalty' => -$penalty, 
                'reason' => 'Eficienta estimata ({$efficiency}) este sub media tehnologica de 38%.'
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare economica
        $duration = $basicData['construction_duration_years'];
        if ($duration > 6) {
            $penalty = ($duration - 6) * 4.0; 
            $deductions[] = [
                'parameter' => 'construction_duration_years',
                'penalty' => -$penalty, 
                'reason' => "Durata de {$duration} ani atrage costuri masive de capitalizare."
            ];
            $totalPenalty += $penalty;
        }

        $finalScore = max(0, $baseScore - $totalPenalty);

        return [
            'type' => 'FBR',
            'base_score' => $baseScore,
            'final_score' => round($finalScore, 2),
            'total_penalty' => round($totalPenalty, 2),
            'deficiencies' => $deductions
        ];
    }
}