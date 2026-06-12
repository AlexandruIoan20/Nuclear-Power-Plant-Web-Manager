<?php

require_once __DIR__ . '/../../../../Entities/Reactor.php';
require_once __DIR__ . '/../../../../Repositories/ReactorRepository.php';

class ReactorWearCalculator {
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
