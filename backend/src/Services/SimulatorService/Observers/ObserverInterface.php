<?php

require_once __DIR__ . '/ViolationEvent.php';

interface ObserverInterface {
    public function update(ViolationEvent $event): void;
}
