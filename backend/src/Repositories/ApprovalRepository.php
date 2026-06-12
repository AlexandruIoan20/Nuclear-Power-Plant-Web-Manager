<?php

class ApprovalRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function updatePlantStatus(string $plantId, string $newStatus): void {
        $stmt = $this->db->prepare("
            UPDATE power_plants SET status = :status, updated_at = NOW() WHERE id = :id
        ");
        $stmt->execute(['status' => $newStatus, 'id' => $plantId]);
    }

    public function findPlantStatusById(string $plantId): ?string {
        $stmt = $this->db->prepare("SELECT status FROM power_plants WHERE id = :id");
        $stmt->execute(['id' => $plantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['status'] : null;
    }
}
