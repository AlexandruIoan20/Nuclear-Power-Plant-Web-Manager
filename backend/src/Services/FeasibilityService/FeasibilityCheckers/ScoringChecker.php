<?php 

require_once __DIR__ . '/Strategies/BwrStrategy.php'; 
require_once __DIR__ . '/Strategies/FbrStrategy.php';
require_once __DIR__ . '/Strategies/PhwrStrategy.php';
require_once __DIR__ . '/Strategies/PwrStrategy.php'; 
require_once __DIR__ . '/Strategies/ConfigHelper.php';

require_once __DIR__ . '/../../../Entities/PlantStatus.php'; 
require_once __DIR__ . '/../../../Entities/ReactorType.php'; 

/** 
 * Ultimul checker al lantului ce reprezinta algoritmul de scoring al centralei. 
 */
class ScoringChecker extends AbstractFeasibilityChecker { 
    /**
     * Implementarea functiei de verificare. 
     * 
     * In functie de fiecare reactor si de tipul acestuia este aplicata o strategie de scoring. 
     * In final este calculat scorul si toate deficientele pe care proiectul de centrala le are. 
     * Pe baza scorului se ia o decizie. 
     */
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

        $scoringCfg = FeasibilityConfigHelper::get()['scoring'];

        $finalEvaluation = [ 
            'nsvi_score' => $averageNsviScore, 
            'deficiencies' => $allDeficiencies
        ]; 

        if ($averageNsviScore >= $scoringCfg['nsvi_approved_min']) {
            $finalEvaluation['status'] = 'APPROVED';
            $finalEvaluation['message'] = "Amplasament aprobat. Indice NSVI: {$averageNsviScore}/100.";
        } elseif ($averageNsviScore >= $scoringCfg['nsvi_review_min']) {
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