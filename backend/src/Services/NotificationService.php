<?php

require_once __DIR__ . '/PlantServiceFacade.php';
require_once __DIR__ . '/AlertService.php';
require_once __DIR__ . '/../Repositories/AlertRepository.php';
require_once __DIR__ . '/../Entities/Alert.php';

class NotificationService {
    private PlantServiceFacade $plantService;
    private AlertService $alertService;
    private AlertRepository $alertRepository;

    public function __construct(PlantServiceFacade $plantService, AlertService $alertService, AlertRepository $alertRepository) {
        $this->plantService = $plantService;
        $this->alertService = $alertService;
        $this->alertRepository = $alertRepository;
    }

    public function getAlertNotifications(): array {
        $notifications = [];
        $seen = [];

        $alerts = $this->alertService->getActivePopups();

        foreach ($alerts as $alert) {
            $alertType = $alert->getType();
            if (in_array($alertType, ['PLANT_STATUS_CHANGE', 'PLANT_APPROVED', 'PLANT_REJECTED', 'DISMISSED_APPROVAL'])) {
                continue;
            }
            $key = $alert->getPlantId() . '|' . $alertType . '|' . substr($alert->getMessage(), 0, 60) . '|' . date('YmdHi', strtotime($alert->getCreatedAt()));
            $seen[$key] = true;
            $notifications[] = [
                'id' => 'alert_' . $alert->getId(),
                'type' => 'SENSOR_ALERT',
                'severity' => $alertType,
                'title' => 'Avertizare Senzor: ' . $alertType,
                'message' => $alert->getMessage(),
                'date' => $alert->getCreatedAt() ?: date('Y-m-d H:i:s'),
                'target_role' => 'ALL',
                'target_email' => null
            ];
        }

        $reactorAlerts = $this->alertRepository->getUnreadReactorAlerts();

        foreach ($reactorAlerts as $ra) {
            $key = $ra['plant_id'] . '|' . ($ra['type'] ?? 'ALERT') . '|' . substr($ra['message'], 0, 60) . '|' . date('YmdHi', strtotime($ra['created_at']));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $notifications[] = [
                'id' => 'reactor_alert_' . $ra['id'],
                'type' => 'SENSOR_ALERT',
                'severity' => $ra['type'] ?? 'ALERT',
                'title' => 'Avertizare Senzor: ' . ($ra['type'] ?? 'ALERT'),
                'message' => $ra['message'],
                'date' => $ra['created_at'] ?: date('Y-m-d H:i:s'),
                'target_role' => 'ALL',
                'target_email' => null
            ];
        }

        usort($notifications, function($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        return $notifications;
    }

    public function getPlantNotifications(string $userRole): array {
        $notifications = [];

        $plantEvents = $this->alertRepository->getPlantEvents();

        foreach ($plantEvents as $event) {
            $notifications[] = [
                'id' => 'plant_' . $event->getId(),
                'type' => 'PLANT_EVENT',
                'severity' => 'INFO',
                'title' => $this->getPlantEventTitle($event->getType()),
                'message' => $event->getMessage(),
                'date' => $event->getCreatedAt() ?: date('Y-m-d H:i:s'),
                'target_role' => 'ADMIN',
                'target_email' => null
            ];
        }

        if ($userRole === 'ADMIN') {
            $dismissedIds = $this->alertRepository->getDismissedApprovalPlantIds();
            $pendingPlants = $this->plantService->getPendingApprovalsList();

            foreach ($pendingPlants as $plant) {
                if (is_object($plant)) {
                    $plantId = method_exists($plant, 'getId') ? $plant->getId() : 'unknown';
                    $plantName = method_exists($plant, 'getName') ? $plant->getName() : 'Necunoscută';
                    $timestamp = method_exists($plant, 'getCreatedAt') ? $plant->getCreatedAt() : date('Y-m-d H:i:s');
                } else {
                    $plantId = $plant['id'] ?? 'unknown';
                    $plantName = $plant['name'] ?? 'Necunoscută';
                    $timestamp = $plant['created_at'] ?? date('Y-m-d H:i:s');
                }

                if (in_array($plantId, $dismissedIds, true)) {
                    continue;
                }

                $notifications[] = [
                    'id' => 'approval_' . $plantId,
                    'type' => 'SYSTEM_APPROVAL',
                    'severity' => 'INFO',
                    'title' => 'Solicitare de Aprobare',
                    'message' => 'Facilitatea nucleară "' . $plantName . '" necesită validare operațională.',
                    'date' => $timestamp,
                    'target_role' => 'ADMIN',
                    'target_email' => null
                ];
            }
        }

        usort($notifications, function($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        return $notifications;
    }

    private function getPlantEventTitle(string $type): string {
        return match ($type) {
            'PLANT_STATUS_CHANGE' => 'Schimbare Status Centrală',
            'PLANT_APPROVED' => 'Centrală Aprobată',
            'PLANT_REJECTED' => 'Centrală Respinsă',
            default => 'Eveniment Centrală'
        };
    }

    public function getAggregatedNotifications(string $userRole, string $userEmail): array {
        return array_merge(
            $this->getAlertNotifications(),
            $this->getPlantNotifications($userRole)
        );
    }
}