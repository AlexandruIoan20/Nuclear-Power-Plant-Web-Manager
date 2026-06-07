<?php

require_once __DIR__ . '/../Services/PlantServiceFacade.php';

class ApprovalController {
    private PlantServiceFacade $plantServiceFacade;

    public function __construct(PlantServiceFacade $plantServiceFacade) {
        $this->plantServiceFacade = $plantServiceFacade;
    }

    /**
     * Schimbă statusul unei centrale în APPROVED.
     * Rută: PUT /api/power-plants/{id}/approve
     */
    public function approvePlant($plantId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanPlantId = trim((string)$plantId);

        if (empty($cleanPlantId)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'ID-ul facilității nucleare este invalid sau lipsește.'
            ]);
            exit;
        }

        $userRole = $_SESSION['user_role'] ?? ($_SESSION['user']['role'] ?? null);

        if ($userRole !== 'ADMIN') {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Acces refuzat. Doar utilizatorii cu privilegii de ADMIN pot aproba facilități nucleare.'
            ]);
            exit;
        }

        try {
            $this->plantServiceFacade->updatePlantStatus($cleanPlantId, 'APPROVED');

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Centrala a fost revizuită și aprobată cu succes. Aceasta este acum activă în sistem.'
            ]);
            exit;
        } catch (Exception $e) {
            error_log("[APPROVAL ERROR] Eșec la aprobarea centralei {$cleanPlantId}: " . $e->getMessage());
            
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Nu s-a putut procesa aprobarea: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}