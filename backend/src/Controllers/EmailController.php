<?php

require_once __DIR__ . '/../Services/EmailService.php';
require_once __DIR__ . '/../Services/LogService.php';

class EmailController {

    public function __construct(
        private EmailService $emailService
    ) {}

    public function handleSendEmail() {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents('php://input');
        $formData = json_decode($jsonPayload, true);

        if ($formData === null) {
            $formData = [];
        }

        LogService::instance()->debug("Form Data decoded from JSON (Email Request):");
        LogService::instance()->debug(print_r($formData, true));

        if (empty($formData['to_email']) || empty($formData['message'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Incomplete data for sending email. Fields to_email and message are required.'
            ]);
            exit;
        }

        try {
            $this->emailService->sendAlert($formData);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'The email notification has been successfully dispatched.'
            ]);
            exit;

        } catch (Exception $e) {
            LogService::instance()->error("POST Send Email: " . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error processing email: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}