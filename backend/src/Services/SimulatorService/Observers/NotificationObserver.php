<?php

require_once __DIR__ . '/ObserverInterface.php';
require_once __DIR__ . '/ViolationEvent.php';
require_once __DIR__ . '/../../../Services/EmailService.php';
require_once __DIR__ . '/../../../Repositories/AlertRepository.php';
require_once __DIR__ . '/../../../Services/LogService.php';

class NotificationObserver implements ObserverInterface {
    private EmailService $emailService;
    private AlertRepository $alertRepository;

    private const DEBOUNCE_SECONDS = 300;
    private array $lastEmailTime = [];

    public function __construct(EmailService $emailService, AlertRepository $alertRepository) {
        $this->emailService = $emailService;
        $this->alertRepository = $alertRepository;
    }

    public function update(ViolationEvent $event): void {
        if ($event->getSeverity() !== 'EMERGENCY') {
            return;
        }

        $key = $event->getReactorId() . '|' . $event->getSeverity();
        $now = time();
        if (isset($this->lastEmailTime[$key]) && ($now - $this->lastEmailTime[$key]) < self::DEBOUNCE_SECONDS) {
            return;
        }
        $this->lastEmailTime[$key] = $now;

        $ownerEmail = $this->alertRepository->getPlantOwnerEmail($event->getPlantId());
        $targetEmail = $ownerEmail ?? getenv('ALERT_EMAIL_FALLBACK') ?: 'admin@nuclear.ro';

        $sensor = $event->getSensor();
        $unit = $sensor->getUnitOfMeasure() ?? '';

        $subject = "EMERGENCY: SCRAM pe reactorul {$event->getReactorId()}";
        $message = "Reactorul {$event->getReactorId()} a fost oprit de urgență.\n\n"
                 . "Senzor: {$sensor->getSensorType()->value} ({$sensor->getSensorCode()})\n"
                 . "Valoare: {$event->getValue()}{$unit}\n"
                 . "Prag SCRAM: {$event->getThreshold()}{$unit}\n"
                 . "Timestamp: {$event->getTimestamp()}";

        try {
            $this->emailService->sendAlert([
                'to_email' => $targetEmail,
                'subject' => $subject,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            LogService::instance()->error("[SCRAM EMAIL] Eroare trimitere email: " . $e->getMessage());
        }
    }
}
