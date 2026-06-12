<?php

require_once __DIR__ . '/../Services/ApprovalService.php';
require_once __DIR__ . '/../Services/AlertService.php';
require_once __DIR__ . '/../Dto/ApiResponseDTO.php';
require_once __DIR__ . '/../Services/LogService.php';

class ApprovalController {
    private ApprovalService $approvalService;
    private AlertService $alertService;

    public function __construct(ApprovalService $approvalService, AlertService $alertService) {
        $this->approvalService = $approvalService;
        $this->alertService = $alertService;
    }

    public function updateStatus(string $plantId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanPlantId = trim((string)$plantId);
        if (empty($cleanPlantId)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'ID-ul centralei lipsește.'));
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['status'] ?? null;

        if (!in_array($newStatus, ['APPROVED', 'REJECTED'], true)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Statusul solicitat este invalid. Trebuie să fie APPROVED sau REJECTED.'));
            exit;
        }

        try {
            if ($newStatus === 'APPROVED') {
                $this->approvalService->approve($cleanPlantId);
            } else {
                $this->approvalService->reject($cleanPlantId);
            }

            $type = $newStatus === 'APPROVED' ? 'PLANT_APPROVED' : 'PLANT_REJECTED';

            $this->alertService->savePlantEvent(
                $cleanPlantId,
                $type,
                "Centrala a fost actualizată la statusul: {$newStatus}"
            );

            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Statusul centralei a fost actualizat cu succes în: ' . $newStatus));
            exit;
        } catch (Exception $e) {
            LogService::instance()->error("[STATUS UPDATE ERROR] Eșec la modificarea centralei {$cleanPlantId}: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage()));
            exit;
        }
    }
}
