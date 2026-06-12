<?php

class LogRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function save(Log $log): void {
        $stmt = $this->db->prepare(
            "INSERT INTO logs (level, message, context, user_id, plant_id, reactor_id, source, request_uri, ip_address)
             VALUES (:level, :message, :context::jsonb, :user_id, :plant_id, :reactor_id, :source, :request_uri, :ip_address)"
        );
        $stmt->execute([
            'level' => $log->getLevel(),
            'message' => $log->getMessage(),
            'context' => $log->getContext() ? json_encode($log->getContext()) : null,
            'user_id' => $log->getUserId(),
            'plant_id' => $log->getPlantId(),
            'reactor_id' => $log->getReactorId(),
            'source' => $log->getSource(),
            'request_uri' => $log->getRequestUri(),
            'ip_address' => $log->getIpAddress(),
        ]);
    }

    public function findRecent(int $limit = 100, ?string $level = null, int $offset = 0): array {
        $sql = "SELECT id, level, message, context, user_id, plant_id, reactor_id, source, request_uri, ip_address, created_at
                FROM logs";
        $params = [];

        if ($level !== null) {
            $sql .= " WHERE level = :level";
            $params['level'] = $level;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = new Log(
                $row['level'],
                $row['message'],
                $row['context'] ? json_decode($row['context'], true) : null,
                $row['user_id'],
                $row['plant_id'],
                $row['reactor_id'],
                $row['source'],
                $row['request_uri'],
                $row['ip_address'],
                $row['id'],
                $row['created_at']
            );
        }
        return $logs;
    }

    public function countByLevel(?string $level = null): int {
        if ($level !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM logs WHERE level = :level");
            $stmt->execute(['level' => $level]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM logs");
        }
        return (int)$stmt->fetchColumn();
    }

    public function purgeOlderThan(int $days): int {
        $stmt = $this->db->prepare(
            "DELETE FROM logs WHERE created_at < NOW() - INTERVAL '1 day' * :days"
        );
        $stmt->execute(['days' => $days]);
        return $stmt->rowCount();
    }

    public function findAfter(string $afterId, int $limit = 50): array {
        $stmt = $this->db->prepare(
            "SELECT id, level, message, context, user_id, plant_id, reactor_id, source, request_uri, ip_address, created_at
             FROM logs
             WHERE created_at > (SELECT created_at FROM logs WHERE id = :after_id)
             ORDER BY created_at ASC
             LIMIT :lim"
        );
        $stmt->execute(['after_id' => $afterId, 'lim' => $limit]);

        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = new Log(
                $row['level'],
                $row['message'],
                $row['context'] ? json_decode($row['context'], true) : null,
                $row['user_id'],
                $row['plant_id'],
                $row['reactor_id'],
                $row['source'],
                $row['request_uri'],
                $row['ip_address'],
                $row['id'],
                $row['created_at']
            );
        }
        return $logs;
    }
}
