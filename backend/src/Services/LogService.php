<?php

require_once __DIR__ . '/../Repositories/LogRepository.php';
require_once __DIR__ . '/../Entities/Log.php';

class LogService {
    private static ?LogService $instance = null;
    private LogRepository $repository;
    private string $lastCleanupFile;

    private function __construct(LogRepository $repository) {
        $this->repository = $repository;
        $this->lastCleanupFile = sys_get_temp_dir() . '/npp_log_cleanup';
    }

    public static function init(PDO $pdo): void {
        if (self::$instance === null) {
            self::$instance = new self(new LogRepository($pdo));
        }
    }

    public static function instance(): self {
        if (self::$instance === null) {
            throw new RuntimeException('LogService nu a fost initializat. Trebuie apelat LogService::init($pdo) prima data.');
        }
        return self::$instance;
    }

    private function log(string $level, string $message, ?array $context = null, ?string $plantId = null, ?string $reactorId = null): void {
        $userId = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        $log = new Log(
            $level,
            $message,
            $context,
            $userId,
            $plantId,
            $reactorId,
            'backend',
            $requestUri,
            $ipAddress
        );

        try {
            $this->repository->save($log);
        } catch (PDOException $e) {
            error_log("[LogService] Eroare la salvarea logului: " . $e->getMessage());
        }

        $timestamp = date('Y-m-d H:i:s');
        $userTag = $userId ? "[user:{$userId}]" : '';
        $formatted = "[{$timestamp}] [{$level}] {$userTag} {$message}";

        if ($context !== null) {
            $formatted .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        error_log($formatted);

        $this->maybeCleanup();
    }

    public function debug(string $message, ?array $context = null, ?string $plantId = null, ?string $reactorId = null): void {
        $this->log('DEBUG', $message, $context, $plantId, $reactorId);
    }

    public function info(string $message, ?array $context = null, ?string $plantId = null, ?string $reactorId = null): void {
        $this->log('INFO', $message, $context, $plantId, $reactorId);
    }

    public function warning(string $message, ?array $context = null, ?string $plantId = null, ?string $reactorId = null): void {
        $this->log('WARNING', $message, $context, $plantId, $reactorId);
    }

    public function error(string $message, ?array $context = null, ?string $plantId = null, ?string $reactorId = null): void {
        $this->log('ERROR', $message, $context, $plantId, $reactorId);
    }

    public function critical(string $message, ?array $context = null, ?string $plantId = null, ?string $reactorId = null): void {
        $this->log('CRITICAL', $message, $context, $plantId, $reactorId);
    }

    public function logFromFrontend(string $level, string $message, ?array $context = null, ?string $userId = null): void {
        $log = new Log(
            $level,
            $message,
            $context,
            $userId,
            null,
            null,
            'frontend',
            null,
            null
        );

        $this->repository->save($log);

        $timestamp = date('Y-m-d H:i:s');
        $userTag = $userId ? "[user:{$userId}]" : '';
        $formatted = "[{$timestamp}] [{$level}] [frontend] {$userTag} {$message}";

        if ($context !== null) {
            $formatted .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        error_log($formatted);
    }

    public function getRepository(): LogRepository {
        return $this->repository;
    }

    private function maybeCleanup(): void {
        $lastCleanup = 0;
        if (file_exists($this->lastCleanupFile)) {
            $lastCleanup = (int)file_get_contents($this->lastCleanupFile);
        }

        if (time() - $lastCleanup > 3600) {
            try {
                $deleted = $this->repository->purgeOlderThan(30);
                if ($deleted > 0) {
                    error_log("[LogService] Curatare automata: {$deleted} loguri mai vechi de 30 de zile au fost sterse.");
                }
                file_put_contents($this->lastCleanupFile, (string)time());
            } catch (Exception $e) {
                error_log("[LogService] Eroare la curatarea automata: " . $e->getMessage());
            }
        }
    }
}
