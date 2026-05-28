<?php 

class BwrStrategy implements ScoringStrategy { 
    public function calculate(array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data'];

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        // Verificare deficienta seismica specifica BWR 
        // Este mult mai sensibil la cutremure din cauza sistemului de oprire 
        $seismicStability = $geologicalData['seismic_stability']; 
        if($seismicStability < 6.5) { 
            $penalty = (6.5 - $seismicStability) * 4.0; 
            $deductions[] = [ 
                'parameter' => 'seismic_stability', 
                'penalty' => -$penalty, 
                'reason' => "Stabilitatea seismică marginală ({$seismicStability}) reprezintă un risc critic pentru BWR, deoarece barele de control sunt acționate hidraulic de jos în sus și pot fi blocate de deformarea fundației."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare deficienta operationaal si de evacuare
        // Aburul iesit din turbina BWR este radioactiv. O densitate mare a populatiei amplifica riscul de expunere la radiatii.
        $population = $geologicalData['population_density']; 
        if($population > 150) { 
            $penalty = ($population - 150) * 0.08;
            $deductions[] = [ 
                'parameter' => 'population_density', 
                'penalty' => -$penalty, 
                'reason' => "Densitatea populației de {$population} loc/km² atrage costuri masive de ecranare (shielding) suplimentară, deoarece clădirea turbinelor BWR conține abur radioactiv."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare deficienta scorului transportului pentru constructie 
        $transportScore = $geologicalData['transport_infrastructure_score']; 
        if($transportScore < 6.0) { 
            $penalty = (6.0 - $transportScore) * 3.5; 
            $deductions[] = [ 
                'parameter' => 'transport_infrastructure_score', 
                'penalty' => -$penalty, 
                'reason' => "Infrastructura de transport ({$transportScore}/10) va crea blocaje. Vasul de presiune al unui BWR este mai voluminos și dificil de transportat pe rute standard."
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificari de eficienta 
        $efficiency = $technicalData['estimated_efficiency']; 
        if($efficiency < 33.0) { 
            $penalty = (30.0 - $efficiency) * 2.0; 
            $deductions[] = [ 
                'parameter' => 'estimated_efficiency', 
                'penalty' => -$penalty, 
                'reason' => 'Eficienta estimata ({$efficiency}) este sub media tehnologica de 33%.'
            ]; 

            $totalPenalty += $penalty; 
        }

        // Verificare economica
        $duration = $basicData['construction_duration_years'];
        if ($duration > 5) {
            $penalty = ($duration - 5) * 4.5; 
            $deductions[] = [
                'parameter' => 'construction_duration_years',
                'penalty' => -$penalty, 
                'reason' => "Durata de {$duration} ani atrage costuri masive de capitalizare."
            ];
            $totalPenalty += $penalty;
        }
        

        $finalScore = max(0, $baseScore - $totalPenalty);

        return [
            'type' => 'BWR',
            'base_score' => $baseScore,
            'final_score' => round($finalScore, 2),
            'total_penalty' => round($totalPenalty, 2),
            'deficiencies' => $deductions
        ];
    }
}