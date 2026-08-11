<?php 

/**
 * Interfata functionala pentru reprezentarea design patternului Strategy
 * 
 *  Fiecare tip de reactor are un mod separat de a face verificari. Fiecare are deficiente separate. 
 */
interface ScoringStrategy { 
    public function calculate(array $plantData): array; 
}