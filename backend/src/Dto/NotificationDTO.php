<?php

require_once __DIR__ . '/BaseDTO.php';

class NotificationDTO extends BaseDTO {
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $severity,
        public readonly string $title,
        public readonly string $message,
        public readonly string $date,
        public readonly string $targetRole = 'ALL',
        public readonly ?string $targetEmail = null,
    ) {}

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            'date' => $this->date,
            'target_role' => $this->targetRole,
            'target_email' => $this->targetEmail,
        ];
    }

    public static function fromAlert(string $alertId, string $alertType, string $alertMessage, ?string $alertDate): self {
        return new self(
            id: 'alert_' . $alertId,
            type: 'SENSOR_ALERT',
            severity: $alertType,
            title: 'Avertizare Senzor: ' . $alertType,
            message: $alertMessage,
            date: $alertDate ?: date('Y-m-d H:i:s'),
            targetRole: 'ALL',
        );
    }

    public static function fromApprovalPlant(string $plantId, string $plantName, ?string $createdAt): self {
        return new self(
            id: 'approval_' . $plantId,
            type: 'SYSTEM_APPROVAL',
            severity: 'INFO',
            title: 'Solicitare de Aprobare',
            message: 'Facilitatea nucleară "' . $plantName . '" necesită validare operațională.',
            date: $createdAt ?: date('Y-m-d H:i:s'),
            targetRole: 'ADMIN',
        );
    }
}
