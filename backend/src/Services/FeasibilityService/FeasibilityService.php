<?php

require_once __DIR__ . '/../../Dto/FeasibilityReportDTO.php'; 

class FeasibilityService
{
    private PlantRepositoryFacade $plantRepositoryFacade;
    private AbstractFeasibilityChecker $checker;
    private FeasibilityRepository $feasibilityRepository;

    public function __construct(
        PlantRepositoryFacade $plantRepositoryFacade,
        AbstractFeasibilityChecker $checker,
        FeasibilityRepository $feasibilityRepository
    ) {
        $this->plantRepositoryFacade = $plantRepositoryFacade;
        $this->checker = $checker;
        $this->feasibilityRepository = $feasibilityRepository;
    }

    public function generateAndSaveReport(string $powerPlantId): array
    {
        try {
            $plantData = $this->plantRepositoryFacade->getPlantData($powerPlantId);

            if (!$plantData) {
                return [
                    'success' => false,
                    'message' => 'Datele necesare pentru raport sunt incomplete.',
                    'data'    => null
                ];
            }

            $reportResult = $this->checker->check($plantData);
            $isSaved = $this->feasibilityRepository->saveReport($powerPlantId, $reportResult);

            if (!$isSaved) {
                return [
                    'success' => false,
                    'message' => 'A aparut o eroare la salvarea raportului.',
                    'data'    => null
                ];
            }

            $dto = FeasibilityReportDTO::fromResult($reportResult);

            return [
                'success' => true,
                'message' => 'Raport salvat cu succes.',
                'data'    => $dto
            ];

        } catch (Exception $e) {
            error_log('[FeasibilityService] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Eroare la generarea raportului de fezabilitate.',
                'data'    => null
            ];
        }
    }

    public function getFeasibilityReport(string $powerPlantId): array
    {
        try {
            $row = $this->feasibilityRepository->getLatestReportByPlantId($powerPlantId);

            if (!$row) {
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
            error_log('[FeasibilityService] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Eroare internă la citirea raportului.',
                'data'    => null
            ];
        }
    }
}