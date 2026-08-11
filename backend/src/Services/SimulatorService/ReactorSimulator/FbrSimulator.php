<?php

require_once __DIR__ . '/AbstractReactorSimulator.php';

/**
 * Simulator pentru reactor de tip FBR (Fast Breeder Reactor).
 *
 * Modeleaza comportamentul unui reactor cu racire pe sodiu lichid,
 * unde dinamica termica si neutronica este diferita fata de BWR/PWR.
 */
class FbrSimulator extends AbstractReactorSimulator {
    private const SODIUM_TEMP_IN_NOMINAL = 400.0;
    private const SODIUM_TEMP_OUT_NOMINAL = 550.0;
    private const SODIUM_PRESSURE_NOMINAL = 0.5;
    private const NA24_ACTIVATION_COEFFICIENT  = 0.0008;
    private const DOPPLER_COEFFICIENT = -0.002;

    /**
     * Aplica corelatiile fizice specifice reactorului FBR.
     *
     * Modelul include efecte de racire cu sodiu, activare neutronica Na-24,
     * feedback Doppler si corelatii intre debit, temperatura si vibratii.
     */
    protected function applyPhysicalCorrelation(array &$newValues, array $sensors, Reactor $reactor): void {

        $index = $this->buildIndex($newValues, $sensors);

        /**
         * Corelatie 1: putere -> temperatura sodiu iesire
         *
         * In FBR, cresterea puterii duce la incalzirea sodiului la iesire
         * din reactor, intre limitele nominale ale sistemului.
         */
        if (isset($index['power_percent']) && isset($index['temp_coolant_out'])) {

            $powerPct = $index['power_percent']['value'];
            $currentTemp = $index['temp_coolant_out']['value'];

            $targetTemp = self::SODIUM_TEMP_IN_NOMINAL +
                (self::SODIUM_TEMP_OUT_NOMINAL - self::SODIUM_TEMP_IN_NOMINAL) * ($powerPct / 100);

            $newValues[$index['temp_coolant_out']['sensor']->getId()] =
                $currentTemp + ($targetTemp - $currentTemp) * 0.08;
        }

        /**
         * Corelatie 2: temperatura iesire -> temperatura intrare sodiu
         *
         * Circuitul de racire are inertie termica.
         * Temperatura de intrare urmeaza iesirea cu un decalaj (lag).
         */
        if (isset($index['temp_coolant_out']) && isset($index['temp_coolant_in'])) {

            $tempOut = $index['temp_coolant_out']['value'];
            $currentIn = $index['temp_coolant_in']['value'];

            $targetIn = $tempOut - 150;

            $newValues[$index['temp_coolant_in']['sensor']->getId()] =
                $currentIn + ($targetIn - $currentIn) * 0.06;
        }

        /**
         * Corelatie 3: presiune circuit sodiu -> stabilitate aproape constanta
         *
         * Sodiu lichid functioneaza la presiune foarte joasa si stabila,
         * astfel incat variatiile sunt amortizate rapid catre nominal.
         */
        if (isset($index['pressure'])) {

            $currentPres = $index['pressure']['value'];

            $newValues[$index['pressure']['sensor']->getId()] =
                $currentPres + (self::SODIUM_PRESSURE_NOMINAL - $currentPres) * 0.03;
        }

        /**
         * Corelatie 4: putere -> activitate radioactiva Na-24
         *
         * Activarea neutronica a sodiului produce izotopul Na-24,
         * iar activitatea creste proportional cu puterea reactorului.
         */
        if (isset($index['power_percent']) && isset($index['activity_primary'])) {

            $powerPct = $index['power_percent']['value'];
            $currentActivity = $index['activity_primary']['value'];

            $na24Production = $powerPct * self::NA24_ACTIVATION_COEFFICIENT;

            // dezintegrare exponentiala a Na-24
            $decayFactor = exp(-log(2) / (15 * 3600) * 5);

            $targetActivity = $currentActivity * $decayFactor + $na24Production;

            $newValues[$index['activity_primary']['sensor']->getId()] =
                $currentActivity + ($targetActivity - $currentActivity) * 0.05;
        }

        /**
         * Corelatie 5: temperatura combustibil -> feedback Doppler (negativ)
         *
         * La temperaturi ridicate, sectiunea eficace neutronica scade
         * (efect Doppler), reducand automat puterea reactorului.
         */
        if (isset($index['temp_fuel_center']) && isset($index['power_percent'])) {

            $fuelTemp = $index['temp_fuel_center']['value'];
            $currentPower = $index['power_percent']['value'];

            if ($fuelTemp > 800) {
                $dopplerReduction =
                    ($fuelTemp - 800) * self::DOPPLER_COEFFICIENT;

                $newValues[$index['power_percent']['sensor']->getId()] =
                    max(0, $currentPower + $dopplerReduction * 0.03);
            }
        }

        /**
         * Corelatie 6: putere -> temperatura combustibil
         *
         * Puterea mai mare duce la incalzirea combustibilului nuclear.
         */
        if (isset($index['power_percent']) && isset($index['temp_fuel_center'])) {

            $powerPct = $index['power_percent']['value'];
            $currentFuelT = $index['temp_fuel_center']['value'];

            $minFuelT = $index['temp_fuel_center']['sensor']->getNormalMin();
            $maxFuelT = $index['temp_fuel_center']['sensor']->getNormalMax();

            $targetFuelT =
                $minFuelT + ($maxFuelT - $minFuelT) * ($powerPct / 100);

            $newValues[$index['temp_fuel_center']['sensor']->getId()] =
                $currentFuelT + ($targetFuelT - $currentFuelT) * 0.04;
        }

        /**
         * Corelatie 7: putere -> debit sodiu
         *
         * Reactorul FBR necesita debit crescut de sodiu la puteri mari
         * pentru a asigura racirea eficienta.
         */
        if (isset($index['power_percent']) && isset($index['flow_rate_primary'])) {

            $powerPct = $index['power_percent']['value'];
            $currentFlow = $index['flow_rate_primary']['value'];

            $nominalFlow =
                $index['flow_rate_primary']['sensor']->getNormalMax() ?? 15000;

            $targetFlow = $nominalFlow * (0.3 + 0.7 * ($powerPct / 100));

            $newValues[$index['flow_rate_primary']['sensor']->getId()] =
                $currentFlow + ($targetFlow - $currentFlow) * 0.05;
        }

        /**
         * Corelatie 8: debit sodiu -> vibratii pompe
         *
         * Cresterea debitului duce la cresterea vibratiilor mecanice
         * ale pompelor din circuitul primar.
         */
        if (isset($index['flow_rate_primary']) && isset($index['VI-001'])) {

            $flow = $index['flow_rate_primary']['value'];
            $currentVib = $index['VI-001']['value'];

            $nominalFlow =
                $index['flow_rate_primary']['sensor']->getNormalMax() ?? 15000;

            $nominalVib =
                $index['VI-001']['sensor']->getNormalMax() ?? 2.0;

            $flowRatio = $flow / $nominalFlow;

            $targetVib = $nominalVib * $flowRatio * $flowRatio;

            $newValues[$index['VI-001']['sensor']->getId()] =
                $currentVib + ($targetVib - $currentVib) * 0.04;
        }
    }
}