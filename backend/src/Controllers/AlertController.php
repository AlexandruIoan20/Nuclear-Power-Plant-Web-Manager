<?php

require_once __DIR__ . '/../Entities/Alert.php'; 

class AlertController {
    private AlertService $alertService;

    public function __construct(AlertService $alertService) {
        $this->alertService = $alertService;
    }

  
    public function receiveAlert(): void {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Metoda nepermisă.']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $this->alertService->processSensorData($input);
            
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Semnalul senzorului a fost procesat.']);
        } catch (\Throwable $e) {
            LogService::instance()->error("[SENSOR ERROR] " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    
    public function getUnread(): void {
        header('Content-Type: application/json; charset=UTF-8');
        
        try {
          
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Neautorizat']);
                exit;
            }

            $alerts = $this->alertService->getActivePopups($userId);

            $payload = array_map(function (Alert $alert) {
                return [
                    'id' => $alert->getId(),
                    'plant_id' => $alert->getPlantId(),
                    'type' => $alert->getType(),
                    'message' => $alert->getMessage(),
                    'created_at' => $alert->getCreatedAt()
                ];
            }, $alerts);

            echo json_encode(['status' => 'success', 'data' => $payload]);
        } catch (\Throwable $e) {
           
            LogService::instance()->error("[ALERT GET ERROR] " . $e->getMessage() . " în " . $e->getFile() . " linia " . $e->getLine());
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Eroare internă: ' . $e->getMessage()
            ]);
        }
    }

    
    public function markRead(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Neautorizat']);
                exit;
            }

            if ($id === 'all') {
                $this->alertService->dismissAllAlerts();
                echo json_encode(['status' => 'success', 'message' => 'Toate alertele au fost marcate ca citite.']);
                return;
            }

            if (str_starts_with($id, 'approval_')) {
                $plantId = substr($id, 9);
                $this->alertService->dismissApproval($plantId);
                echo json_encode(['status' => 'success', 'message' => 'Solicitarea a fost ascunsă.']);
                return;
            }

            $this->alertService->dismissAlert($id);
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $e) {
            LogService::instance()->error("[ALERT PUT ERROR] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}