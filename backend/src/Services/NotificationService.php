<?php

require_once __DIR__ . '/PlantServiceFacade.php';
require_once __DIR__ . '/AlertService.php';
require_once __DIR__ . '/../Entities/Alert.php';
require_once __DIR__ . '/../Dto/NotificationDTO.php';

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
            $notifications[] = NotificationDTO::fromAlert(
                alertId: $alert->getId(),
                alertType: $alert->getType(),
                alertMessage: $alert->getMessage(),
                alertDate: $alert->getCreatedAt(),
            );
        }

    
        if ($userRole === 'ADMIN') {
           
            $pendingPlants = $this->plantService->getPendingApprovalsList();
            
            foreach ($pendingPlants as $plant) {
                $plantId = $plant['id'] ?? 'unknown';
                $plantName = $plant['name'] ?? 'Necunoscută';
                $timestamp = $plant['created_at'] ?? date('Y-m-d H:i:s');
                
                $notifications[] = NotificationDTO::fromApprovalPlant(
                    plantId: $plantId,
                    plantName: $plantName,
                    createdAt: $timestamp,
                );
            }
        }

      
        usort($notifications, function(NotificationDTO $a, NotificationDTO $b) {
            return strtotime($b->date) <=> strtotime($a->date);
        });

        return $notifications;
    }
}