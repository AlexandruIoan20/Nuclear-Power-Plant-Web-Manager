<?php 

require_once __DIR__ . '/ScoringStrategy.php'; 
require_once __DIR__ . '/ConfigHelper.php';

class BwrStrategy implements ScoringStrategy { 
    public function calculate(array $plantData): array { 
        $geologicalData = $plantData['geological_data']; 
        $technicalData = $plantData['technical_data']; 
        $basicData = $plantData['basic_data'];

        $cfg = FeasibilityConfigHelper::get()['BWR'];

        $baseScore = 100.0; 
        $deductions = []; 
        $totalPenalty = 0.0; 

        $check = $cfg['seismic_stability'];
        $seismicStability = $geologicalData->getSeismicStability(); 
        if($seismicStability !== null && $seismicStability < $check['threshold']) { 
            $penalty = ($check['threshold'] - $seismicStability) * $check['multiplier']; 
            $deductions[] = [ 
                'parameter' => 'seismic_stability', 
                'penalty' => -$penalty, 
                'reason' => "Stabilitatea seismică marginală ({$seismicStability}) reprezintă un risc critic pentru BWR, deoarece barele de control sunt acționate hidraulic de jos în sus și pot fi blocate de deformarea fundației."
            ]; 

            $totalPenalty += $penalty; 
        }

        $check = $cfg['population_density'];
        $population = $geologicalData->getPopulationDensity(); 
        if($population !== null && $population > $check['threshold']) { 
            $penalty = ($population - $check['threshold']) * $check['multiplier'];
            $deductions[] = [ 
                'parameter' => 'population_density', 
                'penalty' => -$penalty, 
                'reason' => "Densitatea populației de {$population} loc/km² atrage costuri masive de ecranare (shielding) suplimentară, deoarece clădirea turbinelor BWR conține abur radioactiv."
            ]; 

            $totalPenalty += $penalty; 
        }

        $check = $cfg['transport_infrastructure_score'];
        $transportScore = $geologicalData->getTransportInfrastructureScore(); 
        if($transportScore !== null && $transportScore < $check['threshold']) { 
            $penalty = ($check['threshold'] - $transportScore) * $check['multiplier']; 
            $deductions[] = [ 
                'parameter' => 'transport_infrastructure_score', 
                'penalty' => -$penalty, 
                'reason' => "Infrastructura de transport ({$transportScore}/10) va crea blocaje. Vasul de presiune al unui BWR este mai voluminos și dificil de transportat pe rute standard."
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

        $check = $cfg['construction_duration_years'];
        $duration = $basicData->getConstructionDurationYears();
        if ($duration !== null && $duration > $check['threshold']) {
            $penalty = ($duration - $check['threshold']) * $check['multiplier']; 
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