<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Repositories/ReactorRepository.php';
require_once __DIR__ . '/../../../Entities/ReactorOperationalStatus.php';
require_once __DIR__ . '/../../../Services/LogService.php';

class ScramObserver implements ObserverInterface {
    private ReactorRepository $reactorRepository;

    private const DEBOUNCE_SECONDS = 60;
    private array $lastScramTime = [];

    public function __construct(ReactorRepository $reactorRepository) {
        $this->reactorRepository = $reactorRepository;
    }

    public function update(ViolationEvent $event): void {
        $severity = $event->getSeverity();

        if ($severity !== 'EMERGENCY' && $severity !== 'ALERT') {
            return;
        }

        $key = $event->getReactorId() . '|' . $severity;
        $now = time();
        if (isset($this->lastScramTime[$key]) && ($now - $this->lastScramTime[$key]) < self::DEBOUNCE_SECONDS) {
            return;
        }
        $this->lastScramTime[$key] = $now;

        $reactor = $this->reactorRepository->findById($event->getReactorId());
        if ($reactor === null) {
            return;
        }

        $newStatus = $severity === 'EMERGENCY'
            ? ReactorOperationalStatus::EMERGENCY_SHUTDOWN
            : ReactorOperationalStatus::UNPLANNED_OUTAGE;

        $reactor->setOperationalStatus($newStatus);
        $this->reactorRepository->update($reactor);

        try {
            $sensor = $event->getSensor();
            $level = $severity === 'EMERGENCY' ? 'CRITICAL' : 'ERROR';
            LogService::instance()->$level(
                "{$newStatus->value}: Reactorul {$event->getReactorId()} — {$sensor->getSensorCode()} / {$sensor->getSensorType()->value} = {$event->getValue()} (prag: {$event->getThreshold()})",
                ['severity' => $severity, 'value' => $event->getValue(), 'threshold' => $event->getThreshold()],
                $event->getPlantId(),
                $event->getReactorId()
            );
        } catch (\Throwable $e) {
            error_log("[ScramObserver] Eroare la scrierea logului: " . $e->getMessage());
        }
    }
}
