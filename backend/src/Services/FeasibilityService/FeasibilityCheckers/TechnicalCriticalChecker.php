<?php 

require_once __DIR__ . '/AbstractFeasibilityChecker.php'; 

class TechnicalCriticalChecker extends AbstractFeasibilityChecker { 
    public function check(array $plantData): array { 
        $criticalErrors = []; 
        $technicalData = $plantData['technical_data'] ?? null; 

        if (!$technicalData) {
            return [
                'status' => 'REJECTED',
                'errors' => ['Datele tehnice lipsesc.']
            ];
        }

        $efficiency = $technicalData->getEstimatedEfficiency();
        
        if ($efficiency !== null && $efficiency > 45.0) { 
            $criticalErrors[] = "[Eroare Termodinamică] Eficiența estimată de {$efficiency}% depășește limita practică pentru un ciclu Rankine nuclear convențional (maxim ~35%)."; 
        }

        if ($efficiency !== null && $efficiency < 15.0) { 
            $criticalErrors[] = "[Eroare Eficiență] Eficiența de {$efficiency}% este prea scăzută pentru ca proiectul să fie viabil economic."; 
        }

        $numberOfReactors = $technicalData->getNumberOfReactors();

        if ($numberOfReactors !== null) {
            if ($numberOfReactors < 1) {
                $criticalErrors[] = "[Eroare Arhitecturală] Centrala trebuie să aibă cel puțin un reactor.";
            } elseif ($numberOfReactors > 8) { 
                $criticalErrors[] = "[Eroare de Siguranță] Concentrarea a peste 8 reactoare pe un singur amplasament depășește capacitatea maximă a Sistemului Suprem de Răcire."; 
            }
        }

        if (!empty($criticalErrors)) {
            return [
                'status' => 'REJECTED',
                'errors' => $criticalErrors
            ];
        }

        return parent::check($plantData); 
    }
}