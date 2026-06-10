<?php

require_once __DIR__ . '/../../Services/PlantServiceFacade.php'; 
require_once __DIR__ . '/../../Dto/GeologicalPlantDataDTO.php';
require_once __DIR__ . '/../../Services/LogService.php'; 

class GeologicalPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function getGeologicalPlantData(string $plantId): void { 
        header('Content-Type: application/json; charset=UTF-8'); 

        try {
            $dto = $this->plantServiceFacade->getGeologicalDataByPlantId($plantId); 

            if (!$dto) { 
                http_response_code(404); 
                echo json_encode(["status" => "error", "message" => "Datele geologice nu au fost găsite."]);
                exit;
            }

            http_response_code(200); 
            echo json_encode(["status" => "success", "data" => $dto]); 
        } catch (Exception $e) {
            LogService::instance()->error("GET Geological: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Eroare la preluarea datelor geologice."]);
        }
        exit;
    }

    public function createGeologicalPlantData(string $plantId): void { 
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true);

        if (empty($dateFormular)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nu s-au primit date."]);
            exit;
        }

        try {
            $responseDTO = $this->plantServiceFacade->saveGeologicalData($dateFormular, $plantId);
            
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Datele geologice au fost salvate cu succes.',
                'plantId' => $plantId,
                'geologicalId' => $responseDTO->dataId
            ]);
        } catch (Exception $e) {
            LogService::instance()->error("Create Geological: " . $e->getMessage());
            $code = (str_contains($e->getMessage(), 'Există deja')) ? 409 : 400;
            http_response_code($code);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function updateGeologicalPlantData(string $plantId): void { 
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true);

        if (empty($dateFormular)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nu s-au primit date pentru actualizare."]);
            exit;
        }

        try { 
            $this->plantServiceFacade->updateGeologicalData($dateFormular, $plantId); 
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Datele geologice au fost actualizate cu succes.'
            ]);
        } catch (Exception $e) { 
            LogService::instance()->error("Update Geological: " . $e->getMessage());
            $code = str_contains($e->getMessage(), 'găsit') ? 404 : 400;
            http_response_code($code);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]); 
        }
        exit;
    }
}