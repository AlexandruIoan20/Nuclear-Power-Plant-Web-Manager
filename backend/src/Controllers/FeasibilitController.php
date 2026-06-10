<?php 

class FeasibilityController { 
    private FeasibilityService $feasibilityService; 

    public function __construct(FeasibilityService $feasibilityService) { 
        $this->feasibilityService = $feasibilityService; 
    }

    public function generate(string $powerPlantId): void { 
        header("Content-Type: application/json; charset=utf-8"); 

        LogService::instance()->info("[FeasibilityController] Incepe generare raport pentru plantId={$powerPlantId}");
        $response = $this->feasibilityService->generateAndSaveReport($powerPlantId); 
        $statusCode = $response['success'] ? 200 : 400; 
        http_response_code($statusCode); 

        LogService::instance()->info("[FeasibilityController] Rezultat generare plantId={$powerPlantId} success=" . ($response['success'] ? 'true' : 'false') . " message=" . ($response['message'] ?? 'null'));
        echo json_encode($response); 
    }

    public function getLastByPlantId(string $powerPlantId): void {
        header('Content-Type: application/json; charset=utf-8'); 

        LogService::instance()->info("[FeasibilityController] Citire raport pentru plantId={$powerPlantId}");
        $response = $this->feasibilityService->getFeasibilityReport($powerPlantId); 
        $statusCode = $response['success'] ? 200 : 404; 
        http_response_code($statusCode); 

        echo json_encode($response); 
    }
}