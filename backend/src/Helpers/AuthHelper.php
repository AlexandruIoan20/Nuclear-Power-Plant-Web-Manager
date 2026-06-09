<?php

require_once __DIR__ . '/../Constants/urls.php';

class AuthHelper {
    
    public static function isAuthenticated(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function getCurrentUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    public static function getCurrentUserId(): ?string {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getCurrentUsername(): ?string {
        return $_SESSION['username'] ?? null;
    }

    public static function requireLogin(): void {
        if (!self::isAuthenticated()) {
            header("Location: " . URL_BACKEND . "/login");
            exit;
        }
    }

    public static function requireRole(string $requiredRole): void {
        self::requireLogin();
        
        if ($_SESSION['user_role'] !== $requiredRole) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Acces interzis: rolul utilizatorului nu corespunde'
            ]);
            exit;
        }
    }

    public static function requireAnyRole(array $allowedRoles): void {
        self::requireLogin();
        
        if (!in_array($_SESSION['user_role'], $allowedRoles)) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Acces interzis: rolul utilizatorului nu este autorizat'
            ]);
            exit;
        }
    }

    public static function logout(): void {
        session_destroy();
    }
}
