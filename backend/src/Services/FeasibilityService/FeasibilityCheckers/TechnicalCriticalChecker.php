<?php 

class TechnicalCriticalChecker extends AbstractFeasibilityChecker { 
    public function check(array $plantData): array { 
        $criticalErrors = []; 
        $technicalData = $plantData['technical_data']; 

        if ($technicalData['estimated_efficiency'] > 45.0) { 
            $criticalErrors[] = "[Eroare Termodinamică] Eficiența estimată de {$technicalData['estimated_efficiency']}% depășește limita practică pentru un ciclu Rankine nuclear convențional (maxim ~35%)."; 
        }

        if($technicalData['estimated_efficiency'] < 15.0) { 
            $criticalErrors[] = "[Eroare Eficiență] Eficiența de {$technicalData['estimated_efficiency']}% este prea scăzută pentru ca proiectul să fie viabil economic."; 
        }

        if($technicalData['number_of_reactors'] < 1) {
            $criticalErrors[] = "[Eroare Arhitecturală] Centrala trebuie să aibă cel puțin un reactor.";
        } elseif($technicalData['number_of_reactors'] > 8) { 
            $criticalErrors[] = "[Eroare de Siguranță] Concentrarea a peste 8 reactoare pe un singur amplasament depășește capacitatea maximă a Sistemului Suprem de Răcire."; 
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