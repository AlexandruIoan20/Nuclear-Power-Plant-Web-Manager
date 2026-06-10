<?php 

require_once __DIR__ . '/AbstractReactorSimulator.php'; 

class PwrSimulator extends AbstractReactorSimulator { 
    private const TEMP_PRESSURE_COEFFICIENT = 0.04; 
    private const POWER_TEMP_COEFFICIENT = 0.45; 
    private const FLOW_TEMP_COEFFICIENT = 0.008; 

    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void { 
        $index = $this->buildIndex($newValues, $sensors); 

        // Temperatura iesirii urmeaza puterea reactorului
        if(isset($index['power_percent']) && isset($index['temp_coolant_out']))  {
            $powerPercent = $index['power_percent']['value']; 
            $currentTemperature = $index['temp_coolant_out']['value']; 
            $nominalTemperature = $index['temp_coolant_out']['sensor']->getNormalMax(); 
            $minTemperature = $index['temp_coolant_out']['sensor']->getNormalMin(); 

            $targetTemperature = $minTemperature + ($nominalTemperature - $minTemperature) * ($powerPercent / 100); 
            $newValues[$index['temp_coolant_out']['sensor']->getId()] = $currentTemperature + ($targetTemperature - $currentTemperature) * 0.05; 
        }

        // Temperatura intrarii urmeaza temperatura iesirii cu lag (delta 30 C)
        if(isset($index['temp_coolant_out']) && isset($index['temp_coolant_in'])) { 
            $tempOut = $index['temp_coolant_out']['value']; 
            $currentIn = $index['temp_coolant_in']['value']; 

            $targetIn = $tempOut - 30; 
            $newValues[$index['temp_coolant_in']['sensor']->getId()] = $currentIn + ($targetIn - $currentIn) * 0.04; 
        }

        // Presiunea circuitului primar este corelata cu temperatura 
        if(isset($index['temp_coolant_out'])  && isset($index['pressure'])) { 
            $tempOut = $index['temp_coolant_out']['value']; 
            $currentPres = $index['pressure']['value']; 

            $targetPres = 15.5 + ($tempOut - 310) * self::TEMP_PRESSURE_COEFFICIENT; 
            $newValues[$index['pressure']['sensor']->getId()] = $currentPres + ($targetPres - $currentPres) * 0.06; 
        }

        if(isset($index['temp_coolant_out']) && isset($index['flow_rate_primary'])) { 
            $tempOut = $index['temp_coolant_out']['value']; 
            $currentFlow = $index['flow_rate_primary']['value']; 
            $nominalFlow = $index['flow_rate_primary']['sensor']->getNormalMax(); 

            $densityCorrection = ($tempOut - 310) * 0.0005 * $nominalFlow; 
            $newValues[$index['flow_rate_primary']['sensor']->getId()] = $currentFlow + $densityCorrection * 0.02; 
        }

        // Temperatura combustibilului urmeaza puterea cu factor mare
        if(isset($index['power_percent']) && isset($index['temp_fuel_center'])) { 
            $powerPercent = $index['power_percent']['value']; 
            $currentFuelT = $index['temp_fuel_center']['value']; 
            $minFuelT = $index['temp_fuel_center']['sensor']->getNormalMin(); 
            $maxFuelT = $index['temp_fuel_center']['sensor']->getNormalMax(); 

            $targetFuelT = $minFuelT + ($maxFuelT - $minFuelT) * ($powerPercent / 100); 
            $newValues[$index['temp_fuel_center']['sensor']->getId()] = $currentFuelT + ($targetFuelT - $currentFuelT ) * 0.03; 
        }

        // Activitatea agentului primar creste la temperatura peste nominal 
        if(isset($index['temp_coolant_out']) && isset($index['activity_primary'])) { 
            $tempOut = $index['temp_coolant_out']['value']; 
            $currentActivity = $index['activity_primary']['value'];

            if($tempOut > 310) { 
                $activityBoost = $currentActivity * ($tempOut - 310) * 0.005;
                $newValues[$index['activity_primary']['sensor']->getId()] += $activityBoost * 0.01; 
            }
        }

        // Presiunea aburului urmeaza temperatura ciclului primar
        if(isset($index['temp_coolant_out']) && isset($index['steam_pressure'])) { 
            $tempOut = $index['temp_coolant_out']['value']; 
            $currentSteam = $index['steam_pressure']['value']; 

            $targetSteam = 6.2 + ($tempOut - 310) * 0.015; 
            $newValues[$index['steam_pressure']['sensor']->getId()] = $currentSteam + ($targetSteam - $currentSteam) * 0.05; 
        }
    }
}