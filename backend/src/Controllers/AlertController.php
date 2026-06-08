<?php

class AlertController {
    private AlertService $alertService;

    public function __construct(AlertService $alertService) {
        $this->alertService = $alertService;
    }

   
    public function receiveAlert(): void {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $this->alertService->processSensorData($input);
            
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Semnalul senzorului a fost procesat.']);
        } catch (Exception $e) {
            error_log("[SENSOR ERROR] " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

   
    public function getUnread(): void {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        header('Content-Type: application/json; charset=UTF-8');

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
    }

    /**
     * PUT /api/alerts/{id}/read
     * Apelat din frontend când operatorul dă click pe "Dismiss" la pop-up.
     */
    public function markRead(string $id): void {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        header('Content-Type: application/json; charset=UTF-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            exit;
        }

        $this->alertService->dismissAlert($id);
        echo json_encode(['status' => 'success']);
    }
}