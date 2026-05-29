<?php 

require_once __DIR__ . '/Strategies/BwrStrategy.php'; 
require_once __DIR__ . '/Strategies/FbrStrategy.php';
require_once __DIR__ . '/Strategies/PhwrStrategy.php';
require_once __DIR__ . '/Strategies/PwrStrategy.php'; 

require_once __DIR__ . '/../../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../../Entities/ReactorType.php'; 

class ScoringChecker extends AbstractFeasibilityChecker { 
    public function check(array $plantData): array { 
        $schemas = $plantData['reactor_schemas']; 
         
        if(empty($schemas)) { 
            return [ 
                'status' => PlantStatus::REJECTED, 
                'message' => "[Eroare Logica] Nu exista niciun reactor asociat acestei centrale",  
            ]; 
        }

        $allDeficiencies = []; 
        $totalWeightedScore = 0.0; 

        $reactorTypesArray = array_map(fn($schema) => $schema->getType()->value, $schemas);
        $typeCounts = array_count_values($reactorTypesArray); 
        $totalReactors = array_sum($typeCounts); 

        foreach ($typeCounts as $reactorType => $count) { 
            $strategy = $this->getScoringStrategy($reactorType); 

            $scoreReport = $strategy->calculate($plantData); 
            $totalWeightedScore += ($scoreReport['final_score'] * $count);

            foreach($scoreReport['deficiencies'] as $deficiency) { 
                $deficiency['reactor_source'] = $reactorType;
                $allDeficiencies[] = $deficiency;  
            }
        }

        $averageNsviScore = round($totalWeightedScore / $totalReactors, 2);

        $finalEvaluation = [ 
            'nvsi_score' => $averageNsviScore, 
            'deficiencies' => $allDeficiencies
        ]; 

        if ($averageNsviScore >= 80.0) {
            $finalEvaluation['status'] = 'APPROVED';
            $finalEvaluation['message'] = "Amplasament aprobat. Indice NSVI: {$averageNsviScore}/100.";
        } elseif ($averageNsviScore >= 50.0) {
            $finalEvaluation['status'] = 'REVIEW';
            $finalEvaluation['message'] = "Amplasament marginal. Indice NSVI: {$averageNsviScore}/100. Analizați deficiențele tehnologice mixte.";
        } else {
            $finalEvaluation['status'] = 'REJECTED';
            $finalEvaluation['message'] = "Amplasament respins. Indice NSVI prea mic: {$averageNsviScore}/100.";
        }

        return $finalEvaluation;
    }

    private function getScoringStrategy(string $type): ScoringStrategy { 
        return match($type) { 
            ReactorType::PWR->value => new PwrStrategy(),   
            ReactorType::PHWR->value => new PhwrStrategy(), 
            ReactorType::BWR->value => new BwrStrategy(), 
            ReactorType::FBR->value => new FbrStrategy(), 
            default => throw new Exception("[Eroare] Nu exista o strategie de evaluare pentru acest tip de reactor")
        }; 
    }

}