<?php

class AlertRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function save(Alert $alert): void {
        $stmt = $this->db->prepare(
            "INSERT INTO alerts (plant_id, alert_type, message, is_read) 
             VALUES (:plant_id, :alert_type, :message, :is_read)"
        );
        $stmt->execute([
            'plant_id' => $alert->getPlantId(),
            'alert_type' => $alert->getType(),
            'message' => $alert->getMessage(),
            'is_read' => $alert->isRead() ? 1 : 0
        ]);
    }

    public function getUnreadAlertsForUser(?string $userId = null): array {
    
        
        $stmt = $this->db->query(
            "SELECT id, plant_id, alert_type, message, is_read, created_at 
             FROM alerts 
             WHERE is_read = 0 
             ORDER BY created_at DESC"
        );
        
        $alerts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $alerts[] = new Alert($row['plant_id'], $row['alert_type'], $row['message'], (bool)$row['is_read'], $row['id'], $row['created_at']);
        }
        return $alerts;
    }

    public function markAsRead(string $id): void {
        $stmt = $this->db->prepare("UPDATE alerts SET is_read = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function markAllAsRead(): void {
        $this->db->exec("UPDATE alerts SET is_read = 1 WHERE is_read = 0");
        $this->db->exec("UPDATE reactor_alerts SET is_read = 1 WHERE is_read = 0");
    }

    public function saveReactorAlert(array $data): void {
        $stmt = $this->db->prepare(
            "INSERT INTO reactor_alerts (reactor_id, plant_id, type, severity, sensor_type, value, threshold, message)
             VALUES (:reactor_id, :plant_id, :type, :severity, :sensor_type, :value, :threshold, :message)"
        );
        $stmt->execute([
            'reactor_id' => $data['reactor_id'],
            'plant_id' => $data['plant_id'],
            'type' => $data['type'],
            'severity' => $data['severity'],
            'sensor_type' => $data['sensor_type'],
            'value' => $data['value'],
            'threshold' => $data['threshold'],
            'message' => $data['message'],
        ]);
    }

    public function savePlantEvent(string $plantId, string $type, string $message): void {
        $stmt = $this->db->prepare(
            "INSERT INTO alerts (plant_id, alert_type, message) VALUES (:plant_id, :alert_type, :message)"
        );
        $stmt->execute([
            'plant_id' => $plantId,
            'alert_type' => $type,
            'message' => $message,
        ]);
    }

    public function getUnreadReactorAlerts(int $limit = 500): array {
        $stmt = $this->db->prepare(
            "SELECT id, reactor_id, plant_id, type, severity, sensor_type, value, threshold, message, created_at
             FROM reactor_alerts
             WHERE is_read = 0
             ORDER BY created_at DESC
             LIMIT :lim"
        );
        $stmt->execute(['lim' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPlantEvents(): array {
        $stmt = $this->db->query(
            "SELECT id, plant_id, alert_type, message, is_read, created_at 
             FROM alerts 
             WHERE alert_type IN ('PLANT_STATUS_CHANGE', 'PLANT_APPROVED', 'PLANT_REJECTED')
             AND is_read = 0
             ORDER BY created_at DESC"
        );
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = new Alert($row['plant_id'], $row['alert_type'], $row['message'], (bool)$row['is_read'], $row['id'], $row['created_at']);
        }
        return $events;
    }

    public function dismissApproval(string $plantId): void {
        $stmt = $this->db->prepare(
            "INSERT INTO alerts (plant_id, alert_type, message) VALUES (:plant_id, 'DISMISSED_APPROVAL', :message)"
        );
        $stmt->execute([
            'plant_id' => $plantId,
            'message' => "Approval request for plant {$plantId} was dismissed.",
        ]);
    }

    public function getDismissedApprovalPlantIds(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT plant_id FROM alerts WHERE alert_type = 'DISMISSED_APPROVAL'"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPlantOwnerEmail(string $plantId): ?string {
        $stmt = $this->db->prepare("
            SELECT u.email 
            FROM users u 
            JOIN power_plants p ON p.created_by = u.id 
            WHERE p.id = :plant_id
        ");
        $stmt->execute(['plant_id' => $plantId]);
        $email = $stmt->fetchColumn();
        return $email ?: null;
    }
}
