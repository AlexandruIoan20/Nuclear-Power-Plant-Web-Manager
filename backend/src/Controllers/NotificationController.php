<?php

require_once __DIR__ . '/../Services/NotificationService.php';
require_once __DIR__ . '/../Services/LogService.php';

class NotificationController {
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

  
    public function getNotifications(): void {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $userId = $_SESSION['user_id'] ?? null;
            $userRole = $_SESSION['user_role'] ?? 'OPERATOR';
            $userEmail = $_SESSION['user_email'] ?? '';

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Neautorizat']);
                exit;
            }

            $notifications = $this->notificationService->getAggregatedNotifications($userRole, $userEmail);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success', 
                'data' => $notifications
            ]);
        } catch (\Throwable $e) {
         
            LogService::instance()->error($e->getMessage() . " în " . $e->getFile() . ":" . $e->getLine());
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Eroare internă: ' . $e->getMessage()
            ]);
        }
    }
}