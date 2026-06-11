<?php

require_once __DIR__ . "/../Services/SensorService.php";

class SensorController {
    private SensorService $sensorService;

    public function __construct(SensorService $sensorService) {
        $this->sensorService = $sensorService;
    }

    public function stream(string $reactorId): void {
        set_time_limit(0);

        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
        header("Connection: keep-alive");
        header("X-Accel-Buffering: no");

        while (ob_get_level()) ob_end_clean();
        ob_implicit_flush(true);

        while(true) {
            try {
                $data = $this->sensorService->getStreamData($reactorId);
                echo "data: " . json_encode($data) . "\n\n";
            } catch (Exception $e) {
                echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
                break;
            }

            ob_flush();
            flush();

            if(connection_aborted()) break;

            sleep(3);
        }
    }

    public function getSensorsByReactor(string $reactorId): void {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $sensors = $this->sensorService->getSensorsByReactor($reactorId);
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $sensors]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function getSensor(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $sensor = $this->sensorService->getSensor($id);
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $sensor]);
            exit;
        } catch (Exception $e) {
            http_response_code(str_contains($e->getMessage(), 'găsit') ? 404 : 500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function createSensor(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents("php://input");
        $data = json_decode($jsonPayload, true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nu s-au primit date."]);
            exit;
        }

        try {
            $sensorId = $this->sensorService->createSensor($data);
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Senzorul a fost creat cu succes.",
                "sensorId" => $sensorId,
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function updateSensor(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents("php://input");
        $data = json_decode($jsonPayload, true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Date incomplete pentru actualizare."]);
            exit;
        }

        try {
            $this->sensorService->updateSensor($id, $data);
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Senzorul a fost actualizat cu succes."]);
            exit;
        } catch (Exception $e) {
            $code = str_contains($e->getMessage(), 'găsit') ? 404 : 400;
            http_response_code($code);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function deleteSensor(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $this->sensorService->deleteSensor($id);
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Senzorul a fost șters cu succes."]);
            exit;
        } catch (Exception $e) {
            $code = str_contains($e->getMessage(), 'găsit') ? 404 : 500;
            http_response_code($code);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function populateSensors(string $reactorId): void {
        header('Content-Type: application/json; charset=UTF-8');

        $jsonPayload = file_get_contents("php://input");
        $data = json_decode($jsonPayload, true);
        $reactorType = $data['reactorType'] ?? null;

        if (!$reactorType) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "reactorType este obligatoriu."]);
            exit;
        }

        try {
            $this->sensorService->populateSensorsForReactor($reactorId, $reactorType);
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Senzorii au fost generați cu succes din template."]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }
}
