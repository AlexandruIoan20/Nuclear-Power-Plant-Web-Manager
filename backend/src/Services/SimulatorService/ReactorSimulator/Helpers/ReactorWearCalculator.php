<?php

require_once __DIR__ . '/../../../../Entities/Reactor.php';
require_once __DIR__ . '/../../../../Repositories/ReactorRepository.php';

/**
 * Clasa ajutatoare pentru calcularea indicelui de uzura a centralei.
 *
 * Indicele de uzura reprezinta o aproximare a degradarii cumulative a reactorului
 * in functie de timpul de viata proiectat si nivelul de incarcare (power factor).
 *
 * Valoarea este un numar normalizat intre 0 si 1:
 * - 0 = reactor nou
 * - 1 = sfarsit de viata / uzura maxima
 */
class ReactorWearCalculator {
        /**
     * Calculeaza incrementul de uzura pe baza puterii si duratei de viata proiectate.
     *
     * Formula:
     * - se determina durata totala de viata in secunde
     * - se calculeaza un factor de incarcare (load factor)
     *   = 0.30 + 0.70 * (powerPercent / 100)
     *     (chiar si la putere 0, reactorul are uzura minima de baza)
     *
     * - uzura rezultata este:
     *   loadFactor / totalLifetimeSeconds * 5
     *
     * Interpretare:
     * - un reactor care ruleaza mai aproape de 100% se degradeaza mai rapid
     * - un reactor cu viata mai lunga are uzura mai lenta
     *
     * @param float $powerPercent nivelul de incarcare al reactorului (0-100%)
     * @param int $designLifetimeYr durata de viata proiectata in ani
     * @return float incrementul de uzura pentru un pas de simulare
     */
    public function calculate(float $powerPercent, int $designLifetimeYr): float {
        $totalLifetimeSeconds = $designLifetimeYr * 365 * 24 * 3600;
        $loadFactor = 0.30 + 0.70 * ($powerPercent / 100);

        return $loadFactor / $totalLifetimeSeconds * 5;
    }

    public function applyWear(Reactor $reactor, float $powerPercent, ReactorRepository $reactorRepository): void {
        $wearDelta = $this->calculate($powerPercent, $reactor->getDesignLifetimeYr() ?? 40);

        $currentWear = $reactor->getWearIndex() ?? 0.0;
        $newWear = min(1.0, $currentWear + $wearDelta);

        $reactor->setWearIndex($newWear);
        $reactorRepository->update($reactor);
    }
}
