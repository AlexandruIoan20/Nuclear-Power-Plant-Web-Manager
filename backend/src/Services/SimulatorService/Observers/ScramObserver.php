<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';

class ScramObserver implements ObserverInterface {
    public function update(ViolationEvent $event): void {
        // TODO: implementeaza logica de interventie la EMERGENCY
        // De exemplu: declansare alarma sonora, notificare operator, logica de shutdown avansata
    }
}
