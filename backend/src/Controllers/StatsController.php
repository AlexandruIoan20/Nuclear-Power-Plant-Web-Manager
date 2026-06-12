<?php

require_once __DIR__ . '/../Services/StatsService.php';

class StatsController {
    private StatsService $service;

    public function __construct(StatsService $service) {
        $this->service = $service;
    }

    public function getAll(): void {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->service->getAll());
    }

    public function getMeasurements(): void {
        $reactorId = $_GET['reactorId'] ?? null;
        $hours = isset($_GET['hours']) ? max(1, min(168, (int)$_GET['hours'])) : 24;

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->service->getMeasurements($reactorId, $hours));
    }
}
