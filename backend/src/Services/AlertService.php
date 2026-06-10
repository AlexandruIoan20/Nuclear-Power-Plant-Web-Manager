<?php

require_once __DIR__ . '/../Entities/Alert.php';

require_once __DIR__ . '/LogService.php';

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

        LogService::instance()->warning("Alertă declanșată de senzor", ['plant_id' => $plantId, 'type' => $type, 'message' => $message]);

        $alert = new Alert($plantId, $type, $message);
        $this->alertRepository->save($alert);
        LogService::instance()->info("Alertă salvată în baza de date", ['alert_id' => $alert->getId(), 'type' => $type, 'plant_id' => $plantId]);

        $ownerEmail = $this->alertRepository->getPlantOwnerEmail($plantId);
        $targetEmail = $ownerEmail ?? 'admin@nuclear.ro';

        $this->emailService->sendAlert([
            'to_email' => $targetEmail,
            'subject' => "CRITICAL: [{$type}] la Centrala ID: {$plantId}",
            'message' => "Sistemul automat de senzori a declanșat o notificare de tip {$type}.\nDetalii tehnice: {$message}"
        ]);
        LogService::instance()->info("Email alertă trimis", ['plant_id' => $plantId, 'type' => $type, 'target_email' => $targetEmail]);
    }

    public function getActivePopups(?string $userId = null): array {
        LogService::instance()->debug("Obținere alerte active", ['user_id' => $userId]);
        return $this->alertRepository->getUnreadAlertsForUser($userId);
    }

    public function dismissAlert(string $alertId): void {
        LogService::instance()->info("Alertă marcată ca citită", ['alert_id' => $alertId]);
        $this->alertRepository->markAsRead($alertId);
    }
}