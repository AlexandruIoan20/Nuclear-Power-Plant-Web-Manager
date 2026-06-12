<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Repositories/AlertRepository.php';
require_once __DIR__ . '/../../../Entities/Alert.php';

class AlertObserver implements ObserverInterface {
    private AlertRepository $alertRepository;

    private const DEBOUNCE_SECONDS = 60;

    private array $lastAlertTime = [];

    public function __construct(AlertRepository $alertRepository) {
        $this->alertRepository = $alertRepository;
    }

    public function update(ViolationEvent $event): void {
        $data = $event->toAlertData();
        $key = $event->getReactorId() . '|' . $event->getSensor()->getSensorCode() . '|' . $data['type'];

        $now = time();
        if (isset($this->lastAlertTime[$key]) && ($now - $this->lastAlertTime[$key]) < self::DEBOUNCE_SECONDS) {
            return;
        }
        $this->lastAlertTime[$key] = $now;

        $this->alertRepository->saveReactorAlert($data);

        $this->alertRepository->save(new Alert(
            $event->getPlantId(),
            $data['type'],
            $data['message']
        ));
    }
}
