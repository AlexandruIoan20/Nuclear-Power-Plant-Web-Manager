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

    public function getPlantOwnerEmail(string $plantId): ?string {
        // ARHITECTURĂ VIITOARE: Extragerea email-ului proprietarului atunci când tabela va fi actualizată
        /*
        $stmt = $this->db->prepare("
            SELECT u.email 
            FROM users u 
            JOIN power_plants p ON p.owner_id = u.id 
            WHERE p.id = :plant_id
        ");
        $stmt->execute(['plant_id' => $plantId]);
        $email = $stmt->fetchColumn();
        return $email ?: null;
        */
        
        return null; // Fallback temporar
    }
}