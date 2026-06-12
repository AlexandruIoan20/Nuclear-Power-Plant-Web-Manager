<?php

require_once __DIR__ . '/../Entities/Alert.php'; 
require_once __DIR__ . '/../Dto/AlertListDTO.php'; 
require_once __DIR__ . '/../Dto/ApiResponseDTO.php'; 

class AlertController {
    private AlertService $alertService;

    public function __construct(AlertService $alertService) {
        $this->alertService = $alertService;
    }

  
    public function receiveAlert(): void {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Metoda nepermisă.'));
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $this->alertService->processSensorData($input);

            LogService::instance()->info(
                "Semnal senzor procesat: " . ($input['sensor_code'] ?? 'unknown'),
                ['plant_id' => $input['plant_id'] ?? null],
                $input['plant_id'] ?? null
            );

            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Semnalul senzorului a fost procesat.'));
        } catch (\Throwable $e) {
            LogService::instance()->error("[SENSOR ERROR] " . $e->getMessage());
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage()));
        }
    }

    
    public function getUnread(): void {
        header('Content-Type: application/json; charset=UTF-8');
        
        try {
          
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                http_response_code(401);
                echo json_encode(new ApiResponseDTO(status: 'error', message: 'Neautorizat'));
                exit;
            }

            $alerts = $this->alertService->getActivePopups($userId);

            $payload = array_map(fn(Alert $alert) => AlertListDTO::fromEntity($alert), $alerts);

            echo json_encode(new ApiResponseDTO(status: 'success', data: $payload));
        } catch (\Throwable $e) {
           
            LogService::instance()->error("[ALERT GET ERROR] " . $e->getMessage() . " în " . $e->getFile() . " linia " . $e->getLine());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare internă: ' . $e->getMessage()));
        }
    }

    
    public function markRead(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(new ApiResponseDTO(status: 'error', message: 'Neautorizat'));
                exit;
            }

            if ($id === 'all') {
                $this->alertService->dismissAllAlerts();
                LogService::instance()->info("[ALERT] Toate alertele au fost marcate ca citite.");
                echo json_encode(['status' => 'success', 'message' => 'Toate alertele senzori au fost marcate ca citite.']);
                return;
            }

            if ($id === 'plant-all') {
                $this->alertService->dismissAllPlantEvents();
                LogService::instance()->info("[ALERT] Toate evenimentele de centrale au fost marcate ca citite.");
                echo json_encode(['status' => 'success', 'message' => 'Toate notificările de centrale au fost marcate ca citite.']);
                return;
            }

            if (str_starts_with($id, 'approval_')) {
                $plantId = substr($id, 9);
                $this->alertService->dismissApproval($plantId);
                LogService::instance()->info("[ALERT] Solicitare ascunsă pentru planta {$plantId}.");
                echo json_encode(['status' => 'success', 'message' => 'Solicitarea a fost ascunsă.']);
                return;
            }

            $this->alertService->dismissAlert($id);
            LogService::instance()->info("[ALERT] Alertă {$id} marcată ca citită.");
            echo json_encode(new ApiResponseDTO(status: 'success'));
        } catch (\Throwable $e) {
            LogService::instance()->error("[ALERT PUT ERROR] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage()));
        }
    }
}