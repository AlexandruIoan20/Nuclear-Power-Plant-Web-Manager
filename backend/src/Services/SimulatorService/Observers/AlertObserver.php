<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Repositories/AlertRepository.php';
require_once __DIR__ . '/../../../Entities/Alert.php';

class AlertObserver implements ObserverInterface {
    private AlertRepository $alertRepository;

    public function __construct(AlertRepository $alertRepository) {
        $this->alertRepository = $alertRepository;
    }

    public function update(ViolationEvent $event): void {
        $data = $event->toAlertData();

        $this->alertRepository->saveReactorAlert($data);

        $this->alertRepository->save(new Alert(
            $event->getPlantId(),
            $data['type'],
            $data['message']
        ));
    }
}
