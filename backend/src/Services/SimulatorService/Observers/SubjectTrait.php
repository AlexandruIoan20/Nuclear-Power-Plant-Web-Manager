<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';

trait SubjectTrait {
    private array $observers = [];

    public function attachObserver(ObserverInterface $observer): void {
        $this->observers[] = $observer;
    }

    public function detachObserver(ObserverInterface $observer): void {
        $this->observers = array_filter(
            $this->observers,
            fn($o) => $o !== $observer
        );
    }

    protected function notifyObservers(ViolationEvent $event): void {
        foreach ($this->observers as $observer) {
            $observer->update($event);
        }
    }
}
