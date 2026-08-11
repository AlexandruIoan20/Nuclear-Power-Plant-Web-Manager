<?php

require_once __DIR__ . '/AbstractReactorSimulator.php';

/**
 * Simulator pentru reactor de tip BWR (Boiling Water Reactor).
 *
 * Modeleaza comportamentul fizic al reactorului pe baza corelatiilor
 * dintre flux, putere, temperatura, presiune si alti parametri.
 */
class BwrSimulator extends AbstractReactorSimulator {

    /**
     * Aplica corelatiile fizice specifice reactorului BWR.
     *
     * Fiecare bloc modeleaza o relatie fizica intre doi sau mai multi parametri
     * ai reactorului (putere, temperatura, presiune, flux etc).
     *
     * @param array $newValues valorile care vor fi actualizate
     * @param array $sensors senzorii disponibili
     * @param Reactor $reactor instanta reactorului simulat
     */
    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void {

        $index = $this->buildIndex($newValues, $sensors);

        /**
         * Corelatie 1: flux de recirculare -> putere reactor
         *
         * In BWR, puterea este direct influentata de debitul de apa.
         * Cresterea fluxului duce la cresterea reactiei de fisiune.
         */
        if (isset($index['flow_rate_primary']) && isset($index['power_percent'])) {

            $flowRate = $index['flow_rate_primary']['value'];
            $currentPower = $index['power_percent']['value'];
            $nominalFlow = $index['flow_rate_primary']['sensor']->getNormalMax();

            if ($nominalFlow > 0) {
                $flowFraction = $flowRate / $nominalFlow;

                // relatia neliniara intre flux si putere (efect de saturatie)
                $targetPower = 100 * pow($flowFraction, 0.8);

                $newValues[$index['power_percent']['sensor']->getId()] =
                    $currentPower + ($targetPower - $currentPower) * 0.03;
            }
        }

        /**
         * Corelatie 2: putere reactor -> temperatura iesire miez
         *
         * Cu cat puterea creste, cu atat temperatura agentului de racire
         * la iesirea din miez creste proportional.
         */
        if (isset($index['power_percent']) && isset($index['temp_coolant_out'])) {

            $power = $index['power_percent']['value'];
            $currentTemp = $index['temp_coolant_out']['value'];

            $minTemp = $index['temp_coolant_out']['sensor']->getNormalMin();
            $maxTemp = $index['temp_coolant_out']['sensor']->getNormalMax();

            $targetTemp = $minTemp + ($maxTemp - $minTemp) * ($power / 100);

            $newValues[$index['temp_coolant_out']['sensor']->getId()] =
                $currentTemp + ($targetTemp - $currentTemp) * 0.08;
        }

        /**
         * Corelatie 3: temperatura iesire miez -> presiune reactor
         *
         * In BWR, cresterea temperaturii duce la cresterea presiunii
         * prin efect de saturare a apei.
         */
        if (isset($index['temp_coolant_out']) && isset($index['pressure'])) {

            $tempOut = $index['temp_coolant_out']['value'];
            $currentPres = $index['pressure']['value'];

            // model simplificat de presiune in functie de temperatura
            $targetPres = 6.5 + ($tempOut - 280) * 0.04;

            $newValues[$index['pressure']['sensor']->getId()] =
                $currentPres + ($targetPres - $currentPres) * 0.05;
        }

        /**
         * Corelatie 4: putere reactor -> temperatura combustibil
         *
         * Cresterea puterii duce la incalzirea directa a combustibilului nuclear.
         */
        if (isset($index['power_percent']) && isset($index['temp_fuel_center'])) {

            $power = $index['power_percent']['value'];
            $currentFuelT = $index['temp_fuel_center']['value'];

            $minFuelT = $index['temp_fuel_center']['sensor']->getNormalMin();
            $maxFuelT = $index['temp_fuel_center']['sensor']->getNormalMax();

            $targetFuelT = $minFuelT + ($maxFuelT - $minFuelT) * ($power / 100);

            $newValues[$index['temp_fuel_center']['sensor']->getId()] =
                $currentFuelT + ($targetFuelT - $currentFuelT) * 0.04;
        }

        /**
         * Corelatie 5: putere reactor -> debit abur
         *
         * Puterea mai mare genereaza mai mult abur in circuitul secundar.
         */
        if (isset($index['power_percent']) && isset($index['steam_flow_rate'])) {

            $power = $index['power_percent']['value'];
            $currentSteamFlow = $index['steam_flow_rate']['value'];
            $maxSteamFlow = $index['steam_flow_rate']['sensor']->getNormalMax();

            $targetSteamFlow = $maxSteamFlow * ($power / 100);

            $newValues[$index['steam_flow_rate']['sensor']->getId()] =
                $currentSteamFlow + ($targetSteamFlow - $currentSteamFlow) * 0.06;
        }

        /**
         * Corelatie 6: putere mare -> nivel apa scazut in vas
         *
         * La puteri mari apare fenomenul de void fraction (formare abur),
         * ceea ce reduce nivelul efectiv de apa din vas.
         */
        if (isset($index['power_percent']) && isset($index['level_reactor_vessel'])) {

            $power = $index['power_percent']['value'];
            $currentLevel = $index['level_reactor_vessel']['value'];

            $voidEffect = max(0, ($power - 50) * 0.08);
            $targetLevel = 45 - $voidEffect;

            $newValues[$index['level_reactor_vessel']['sensor']->getId()] =
                $currentLevel + ($targetLevel - $currentLevel) * 0.02;
        }

        /**
         * Corelatie 7: temperatura ridicata -> crestere activitate circuit primar
         *
         * La temperaturi mari apar efecte neutronice si crestere usoara
         * a activitatii in circuitul primar.
         */
        if (isset($index['temp_coolant_out']) && isset($index['activity_primary'])) {

            $tempOut = $index['temp_coolant_out']['value'];
            $currentActivity = $index['activity_primary']['value'];

            if ($tempOut > 285) {
                $newValues[$index['activity_primary']['sensor']->getId()] +=
                    $currentActivity * ($tempOut - 285) * 0.003 * 0.01;
            }
        }
    }
}