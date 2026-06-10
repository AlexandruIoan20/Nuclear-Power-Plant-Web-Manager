<?php 

class FeasibilityRepository { 
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function saveReport(string $powerPlantId, array $reportResult): bool { 
        $status = $reportResult['status']; 
        $score = $reportResult['nsvi_score'] ?? null; 
        $deficienciesJson = isset($reportResult['deficiencies']) ? json_encode($reportResult['deficiencies']) : json_encode([]); 
        $errorsJson = isset($reportResult['errors']) ? json_encode($reportResult['errors']) : json_encode([]); 

        try { 
            $statement = $this->db->prepare(
                "INSERT INTO feasibility_reports(power_plant_id, status, nsvi_score, deficiencies, errors) 
                 VALUES (:plant_id, :status, :score, :deficiencies, :errors)"
            ); 

            $statement->execute([ 
                ':plant_id' => $powerPlantId, 
                ':status' => $status, 
                ':score' => $score, 
                ':deficiencies' => $deficienciesJson,
                ':errors' => $errorsJson
            ]); 

            return true;
            
        } catch(PDOException $e) { 
            LogService::instance()->error("[FeasibilityRepository] Eroare la salvarea raportului: " . $e->getMessage());
            return false;
        }
    }

    public function getLatestReportByPlantId(string $powerPlantId): ?array { 
        try { 
            $statement = $this->db->prepare(
                "SELECT id as report_id, status, nsvi_score, deficiencies, errors, created_at 
                 FROM feasibility_reports 
                 WHERE power_plant_id = :plant_id
                 ORDER BY created_at DESC LIMIT 1"
            ); 

            $statement->execute([
                ':plant_id' => $powerPlantId
            ]); 
            
            $report = $statement->fetch(PDO::FETCH_ASSOC); 
            
            if (!$report) { 
                return null; 
            }

            $report['deficiencies'] = json_decode($report['deficiencies'], true);  
            $report['errors'] = json_decode($report['errors'], true);  
            $report['nsvi_score'] = $report['nsvi_score'] !== null ? (float) $report['nsvi_score'] : null; 

            return $report;
            
        } catch(PDOException $e) { 
            LogService::instance()->error("[FeasibilityRepository] Eroare la citirea raportului: " . $e->getMessage());
            throw new Exception("Eroare interna la baza de date.");
        }
    }
}