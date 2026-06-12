<?php

require_once __DIR__ . '/../../Services/PlantService/PlantExportImportService.php';

class ImportExportController {
    private PlantExportImportService $service;

    public function __construct(PlantExportImportService $service) {
        $this->service = $service;
    }

    // GET /api/power-plants/{id}/export — Export single plant as JSON
    public function exportSingle(string $id): void {
        try {
            $data = $this->service->exportPlantJson($id);
            $this->sendJson($data, "plant_{$id}.json");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // GET /api/power-plants/export — Export multiple/all plants as JSON
    public function exportMultiple(): void {
        try {
            $ids = $this->getQueryIds();
            $data = $this->service->exportPlantsJson($ids);
            $this->sendJson($data, 'plants_export.json');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // GET /api/power-plants/{id}/export/csv — Export single plant as CSV ZIP
    public function exportSingleCsv(string $id): void {
        try {
            $content = $this->service->exportPlantCsv($id);
            $this->sendZip($content, "plant_{$id}.zip");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // GET /api/power-plants/export/csv — Export multiple/all plants as CSV ZIP
    public function exportMultipleCsv(): void {
        try {
            $ids = $this->getQueryIds();
            $content = $this->service->exportPlantsCsv($ids);
            $this->sendZip($content, 'plants_export.zip');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // POST /api/power-plants/import — Import single plant from JSON body
    public function importSingle(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty($input)) {
                throw new Exception('Invalid or empty JSON payload');
            }

            // Support both wrapped format and direct plant object
            $plantData = $input;
            if (isset($input['plants']) && is_array($input['plants'])) {
                if (count($input['plants']) !== 1) {
                    throw new Exception('Use /import/batch for multiple plants');
                }
                $plantData = $input['plants'][0];
            }

            $newId = $this->service->importPlant($plantData);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => 'Plant imported successfully',
                'plant_id' => $newId,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // POST /api/power-plants/import/batch — Import multiple plants from JSON array
    public function importMultiple(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Invalid or empty JSON payload');
            }

            $plantsData = $input;
            if (isset($input['plants']) && is_array($input['plants'])) {
                $plantsData = $input['plants'];
            }

            if (!is_array($plantsData) || empty($plantsData)) {
                throw new Exception('Expected a non-empty array of plants');
            }

            $newIds = $this->service->importPlants($plantsData);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => count($newIds) . ' plant(s) imported successfully',
                'plant_ids' => $newIds,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ==================== HELPERS ====================

    private function sendJson(array $data, string $filename): void {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function sendZip(string $content, string $filename): void {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
    }

    private function getQueryIds(): ?array {
        if (isset($_GET['ids']) && !empty($_GET['ids'])) {
            return array_map('trim', explode(',', $_GET['ids']));
        }
        return null;
    }

    private function error(string $message): void {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
}
