<?php

require_once __DIR__ . '/../../Dto/FeasibilityReportDTO.php'; 
require_once __DIR__ . '/../../Helpers/TransactionManager.php'; 
require_once __DIR__ . '/../LogService.php'; 

class FeasibilityService
{
    private PlantRepositoryFacade $plantRepositoryFacade;
    private AbstractFeasibilityChecker $checker;
    private FeasibilityRepository $feasibilityRepository;
    private TransactionManager $transactionManager; 

    public function __construct(
        PlantRepositoryFacade $plantRepositoryFacade,
        AbstractFeasibilityChecker $checker,
        FeasibilityRepository $feasibilityRepository, 
        TransactionManager $transactionManager
    ) {
        $this->plantRepositoryFacade = $plantRepositoryFacade;
        $this->checker = $checker;
        $this->feasibilityRepository = $feasibilityRepository;
        $this->transactionManager = $transactionManager; 
    }

    public function generateAndSaveReport(string $powerPlantId): array
    {
        try {
            $result = $this->transactionManager->run(function() use ($powerPlantId) {
                $plantData = $this->plantRepositoryFacade->getPlantData($powerPlantId);
    
                if (!$plantData) {
                    throw new RuntimeException('Datele necesare pentru raport sunt incomplete.');
                }
    
                $reportResult = $this->checker->check($plantData);
                $isSaved = $this->feasibilityRepository->saveReport($powerPlantId, $reportResult);
    
                if (!$isSaved) {
                    throw new RuntimeException('A aparut o eroare la salvarea raportului.');
                }
    
                return FeasibilityReportDTO::fromResult($reportResult);
            });
    
            return ['success' => true, 'message' => 'Raport salvat cu succes.', 'data' => $result];
    
        } catch (Exception $e) {
            LogService::instance()->info("PlantId={$powerPlantId} Eroare: {$e->getMessage()}");
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    public function getFeasibilityReport(string $powerPlantId): array
    {
        try {
            $row = $this->feasibilityRepository->getLatestReportByPlantId($powerPlantId);

            if (!$row) {
                LogService::instance()->info("PlantId={$powerPlantId}: nu s-a gasit raport");
                return [
                    'success' => false,
                    'message' => 'Nu a fost găsit niciun raport de fezabilitate pentru această centrală.',
                    'data'    => null
                ];
            }

            $dto = FeasibilityReportDTO::fromDatabase($row);

            return [
                'success' => true,
                'message' => 'Raport încărcat cu succes.',
                'data'    => $dto
            ];

        } catch (Exception $e) {
            LogService::instance()->info("PlantId={$powerPlantId} Eroare la citire: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return [
                'success' => false,
                'message' => 'Eroare internă la citirea raportului.',
                'data'    => null
            ];
        }
    }
}