<?php

require_once __DIR__ . '/../Repositories/ApprovalRepository.php';

class ApprovalService {
    private ApprovalRepository $approvalRepository;

    public function __construct(ApprovalRepository $approvalRepository) {
        $this->approvalRepository = $approvalRepository;
    }

    public function approve(string $plantId): void {
        $this->validateTransition($plantId, 'APPROVED');
        $this->approvalRepository->updatePlantStatus($plantId, 'APPROVED');
    }

    public function reject(string $plantId): void {
        $this->validateTransition($plantId, 'REJECTED');
        $this->approvalRepository->updatePlantStatus($plantId, 'REJECTED');
    }

    private function validateTransition(string $plantId, string $targetStatus): void {
        $currentStatus = $this->approvalRepository->findPlantStatusById($plantId);

        if ($currentStatus === null) {
            throw new \Exception("Centrala cu ID-ul {$plantId} nu a fost găsită.");
        }

        if ($currentStatus !== 'REVIEW') {
            throw new \Exception(
                "Doar centralele cu status REVIEW pot fi aprobate sau respinse. Status curent: {$currentStatus}"
            );
        }

        if (!in_array($targetStatus, ['APPROVED', 'REJECTED'], true)) {
            throw new \Exception("Statusul țintă trebuie să fie APPROVED sau REJECTED.");
        }
    }
}
