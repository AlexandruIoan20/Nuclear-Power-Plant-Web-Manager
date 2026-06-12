<?php

require_once __DIR__ . '/../../Services/PlantService/BasicPlantService.php'; 
require_once __DIR__ . '../../../Dto/BasicPlantDataDTO.php'; 
require_once __DIR__ . '/../../Dto/ApiResponseDTO.php'; 

class BasicPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade
    ) {}

    public function getBasicPlantData(string $plantId) { 
        header('Content-Type: application/json; charset=UTF-8'); 

        $basicPlantData = $this->plantServiceFacade->getBasicDataByPlantId($plantId); 

        if(!$basicPlantData) { 
            http_response_code(404); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Datele despre centrala nu au fost gasite")); 
            exit; 
        }

        $basicPlantDataDTO = BasicPlantDataDTO::fromEntity($basicPlantData); 

        http_response_code(200); 
        echo json_encode(new ApiResponseDTO(status: 'success', data: $basicPlantDataDTO)); 
        exit; 
    }

    public function createBasicPlantData(string $plantId) { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true);
        
        if ($dateFormular === null) {
            $dateFormular = [];
        }

        LogService::instance()->debug("[DEBUG] Date Formular decodate din JSON:");
        LogService::instance()->info(print_r($dateFormular, true));

        try { 
            $responseDTO = $this->plantServiceFacade->saveBasicData($dateFormular, $plantId); 
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Datele de bază au fost salvate cu succes.',
                'plantId' => $plantId,  
                "basicsId" => $responseDTO->dataId
            ]);

        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] POST Basic Data: " . $e->getMessage());
            
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la salvare: ' . $e->getMessage())); 
        }
    }


    public function updateBasicPlantData(string $plantId) { 
        $jsonPayload = file_get_contents('php://input');
        $dateFormular = json_decode($jsonPayload, true);
        
        if ($dateFormular === null) {
            $dateFormular = [];
        }

        LogService::instance()->debug("[DEBUG] Date Formular decodate din JSON (Update):");
        LogService::instance()->info(print_r($dateFormular, true));

        try { 
            $this->plantServiceFacade->updateBasicData($dateFormular, $plantId); 
            
            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Datele de bază au fost actualizate cu succes.'));

        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] POST/PUT Basic Data Update: " . $e->getMessage());
            
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la actualizare: ' . $e->getMessage())); 
        }
    }
}