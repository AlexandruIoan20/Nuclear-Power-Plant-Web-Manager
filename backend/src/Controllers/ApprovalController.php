<?php

require_once __DIR__ . '/../Services/ApprovalService.php';

class ApprovalController {
    private ApprovalService $approvalService;

    public function __construct(ApprovalService $approvalService) {
        $this->approvalService = $approvalService;
    }

    public function updateStatus(string $plantId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanPlantId = trim((string)$plantId);
        if (empty($cleanPlantId)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID-ul centralei lipsește.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['status'] ?? null;

        if (!in_array($newStatus, ['APPROVED', 'REJECTED'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Statusul solicitat este invalid. Trebuie să fie APPROVED sau REJECTED.']);
            exit;
        }

        try {
            if ($newStatus === 'APPROVED') {
                $this->approvalService->approve($cleanPlantId);
            } else {
                $this->approvalService->reject($cleanPlantId);
            }

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Statusul centralei a fost actualizat cu succes în: ' . $newStatus
            ]);
            exit;
        } catch (Exception $e) {
            LogService::instance()->error("[STATUS UPDATE ERROR] Eșec la modificarea centralei {$cleanPlantId}: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}
