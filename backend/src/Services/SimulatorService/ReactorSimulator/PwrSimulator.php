<?php 

require_once __DIR__ . '/AbstractReactorSimulator.php'; 

/**
 * Simulator pentru reactor de tip PWR (Pressurized Water Reactor).
 *
 * Modeleaza comportamentul termic si hidraulic al unui reactor cu apa sub presiune,
 * unde temperatura, presiunea si debitul sunt strans cuplate cu puterea reactorului.
 */
class PwrSimulator extends AbstractReactorSimulator { 
    private const TEMP_PRESSURE_COEFFICIENT = 0.04; 
    private const POWER_TEMP_COEFFICIENT = 0.45; 
    private const FLOW_TEMP_COEFFICIENT = 0.008; 

    /**
     * Aplica corelatiile fizice specifice reactorului PWR.
     *
     * Modelul include interactiuni intre putere, temperatura, presiune,
     * debitul agentului de racire si activitatea radiologica.
     */
    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void { 

        $index = $this->buildIndex($newValues, $sensors); 

        /**
         * Corelatie 1: putere -> temperatura iesire agent de racire
         *
         * Cresterea puterii duce la cresterea temperaturii apei din circuitul primar,
         * intre limitele minime si maxime de functionare.
         */
        if (isset($index['power_percent']) && isset($index['temp_coolant_out']))  {

            $powerPercent = $index['power_percent']['value']; 
            $currentTemperature = $index['temp_coolant_out']['value']; 

            $nominalTemperature = $index['temp_coolant_out']['sensor']->getNormalMax(); 
            $minTemperature = $index['temp_coolant_out']['sensor']->getNormalMin(); 

            $targetTemperature =
                $minTemperature + ($nominalTemperature - $minTemperature) * ($powerPercent / 100); 

            $newValues[$index['temp_coolant_out']['sensor']->getId()] =
                $currentTemperature + ($targetTemperature - $currentTemperature) * 0.05; 
        }

        /**
         * Corelatie 2: temperatura iesire -> temperatura intrare (lag termic)
         *
         * Circuitul primar are inertie termica.
         * Temperatura de intrare urmeaza temperatura de iesire cu un decalaj (~30C).
         */
        if (isset($index['temp_coolant_out']) && isset($index['temp_coolant_in'])) { 

            $tempOut = $index['temp_coolant_out']['value']; 
            $currentIn = $index['temp_coolant_in']['value']; 

            $targetIn = $tempOut - 30; 

            $newValues[$index['temp_coolant_in']['sensor']->getId()] =
                $currentIn + ($targetIn - $currentIn) * 0.04; 
        }

        /**
         * Corelatie 3: temperatura -> presiune circuit primar
         *
         * Cresterea temperaturii determina cresterea presiunii in circuitul primar,
         * conform unei relatii aproximativ liniare in regim operational.
         */
        if (isset($index['temp_coolant_out'])  && isset($index['pressure'])) { 

            $tempOut = $index['temp_coolant_out']['value']; 
            $currentPres = $index['pressure']['value']; 

            $targetPres =
                15.5 + ($tempOut - 310) * self::TEMP_PRESSURE_COEFFICIENT; 

            $newValues[$index['pressure']['sensor']->getId()] =
                $currentPres + ($targetPres - $currentPres) * 0.06; 
        }

        /**
         * Corelatie 4: temperatura -> debit agent de racire
         *
         * Variatia temperaturii influenteaza densitatea fluidului,
         * ceea ce modifica debitul efectiv in circuitul primar.
         */
        if (isset($index['temp_coolant_out']) && isset($index['flow_rate_primary'])) { 

            $tempOut = $index['temp_coolant_out']['value']; 
            $currentFlow = $index['flow_rate_primary']['value']; 

            $nominalFlow =
                $index['flow_rate_primary']['sensor']->getNormalMax(); 

            $densityCorrection =
                ($tempOut - 310) * 0.0005 * $nominalFlow; 

            $newValues[$index['flow_rate_primary']['sensor']->getId()] =
                $currentFlow + $densityCorrection * 0.02; 
        }

        /**
         * Corelatie 5: putere -> temperatura combustibil
         *
         * Cresterea puterii determina incalzirea combustibilului nuclear
         * in zona centrala a reactorului.
         */
        if (isset($index['power_percent']) && isset($index['temp_fuel_center'])) { 

            $powerPercent = $index['power_percent']['value']; 
            $currentFuelT = $index['temp_fuel_center']['value']; 

            $minFuelT = $index['temp_fuel_center']['sensor']->getNormalMin(); 
            $maxFuelT = $index['temp_fuel_center']['sensor']->getNormalMax(); 

            $targetFuelT =
                $minFuelT + ($maxFuelT - $minFuelT) * ($powerPercent / 100); 

            $newValues[$index['temp_fuel_center']['sensor']->getId()] =
                $currentFuelT + ($targetFuelT - $currentFuelT) * 0.03; 
        }

        /**
         * Corelatie 6: temperatura ridicata -> activitate primara
         *
         * La temperaturi peste nominal apar efecte de activare radiologica
         * in circuitul primar.
         */
        if (isset($index['temp_coolant_out']) && isset($index['activity_primary'])) { 

            $tempOut = $index['temp_coolant_out']['value']; 
            $currentActivity = $index['activity_primary']['value'];

            if ($tempOut > 310) { 
                $activityBoost =
                    $currentActivity * ($tempOut - 310) * 0.005;

                $newValues[$index['activity_primary']['sensor']->getId()] +=
                    $activityBoost * 0.01; 
            }
        }

        /**
         * Corelatie 7: temperatura -> presiune abur secundar
         *
         * Circuitul secundar de abur este influentat direct de temperatura
         * circuitului primar prin schimbatorul de caldura.
         */
        if (isset($index['temp_coolant_out']) && isset($index['steam_pressure'])) { 

            $tempOut = $index['temp_coolant_out']['value']; 
            $currentSteam = $index['steam_pressure']['value']; 

            $targetSteam =
                6.2 + ($tempOut - 310) * self::FLOW_TEMP_COEFFICIENT; 

            $newValues[$index['steam_pressure']['sensor']->getId()] =
                $currentSteam + ($targetSteam - $currentSteam) * 0.05; 
        }
    }
}