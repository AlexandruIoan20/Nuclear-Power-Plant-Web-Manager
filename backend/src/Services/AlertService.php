<?php

require_once __DIR__ . '/../Entities/Alert.php';

class AlertService {
    private AlertRepository $alertRepository;
    private EmailService $emailService;

    public function __construct(AlertRepository $alertRepository, EmailService $emailService) {
        $this->alertRepository = $alertRepository;
        $this->emailService = $emailService;
    }

    public function processSensorData(array $payload): void {
        $plantId = $payload['plant_id'] ?? '';
        $type = strtoupper($payload['type'] ?? '');
        $message = $payload['message'] ?? 'Alertă nespecificată.';

        if (empty($plantId) || empty($type)) {
            throw new Exception("Date insuficiente de la senzor.");
        }

    
        if ($type === 'NORMAL') {
            return; 
        }

        if (!in_array($type, ['ALARM', 'ALERT', 'SCRAM'])) {
            throw new Exception("Tip de alertă invalid.");
        }

      
        $alert = new Alert($plantId, $type, $message);
        $this->alertRepository->save($alert);

        $ownerEmail = $this->alertRepository->getPlantOwnerEmail($plantId);
        $targetEmail = $ownerEmail ?? 'admin@nuclear.ro'; // Fallback curent

        $this->emailService->sendAlert([
            'to_email' => $targetEmail,
            'subject' => "CRITICAL: [{$type}] la Centrala ID: {$plantId}",
            'message' => "Sistemul automat de senzori a declanșat o notificare de tip {$type}.\nDetalii tehnice: {$message}"
        ]);
    }

    public function getActivePopups(?string $userId = null): array {
        return $this->alertRepository->getUnreadAlertsForUser($userId);
    }

    public function dismissAlert(string $alertId): void {
        $this->alertRepository->markAsRead($alertId);
    }

    public function dismissAllAlerts(): void {
        $this->alertRepository->markAllAsRead();
    }

    public function savePlantEvent(string $plantId, string $type, string $message): void {
        $this->alertRepository->savePlantEvent($plantId, $type, $message);
    }

    public function dismissApproval(string $plantId): void {
        $this->alertRepository->dismissApproval($plantId);
    }
}