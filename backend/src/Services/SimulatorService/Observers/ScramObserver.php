<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Repositories/ReactorRepository.php';
require_once __DIR__ . '/../../../Repositories/AlertRepository.php';
require_once __DIR__ . '/../../../Entities/ReactorOperationalStatus.php';

class ScramObserver implements ObserverInterface {
    private ReactorRepository $reactorRepository;
    private AlertRepository $alertRepository;

    public function __construct(ReactorRepository $reactorRepository, AlertRepository $alertRepository) {
        $this->reactorRepository = $reactorRepository;
        $this->alertRepository = $alertRepository;
    }

    public function update(ViolationEvent $event): void {
        if ($event->getSeverity() !== 'EMERGENCY') {
            return;
        }

        $reactor = $this->reactorRepository->findById($event->getReactorId());
        if ($reactor === null) {
            return;
        }

        $reactor->setOperationalStatus(ReactorOperationalStatus::EMERGENCY_SHUTDOWN);
        $this->reactorRepository->update($reactor);

        $data = $event->toAlertData();
        $this->alertRepository->saveReactorAlert($data);
    }
}
