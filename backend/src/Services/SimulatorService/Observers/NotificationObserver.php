<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';

class NotificationObserver implements ObserverInterface {
    public function update(ViolationEvent $event): void {
        // TODO: implementeaza logica de notificare (email, SMS, etc.)
        // De exemplu: trimite email catre operatori, trimite SMS de urgenta
    }
}
