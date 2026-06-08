<?php

require_once __DIR__ . '/PlantServiceFacade.php';
require_once __DIR__ . '/AlertService.php';
require_once __DIR__ . '/../Entities/Alert.php';

class NotificationService {
    private PlantServiceFacade $plantService;
    private AlertService $alertService;

    public function __construct(PlantServiceFacade $plantService, AlertService $alertService) {
        $this->plantService = $plantService;
        $this->alertService = $alertService;
    }

  
    public function getAggregatedNotifications(string $userRole, string $userEmail): array {
        $notifications = [];

     
        $alerts = $this->alertService->getActivePopups(); 
        
        foreach ($alerts as $alert) {
            $notifications[] = [
                'id' => 'alert_' . $alert->getId(),
                'type' => 'SENSOR_ALERT',
                'severity' => $alert->getType(),
                'title' => 'Avertizare Senzor: ' . $alert->getType(),
                'message' => $alert->getMessage(),
                'date' => $alert->getCreatedAt() ?: date('Y-m-d H:i:s'),
                'target_role' => 'ALL',    
                'target_email' => null     
            ];
        }

    
        if ($userRole === 'ADMIN') {
           
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
}