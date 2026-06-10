<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';

class AlertObserver implements ObserverInterface {
    public function update(ViolationEvent $event): void {
        // TODO: implementeaza logica de alertare in timp real
        // De exemplu: WebSocket, SSE, push notification, etc.
    }
}
