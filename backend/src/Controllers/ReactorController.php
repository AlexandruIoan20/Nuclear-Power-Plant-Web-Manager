<?php 

require_once __DIR__ . '/../Services/ReactorService.php';
require_once __DIR__ . '/../Services/SensorService.php';
require_once __DIR__ . '/../Dto/ApiResponseDTO.php';
require_once __DIR__ . '/../Services/LogService.php';

class ReactorController { 
    private ReactorService $reactorService; 
    private SensorService $sensorService; 

    public function __construct(ReactorService $reactorService, ?SensorService $sensorService = null) { 
        $this->reactorService = $reactorService; 
        $this->sensorService = $sensorService; 
    }

    public function getAllReactors() { 
        header('Content-Type: application/json; charset=UTF-8'); 

        try { 
            $reactors = $this->reactorService->getAllReactors(); 

            http_response_code(200); 
            echo json_encode(new ApiResponseDTO(status: 'success', data: $reactors)); 
            exit; 
        } catch(Exception $e) { 
            LogService::instance()->error('[ERROR] GET All Reactors: ' . $e->getMessage());
            http_response_code(500); 

            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la preluarea reactoarelor: " . $e->getMessage()));
            exit; 
        }
    }

    public function getReactor(string $id) { 
        header('Content-Type: application/json; charset=UTF-8'); 

        try { 
            $reactor = $this->reactorService->getReactor($id); 

            http_response_code(200); 
            echo json_encode(new ApiResponseDTO(status: 'success', data: $reactor)); 
            exit; 
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] GET Reactor {$id}: " . $e->getMessage());
            
            http_response_code(500); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage())); 
            exit; 
        }
    }

    public function getReactorsByPlant(string $plantId) { 
        header('Content-Type: application/json; charset=UTF-8'); 

        try { 
            $reactors = $this->reactorService->getReactorsByPlant($plantId); 

            http_response_code(200); 
            echo json_encode(new ApiResponseDTO(status: 'success', data: $reactors)); 
            exit; 
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] GET Reactors By plantId {$plantId}: " . $e->getMessage() );
            http_response_code(500); 

            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la preluarea reactoarelor: " . $e->getMessage()));
            exit;  
        }
    }

    public function createReactor() { 
        header('Content-Type: application/json; charset=UTF-8'); 

        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        LogService::instance()->debug("[DEBUG] Date Formular Create Reactor: " . print_r($dateFormular, true));

        if(empty($dateFormular)) { 
            http_response_code(400); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Nu s-au primit date.")); 
            exit; 
        }

        try { 
            $newReactorId = $this->reactorService->createReactor($dateFormular); 

            if ($this->sensorService && isset($dateFormular['reactorType'])) {
                $this->sensorService->populateSensorsForReactor($newReactorId, $dateFormular['reactorType']);
            }

            http_response_code(201); 
            echo json_encode([ 
                "status" => "success", 
                "message" => "Reactorul a fost creat cu succes", 
                "reactorId" => $newReactorId
            ]);

            exit; 
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] Create Reactor: " . $e->getMessage());
            http_response_code(400); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la crearea reactorului: " . $e->getMessage())); 
            exit;
        }
    }

    public function updateReactor(string $id) { 
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true);

        LogService::instance()->debug("[DEBUG] Date Formular API Update Reactor pt ID {$id}: " . print_r($dateFormular, true));

        if (empty($dateFormular)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Date incomplete pentru actualizare."));
            exit;
        }

        try {
            $this->reactorService->updateReactor($id, $dateFormular);
            
            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: "Reactorul a fost actualizat cu succes."));
            exit;
        } catch (\Exception $e) {
            LogService::instance()->error("[ERROR] Update Reactor {$id}: " . $e->getMessage());
            $code = str_contains($e->getMessage(), 'găsit') ? 404 : 400;
            
            http_response_code($code);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la actualizare: " . $e->getMessage()));
            exit;
        }
    }

    public function deleteReactor(string $id) {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $this->reactorService->deleteReactor($id);
            
            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: "Reactorul a fost șters cu succes."));
            exit;
        } catch (\Exception $e) {
            LogService::instance()->error("[ERROR] Delete Reactor {$id}: " . $e->getMessage());
            $code = str_contains($e->getMessage(), 'găsit') ? 404 : 500;
            
            http_response_code($code);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la ștergere: " . $e->getMessage()));
            exit;
        }
    }    
}