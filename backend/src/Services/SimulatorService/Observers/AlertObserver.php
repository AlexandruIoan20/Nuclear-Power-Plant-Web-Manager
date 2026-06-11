<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Repositories/AlertRepository.php';

class AlertObserver implements ObserverInterface {
    private AlertRepository $alertRepository;

    public function __construct(AlertRepository $alertRepository) {
        $this->alertRepository = $alertRepository;
    }

    public function update(ViolationEvent $event): void {
        if ($event->getSeverity() === 'EMERGENCY') {
            return;
        }

        $data = $event->toAlertData();
        $this->alertRepository->saveReactorAlert($data);
    }
}
