<?php

require_once __DIR__ . '/../Services/PlantServiceFacade.php';

class ApprovalController {
    private PlantServiceFacade $plantServiceFacade;

    public function __construct(PlantServiceFacade $plantServiceFacade) {
        $this->plantServiceFacade = $plantServiceFacade;
    }

    /**
     * Actualizează statusul unei centrale (ex: APPROVED, REJECTED).
     * Rută: PUT /api/power-plants/{id}/status
     */
    public function updateStatus($plantId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanPlantId = trim((string)$plantId);
        if (empty($cleanPlantId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID-ul facilității lipsește.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['status'] ?? null;

        if (!in_array($newStatus, ['APPROVED', 'REJECTED'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Statusul solicitat este invalid.']);
            exit;
        }

        $userRole = $_SESSION['user_role'] ?? ($_SESSION['user']['role'] ?? null);
        if ($userRole !== 'ADMIN') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Acces refuzat. Necesită privilegii de ADMIN.']);
            exit;
        }

        try {
            $this->plantServiceFacade->updatePlantStatus($cleanPlantId, $newStatus);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Statusul centralei a fost actualizat cu succes în: ' . $newStatus
            ]);
            exit;
        } catch (Exception $e) {
            LogService::instance()->error("[STATUS UPDATE ERROR] Eșec la modificarea centralei {$cleanPlantId}: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nu s-a putut procesa actualizarea: ' . $e->getMessage()]);
            exit;
        }
    }
}