<?php 

require_once __DIR__ . '/../../Services/AlertService.php';
require_once __DIR__ . '/../../Dto/PlantDTO.php';
require_once __DIR__ . '/../../Dto/PlantDetailsDTO.php'; 
require_once __DIR__ . '/../../Dto/GetPlantDTO.php';  
require_once __DIR__ . '/../../Dto/PlantListDTO.php';
require_once __DIR__ . '/../../Dto/PlantMapDTO.php';
require_once __DIR__ . '/../../Dto/PlantStatusListDTO.php';
require_once __DIR__ . '/../../Dto/CoordinatesPreviewResponseDTO.php';
require_once __DIR__ . '/../../Dto/ApiResponseDTO.php';
require_once __DIR__ . '/../../Services/LogService.php';

class DetailsPlantController { 
    public function __construct(
        private PlantServiceFacade $plantServiceFacade,
        private AlertService $alertService,
    ) {}

    public function getCountries() { 
        header('Content-Type: application/json; charset=UTF-8');
        
        http_response_code(200);
        echo json_encode($this->plantServiceFacade->getCountries());
        exit;
    }

    public function getPlant(string $id) {
        header('Content-Type: application/json; charset=utf-8');
    
        $plant = $this->plantServiceFacade->getCompletePlantProfile($id);
        if(!$plant || !$plant['details']) { 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Centrala nu a fost gasita")); 
            exit; 
        } 
        $dto = GetPlantDTO::fromServiceArray($plant);
    
        echo json_encode(new ApiResponseDTO(status: 'success', data: $dto));
    }

    public function getPlantDetails(string $id) { 
        header('Content-Type: application/json; charset=UTF-8');
        
        $plant = $this->plantServiceFacade->getPlantDetailsById($id); 

        if(!$plant) { 
            http_response_code(404); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Centrala nu a fost găsită."));
            exit; 
        }

        $plantDTO = PlantDetailsDTO::fromEntity($plant);

        http_response_code(200);
        echo json_encode(new ApiResponseDTO(status: 'success', data: $plantDTO));
        exit;
    }

    public function getMyPowerPlants(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $userId = AuthHelper::getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Neautorizat.'));
            exit;
        }

        $plants = $this->plantServiceFacade->getMyPowerPlants($userId);
        $payload = array_map(fn($plant) => PlantListDTO::fromDbArray($plant), $plants);

        http_response_code(200);
        echo json_encode(new ApiResponseDTO(status: 'success', data: $payload));
        exit;
    }

    public function getPowerPlantsList(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $plants = $this->plantServiceFacade->getAllPowerPlants();
        $payload = array_map(fn($plant) => PlantListDTO::fromDbArray($plant), $plants);

        http_response_code(200);
        echo json_encode(new ApiResponseDTO(status: 'success', data: $payload));
        exit;
    }

    
    public function getPendingApprovalsList(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $pendingPlants = $this->plantServiceFacade->getPendingApprovalsList();

        $payload = array_map(fn($plant) => PlantListDTO::fromDbArray($plant), $pendingPlants);

        http_response_code(200);
        echo json_encode(new ApiResponseDTO(status: 'success', data: $payload));
        exit;
    }

    public function getPlantsByStatus() { 
        header("Content-Type: application/json; charset=UTF-8"); 

        $status = $_GET['status'] ?? null;
    
        if (!$status) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Parametrul 'status' lipseste"));
            return;
        }

        if (!AuthHelper::isAuthenticated()) {
            $status = 'APPROVED';
        }
    
        $data = ['status' => $status];
    
        try { 
            $powerPlants = $this->plantServiceFacade->getPlantsByStatus($data); 

            $dtos = array_map(fn($plant) => PlantStatusListDTO::fromDbArray($plant), $powerPlants);
            
            http_response_code(200); 
            echo json_encode(new ApiResponseDTO(status: 'success', data: $dtos));
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] GET Plants By Status: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la cautarea dupa status: " . $e->getMessage()));
        }
    }
    
    public function getPowerPlantsMapData()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $plants = $this->plantServiceFacade->getAllPowerPlants();
        $dtos = array_map(fn($plant) => PlantMapDTO::fromDbArray($plant), $plants);

        http_response_code(200);
        echo json_encode(new ApiResponseDTO(status: 'success', data: $dtos));
        exit;
    }

    public function handleSavePlantDetails() { 
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        LogService::instance()->debug("[DEBUG] Date Formular API Creare: " . print_r($dateFormular, true));
        
        if (empty($dateFormular)) {
            http_response_code(400); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Nu s-au primit date."));
            exit;
        }

        try { 
            $responseDTO = $this->plantServiceFacade->savePlantDetails($dateFormular); 
            
            http_response_code(201); 
            echo json_encode(new ApiResponseDTO(status: 'success', message: "Centrala a fost salvată cu succes.", data: ['id' => $responseDTO->dataId]));
            exit; 
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] Save Plant: " . $e->getMessage());
            http_response_code(500); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la salvare: " . $e->getMessage()));
            exit;
        }
    }

    public function updateStatus(string $plantId) { 
        header('Content-Type: application/json; charset=UTF-8'); 

        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        LogService::instance()->debug("[DEBUG] Date Formular API Creare: " . print_r($dateFormular, true));
        
        if (empty($dateFormular)) {
            http_response_code(400); 
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Nu s-au primit date."));
            exit;
        }

        try { 
            $verified = $this->plantServiceFacade->updateStatus($dateFormular, $plantId);  
            
            if(!$verified) { 
                echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la actualizarea statusului")); 

                exit; 
            } 

            $newStatus = $dateFormular['status'] ?? '';
            $plant = $this->plantServiceFacade->getPlantDetailsById($plantId);
            $plantName = $plant ? $plant->getName() : $plantId;
            $type = match (strtoupper($newStatus)) {
                'APPROVED' => 'PLANT_APPROVED',
                'REJECTED' => 'PLANT_REJECTED',
                default => 'PLANT_STATUS_CHANGE',
            };
            $this->alertService->savePlantEvent(
                $plantId,
                $type,
                "Centrala \"{$plantName}\" a fost actualizată la statusul: {$newStatus}"
            );

            LogService::instance()->info(
                "[PLANT] Status actualizat: {$plantId} → {$newStatus}",
                null,
                $plantId
            );

            echo json_encode(new ApiResponseDTO(status: 'success', message: "Status actualizat cu succes")); 
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] Update Plant: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la actualizare statusului: " . $e->getMessage()));
            exit;
        }
    }

    public function submitForReview(string $plantId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $userId = AuthHelper::getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Neautorizat.'));
            exit;
        }

        try {
            $result = $this->plantServiceFacade->submitForReview($plantId, $userId);

            if (!$result) {
                http_response_code(400);
                echo json_encode(new ApiResponseDTO(status: 'error', message: 'Nu se poate trimite spre verificare. ' .
                    'Verificați: statusul să fie DRAFT, datele să fie complete și să fiți proprietarul.'));
                exit;
            }

            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Centrala a fost trimisă spre verificare (status REVIEW).'));
        } catch (Exception $e) {
            LogService::instance()->error("[ERROR] Submit Review: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare: ' . $e->getMessage()));
        }
    }

    public function reopenDraft(string $plantId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $userId = AuthHelper::getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Neautorizat.'));
            exit;
        }

        try {
            $result = $this->plantServiceFacade->reopenDraft($plantId, $userId);

            if (!$result) {
                http_response_code(400);
                echo json_encode(new ApiResponseDTO(status: 'error', message: 'Nu se poate redeschide centrala. Statusul curent trebuie să fie REJECTED sau REVIEW.'));
                exit;
            }

            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Centrala a fost redeschisă (status DRAFT).'));
        } catch (Exception $e) {
            LogService::instance()->error("[ERROR] Reopen Draft: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare: ' . $e->getMessage()));
        }
    }

    public function handleUpdatePlantDetails(string $id) { 
        header('Content-Type: application/json; charset=UTF-8');
        
        $jsonPayload = file_get_contents("php://input");
        $dateFormular = json_decode($jsonPayload, true); 

        LogService::instance()->debug("[DEBUG] Date Formular API Update pt ID {$id}: " . print_r($dateFormular, true));

        if (empty($dateFormular)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Date incomplete pentru actualizare."));
            exit;
        }

        try { 
            $this->plantServiceFacade->updatePlantDetails($dateFormular, $id); 
            
            http_response_code(200); 
            echo json_encode(new ApiResponseDTO(status: 'success', message: "Detaliile au fost actualizate cu succes."));
            exit; 
        } catch(Exception $e) { 
            LogService::instance()->error("[ERROR] Update Plant: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: "Eroare la actualizare: " . $e->getMessage()));
            exit;
        }
    }

    public function previewCoordinates()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents('php://input');
        $body = json_decode($jsonPayload, true);

        if (!$body || !isset($body['latitude']) || !isset($body['longitude'])) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Payload invalid.'));
            exit;
        }

        $lat = filter_var($body['latitude'], FILTER_VALIDATE_FLOAT);
        $lon = filter_var($body['longitude'], FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lon === false) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Coordonate invalide.'));
            exit;
        }

        try {
            $responseDTO = $this->plantServiceFacade->plantPreviewCoordinates($lat, $lon);
            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', data: $responseDTO));
        } catch (Throwable $e) {
            LogService::instance()->error("[PREVIEW ERROR] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la procesarea coordonatelor.'));
        }
        exit;
    }
}
