<?php 

interface ScoringStrategy { 
    public function calculate(array $plantData): array; 
}