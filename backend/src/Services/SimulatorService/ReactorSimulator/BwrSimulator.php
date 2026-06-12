<?php

require_once __DIR__ . '/AbstractReactorSimulator.php';

class BwrSimulator extends AbstractReactorSimulator {
    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void {
        $index = $this->buildIndex($newValues, $sensors);

        // Puterea reactorului urmeaza fluxul de recirculare (caracteristica BWR)
        if (isset($index['flow_rate_primary']) && isset($index['power_percent'])) {
            $flowRate = $index['flow_rate_primary']['value'];
            $currentPower = $index['power_percent']['value'];
            $nominalFlow = $index['flow_rate_primary']['sensor']->getNormalMax();

            if ($nominalFlow > 0) {
                $flowFraction = $flowRate / $nominalFlow;
                $targetPower = 100 * pow($flowFraction, 0.8);
                $newValues[$index['power_percent']['sensor']->getId()] = $currentPower + ($targetPower - $currentPower) * 0.03;
            }
        }

        // Temperatura iesirii miezului urmeaza puterea
        if (isset($index['power_percent']) && isset($index['temp_coolant_out'])) {
            $power = $index['power_percent']['value'];
            $currentTemp = $index['temp_coolant_out']['value'];
            $minTemp = $index['temp_coolant_out']['sensor']->getNormalMin();
            $maxTemp = $index['temp_coolant_out']['sensor']->getNormalMax();

            $targetTemp = $minTemp + ($maxTemp - $minTemp) * ($power / 100);
            $newValues[$index['temp_coolant_out']['sensor']->getId()] = $currentTemp + ($targetTemp - $currentTemp) * 0.08;
        }

        // Presiunea reactorului (saturare) urmeaza temperatura iesirii miezului
        if (isset($index['temp_coolant_out']) && isset($index['pressure'])) {
            $tempOut = $index['temp_coolant_out']['value'];
            $currentPres = $index['pressure']['value'];

            $targetPres = 6.5 + ($tempOut - 280) * 0.04;
            $newValues[$index['pressure']['sensor']->getId()] = $currentPres + ($targetPres - $currentPres) * 0.05;
        }

        // Temperatura combustibilului urmeaza puterea
        if (isset($index['power_percent']) && isset($index['temp_fuel_center'])) {
            $power = $index['power_percent']['value'];
            $currentFuelT = $index['temp_fuel_center']['value'];
            $minFuelT = $index['temp_fuel_center']['sensor']->getNormalMin();
            $maxFuelT = $index['temp_fuel_center']['sensor']->getNormalMax();

            $targetFuelT = $minFuelT + ($maxFuelT - $minFuelT) * ($power / 100);
            $newValues[$index['temp_fuel_center']['sensor']->getId()] = $currentFuelT + ($targetFuelT - $currentFuelT) * 0.04;
        }

        // Debitul de abur urmeaza puterea reactorului
        if (isset($index['power_percent']) && isset($index['steam_flow_rate'])) {
            $power = $index['power_percent']['value'];
            $currentSteamFlow = $index['steam_flow_rate']['value'];
            $maxSteamFlow = $index['steam_flow_rate']['sensor']->getNormalMax();

            $targetSteamFlow = $maxSteamFlow * ($power / 100);
            $newValues[$index['steam_flow_rate']['sensor']->getId()] = $currentSteamFlow + ($targetSteamFlow - $currentSteamFlow) * 0.06;
        }

        // Nivelul apei in vas scade la putere mare (void fraction crescut)
        if (isset($index['power_percent']) && isset($index['level_reactor_vessel'])) {
            $power = $index['power_percent']['value'];
            $currentLevel = $index['level_reactor_vessel']['value'];

            $voidEffect = max(0, ($power - 50) * 0.08);
            $targetLevel = 45 - $voidEffect;
            $newValues[$index['level_reactor_vessel']['sensor']->getId()] = $currentLevel + ($targetLevel - $currentLevel) * 0.02;
        }

        // Activitatea in circuit creste la temperatura ridicata
        if (isset($index['temp_coolant_out']) && isset($index['activity_primary'])) {
            $tempOut = $index['temp_coolant_out']['value'];
            $currentActivity = $index['activity_primary']['value'];

            if ($tempOut > 285) {
                $newValues[$index['activity_primary']['sensor']->getId()] += $currentActivity * ($tempOut - 285) * 0.003 * 0.01;
            }
        }
    }
}
