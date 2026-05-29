<?php 

require_once __DIR__ . '/FeasibilityCheckers/GeologicalCriticalChecker.php'; 
require_once __DIR__ . '/FeasibilityCheckers/TechnicalCriticalChecker.php' ; 
require_once __DIR__ . '/FeasibilityCheckers/ScoringChecker.php'; 

class FeasibilityServiceFactory { 
    public static function create(PDO $db, PlantRepositoryFacade $plantRepositoryFacade): FeasibilityService { 
        $feasibilityRepository = new FeasibilityRepository($db); 

        $geologicalCriticalChecker = new GeologicalCriticalChecker(); 
        $technicalCriticalChecker = new TechnicalCriticalChecker(); 
        $scoringChecker = new ScoringChecker(); 

        $geologicalCriticalChecker->setNext($technicalCriticalChecker); 
        $technicalCriticalChecker->setNext($scoringChecker); 

        return new FeasibilityService(
            $plantRepositoryFacade, 
            $geologicalCriticalChecker, 
            $feasibilityRepository
        ); 
    }
}