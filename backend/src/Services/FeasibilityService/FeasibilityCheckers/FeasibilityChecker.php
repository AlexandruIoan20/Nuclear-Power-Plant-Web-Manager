<?php 

/**
 * Interfata implementata de clasa abstracta a checkerelor 
 */
interface FeasibilityChecker { 
    public function setNext(FeasibilityChecker $feasibilityChecker): FeasibilityChecker; 
    public function check(array $plantData): array; 
}

