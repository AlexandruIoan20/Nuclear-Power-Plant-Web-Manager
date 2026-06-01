<?php 

class FeasibilityController { 
    private FeasibilityService $feasibilityService; 

    public function __construct(FeasibilityService $feasibilityService) { 
        $this->feasibilityService = $feasibilityService; 
    }

    public function generate(string $powerPlantId): void { 
        header("Content-Type: application/json; charset=utf-8"); 

        $response = $this->feasibilityService->generateAndSaveReport($powerPlantId); 
        $statusCode = $response['success'] ? 200 : 400; 
        http_response_code($statusCode); 

        echo json_encode($response); 
    }

    public function getLastByPlantId(string $powerPlantId): void {
        header('Content-Type: application/json; charset=utf-8'); 

        $response = $this->feasibilityService->getFeasibilityReport($powerPlantId); 
        $statusCode = $response['success'] ? 200 : 404; 
        http_response_code($statusCode); 

        echo json_encode($response); 
    }
}