<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';

/**
 * Trait care implementeaza partea de "Subject" din Observer Pattern.
 *
 * Permite unei clase sa:
 * - ataseze observatori
 * - detaseze observatori
 * - notifice toti observatorii cand apare un eveniment
 *
 * Este reutilizabil si poate fi inclus in orice clasa care are nevoie
 * de mecanism de observare (event-driven behavior).
 */
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
