<?php 

require_once __DIR__ . '/FeasibilityCheckers/GeologicalCriticalChecker.php'; 
require_once __DIR__ . '/FeasibilityCheckers/TechnicalCriticalChecker.php' ; 
require_once __DIR__ . '/FeasibilityCheckers/ScoringChecker.php'; 
require_once __DIR__ . '/../../Helpers/TransactionManager.php'; 


/**
 * Clasa ce reprezinta implementarea design patternului Factory si este responsabila 
 * cu logica creerii clasei ServiceFactory. 
 */
class FeasibilityServiceFactory { 
    /**
     *  Functia de creare a serviciului. Initializeaza checkerele si le pune in chain 
     * 
     * @return FeasibilityService
     */
    public static function create(
            PDO $db,
            PlantRepositoryFacade $plantRepositoryFacade,
            TransactionManager $transactionManager): FeasibilityService { 
        $feasibilityRepository = new FeasibilityRepository($db); 

        $geologicalCriticalChecker = new GeologicalCriticalChecker(); 
        $technicalCriticalChecker = new TechnicalCriticalChecker(); 
        $scoringChecker = new ScoringChecker(); 

        $geologicalCriticalChecker->setNext($technicalCriticalChecker); 
        $technicalCriticalChecker->setNext($scoringChecker); 

        return new FeasibilityService(
            $plantRepositoryFacade, 
            $geologicalCriticalChecker, 
            $feasibilityRepository, 
            $transactionManager
        ); 
    }
}