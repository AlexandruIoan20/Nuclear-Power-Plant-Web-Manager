<?php

require_once __DIR__ . '/AbstractReactorSimulator.php';

class PhwrSimulator extends AbstractReactorSimulator {
    private const MODERATOR_TEMP_NOMINAL  = 70.0;
    private const COOLANT_PRESSURE_NOMINAL = 11.0;
    private const TRITIUM_PRODUCTION_RATE  = 0.0001;

    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void {
        $index = $this->buildIndex($newValues, $sensors);

        // Temperatura agentului de racire urmeaza puterea
        if (isset($index['power_percent']) && isset($index['temp_coolant_out'])) {
            $powerPct = $index['power_percent']['value'];
            $currentTemp = $index['temp_coolant_out']['value'];
            $minTemp = $index['temp_coolant_out']['sensor']->getNormalMin() ?? 260;
            $maxTemp = $index['temp_coolant_out']['sensor']->getNormalMax() ?? 310;

            $targetTemp = $minTemp + ($maxTemp - $minTemp) * ($powerPct / 100);
            $newValues[$index['temp_coolant_out']['sensor']->getId()] =
                $currentTemp + ($targetTemp - $currentTemp) * 0.05;
        }

        // Moderatorul ramane aproape constant (racit independent)
        if (isset($index['temp_moderator'])) {
            $currentModTemp = $index['temp_moderator']['value'];
            $newValues[$index['temp_moderator']['sensor']->getId()] =
                $currentModTemp + (self::MODERATOR_TEMP_NOMINAL - $currentModTemp) * 0.02;
        }

        // Presiunea circuitului de racire
        if (isset($index['temp_coolant_out']) && isset($index['pressure'])) {
            $tempOut = $index['temp_coolant_out']['value'];
            $currentPres = $index['pressure']['value'];

            $targetPres = self::COOLANT_PRESSURE_NOMINAL + ($tempOut - 285) * 0.025;
            $newValues[$index['pressure']['sensor']->getId()] =
                $currentPres + ($targetPres - $currentPres) * 0.05;
        }

        // Activitate tritiu in moderator creste cu puterea
        if (isset($index['power_percent']) && isset($index['activity_primary'])) {
            $powerPct = $index['power_percent']['value'];
            $currentActivity = $index['activity_primary']['value'];

            $tritiumProduction = $powerPct * self::TRITIUM_PRODUCTION_RATE;
            $newValues[$index['activity_primary']['sensor']->getId()] = $currentActivity + $tritiumProduction * 0.001;
        }

        // Debit agent de racire urmeaza presiunea
        if (isset($index['pressure']) && isset($index['flow_rate_primary'])) {
            $pressure = $index['pressure']['value'];
            $currentFlow = $index['flow_rate_primary']['value'];
            $nominalPres = self::COOLANT_PRESSURE_NOMINAL;
            $nominalFlow = $index['flow_rate_primary']['sensor']->getNormalMax() ?? 20000;

            $flowFactor = sqrt(max(0.1, $pressure / $nominalPres));
            $targetFlow = $nominalFlow * $flowFactor;
            $newValues[$index['flow_rate_primary']['sensor']->getId()] = $currentFlow + ($targetFlow - $currentFlow) * 0.04;
        }
    }
}
