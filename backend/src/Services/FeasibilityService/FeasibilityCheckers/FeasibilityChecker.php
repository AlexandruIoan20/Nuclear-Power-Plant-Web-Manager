<?php 

interface FeasibilityChecker { 
    public function setNext(FeasibilityChecker $feasibilityChecker): FeasibilityChecker; 
    public function check(array $plantData): array; 
}

