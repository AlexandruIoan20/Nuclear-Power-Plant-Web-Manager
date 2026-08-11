<?php

require_once __DIR__ . '/AbstractReactorSimulator.php';

/**
 * Simulator pentru reactor de tip PHWR (Pressurized Heavy Water Reactor).
 *
 * Modeleaza comportamentul unui reactor cu apa grea ca moderator si agent de racire,
 * unde temperatura moderatorului, presiunea si productia de tritiu sunt factori cheie.
 */
class PhwrSimulator extends AbstractReactorSimulator {
    private const MODERATOR_TEMP_NOMINAL  = 70.0;
    private const COOLANT_PRESSURE_NOMINAL = 11.0;
    private const TRITIUM_PRODUCTION_RATE  = 0.0001;

    /**
     * Aplica corelatiile fizice specifice reactorului PHWR.
     *
     * Modelul include efecte de incalzire a agentului de racire,
     * stabilitatea moderatorului, presiunea circuitului si
     * productia de tritiu in apa grea.
     */
    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void {

        $index = $this->buildIndex($newValues, $sensors);

        /**
         * Corelatie 1: putere -> temperatura agent de racire
         *
         * Cresterea puterii duce la cresterea temperaturii agentului de racire.
         * Temperatura evolueaza intre limitele operationale ale sistemului.
         */
        if (isset($index['power_percent']) && isset($index['temp_coolant_out'])) {

            $powerPct = $index['power_percent']['value'];
            $currentTemp = $index['temp_coolant_out']['value'];

            $minTemp = $index['temp_coolant_out']['sensor']->getNormalMin() ?? 260;
            $maxTemp = $index['temp_coolant_out']['sensor']->getNormalMax() ?? 310;

            $targetTemp =
                $minTemp + ($maxTemp - $minTemp) * ($powerPct / 100);

            $newValues[$index['temp_coolant_out']['sensor']->getId()] =
                $currentTemp + ($targetTemp - $currentTemp) * 0.05;
        }

        /**
         * Corelatie 2: moderator -> stabilizare temperatura constanta
         *
         * Moderatorul (apa grea) este mentinut la temperatura stabila
         * prin sisteme independente de racire.
         */
        if (isset($index['temp_moderator'])) {

            $currentModTemp = $index['temp_moderator']['value'];

            $newValues[$index['temp_moderator']['sensor']->getId()] =
                $currentModTemp +
                (self::MODERATOR_TEMP_NOMINAL - $currentModTemp) * 0.02;
        }

        /**
         * Corelatie 3: temperatura agent de racire -> presiune circuit
         *
         * Cresterea temperaturii duce la cresterea presiunii in circuitul primar
         * al reactorului PHWR.
         */
        if (isset($index['temp_coolant_out']) && isset($index['pressure'])) {

            $tempOut = $index['temp_coolant_out']['value'];
            $currentPres = $index['pressure']['value'];

            $targetPres =
                self::COOLANT_PRESSURE_NOMINAL + ($tempOut - 285) * 0.025;

            $newValues[$index['pressure']['sensor']->getId()] =
                $currentPres + ($targetPres - $currentPres) * 0.05;
        }

        /**
         * Corelatie 4: putere -> productie tritiu
         *
         * In reactoarele cu apa grea, neutronii produc tritiu,
         * iar rata de productie creste proportional cu puterea.
         */
        if (isset($index['power_percent']) && isset($index['activity_primary'])) {

            $powerPct = $index['power_percent']['value'];
            $currentActivity = $index['activity_primary']['value'];

            $tritiumProduction =
                $powerPct * self::TRITIUM_PRODUCTION_RATE;

            $newValues[$index['activity_primary']['sensor']->getId()] =
                $currentActivity + $tritiumProduction * 0.001;
        }

        /**
         * Corelatie 5: presiune -> debit agent de racire
         *
         * Debitul de racire creste odata cu presiunea circuitului,
         * conform unei relatii radacina patratica (comportament hidraulic).
         */
        if (isset($index['pressure']) && isset($index['flow_rate_primary'])) {

            $pressure = $index['pressure']['value'];
            $currentFlow = $index['flow_rate_primary']['value'];

            $nominalPres = self::COOLANT_PRESSURE_NOMINAL;
            $nominalFlow =
                $index['flow_rate_primary']['sensor']->getNormalMax() ?? 20000;

            $flowFactor = sqrt(max(0.1, $pressure / $nominalPres));

            $targetFlow = $nominalFlow * $flowFactor;

            $newValues[$index['flow_rate_primary']['sensor']->getId()] =
                $currentFlow + ($targetFlow - $currentFlow) * 0.04;
        }
    }
}