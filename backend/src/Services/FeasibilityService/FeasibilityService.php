<?php

require_once __DIR__ . '/../../Dto/FeasibilityReportDTO.php'; 
require_once __DIR__ . '/../../Dto/ApiResponseDTO.php'; 
require_once __DIR__ . '/../../Helpers/TransactionManager.php'; 

/**
 *  Clasa de tip Service ce este responsabila cu generarea de rapoarte de fezabilitate pe baza
 *  informatiile introduse de catre operator. 
 */
class FeasibilityService {
    private PlantRepositoryFacade $plantRepositoryFacade;
    private FeasibilityRepository $feasibilityRepository;
    private TransactionManager $transactionManager; 

    /**
     *  @var AbstractFeasibilityChecker $checker Clasa abstracta ce este reprezentarea design patternului Chain of Responsibility
     */
    private AbstractFeasibilityChecker $checker;

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

    /**
     * Functie ce se ocupa cu generarea rapoartelor de fezabilitate. 
     * 
     * Porneste o tranzactie si se asigura ca exista centrala si ca este valida pentru generare. 
     * Mai apoi genereaza raportul si opreste tranzactia. 
     * 
     * @param string $powerPlantId ID-ul centralei pentru care se face raportul 
     * 
     * @return ApiResponseDTO 
     */
    public function generateAndSaveReport(string $powerPlantId): ApiResponseDTO
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
     
            return new ApiResponseDTO(status: 'success', message: 'Raport salvat cu succes.', data: $result);
     
        } catch (Exception $e) {
            LogService::instance()->error("[FeasibilityService] PlantId={$powerPlantId} Eroare: {$e->getMessage()}");
            return new ApiResponseDTO(status: 'error', message: $e->getMessage(), data: null);
        }
    }

    public function getFeasibilityReport(string $powerPlantId): ApiResponseDTO
    {
        try {
            $row = $this->feasibilityRepository->getLatestReportByPlantId($powerPlantId);

            if (!$row) {
                LogService::instance()->info("[FeasibilityService] PlantId={$powerPlantId}: nu s-a gasit raport");
                return new ApiResponseDTO(
                    status: 'error',
                    message: 'Nu a fost găsit niciun raport de fezabilitate pentru această centrală.',
                    data: null
                );
            }

            $dto = FeasibilityReportDTO::fromDatabase($row);

            return new ApiResponseDTO(status: 'success', message: 'Raport încărcat cu succes.', data: $dto);

        } catch (Exception $e) {
            LogService::instance()->error("[FeasibilityService] PlantId={$powerPlantId} Eroare la citire: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return new ApiResponseDTO(status: 'error', message: 'Eroare internă la citirea raportului.', data: null);
        }
    }
}