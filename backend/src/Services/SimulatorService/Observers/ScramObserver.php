<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Repositories/ReactorRepository.php';
require_once __DIR__ . '/../../../Entities/ReactorOperationalStatus.php';

class ScramObserver implements ObserverInterface {
    private ReactorRepository $reactorRepository;

    public function __construct(ReactorRepository $reactorRepository) {
        $this->reactorRepository = $reactorRepository;
    }

    public function update(ViolationEvent $event): void {
        $severity = $event->getSeverity();

        if ($severity !== 'EMERGENCY' && $severity !== 'ALERT') {
            return;
        }

        $reactor = $this->reactorRepository->findById($event->getReactorId());
        if ($reactor === null) {
            return;
        }

        if ($severity === 'EMERGENCY') {
            $reactor->setOperationalStatus(ReactorOperationalStatus::EMERGENCY_SHUTDOWN);
        } else {
            $reactor->setOperationalStatus(ReactorOperationalStatus::UNPLANNED_OUTAGE);
        }

        $this->reactorRepository->update($reactor);
    }
}
