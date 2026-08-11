<?php 

require_once __DIR__ . '/../../../Entities/ReactorSensor.php'; 

/**
 * Strategie functionala pentru calcularea masuratorii unui senzor 
 */
interface SensorGeneratorStrategy { 
    /**
     * Genereaza noua valoare a senzorului pe baza valorii curente si a caracteristicilor senzorului.
     *
     * Implementarile acestei metode pot introduce:
     * - variatii aleatorii (noise)
     * - derivare in timp (drift)
     * - modele fizice de evolutie
     * - limitari bazate pe intervalele senzorului
     *
     * @param float $currentValue valoarea actuala masurata de senzor
     * @param ReactorSensor $sensor entitatea senzorului (contine limite, tip, configuratie)
     * @return float noua valoare simulata a senzorului
     */
    public function generate(float $currentValue, ReactorSensor $sensor): float; 
}