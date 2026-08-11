<?php

require_once __DIR__ . '/ViolationEvent.php';

/**
 * Interfata functionala pentru a ajuta la implementare design patternului Observer
 * 
 * Justificarea importantei: Fiecare observer este responsabil de a face un lucr atunci cand are loc un eveniment
 */
interface ObserverInterface {
    public function update(ViolationEvent $event): void;
}
