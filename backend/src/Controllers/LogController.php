<?php

require_once __DIR__ . '/../Services/LogService.php';
require_once __DIR__ . '/../Dto/LogListDTO.php';
require_once __DIR__ . '/../Dto/ApiResponseDTO.php';

class LogController {
    public function getLogs(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $level = $_GET['level'] ?? null;
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        $offset = max((int)($_GET['offset'] ?? 0), 0);

        $validLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
        if ($level !== null && !in_array(strtoupper($level), $validLevels)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Nivel de log invalid.'));
            return;
        }

        try {
            $logs = LogService::instance()->getRepository()->findRecent($limit, $level ? strtoupper($level) : null, $offset);
            $total = LogService::instance()->getRepository()->countByLevel($level ? strtoupper($level) : null);

            $data = array_map(fn(Log $log) => LogListDTO::fromEntity($log), $logs);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la citirea logurilor: ' . $e->getMessage()));
        }
    }

    public function receiveFrontendLog(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents('php://input');
        $data = json_decode($jsonPayload, true);

        if (empty($data) || empty($data['level']) || empty($data['message'])) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Date incomplete pentru log.'));
            return;
        }

        $validLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
        $level = strtoupper($data['level']);
        if (!in_array($level, $validLevels)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Nivel de log invalid.'));
            return;
        }

        try {
            $userId = $data['user_id'] ?? null;
            LogService::instance()->logFromFrontend(
                $level,
                $data['message'],
                $data['context'] ?? null,
                $userId
            );

            http_response_code(201);
            echo json_encode(new ApiResponseDTO(status: 'success'));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la salvarea logului: ' . $e->getMessage()));
        }
    }
}
