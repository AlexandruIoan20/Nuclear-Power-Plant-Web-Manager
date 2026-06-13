<?php

require_once __DIR__ . '/../Constants/urls.php';
require_once __DIR__ . '/../Dto/UserDTO.php';
require_once __DIR__ . '/../Dto/ApiResponseDTO.php';
require_once __DIR__ . '/../Services/LogService.php';

require_once __DIR__ . '/../Constants/urls.php'; 

class UserController { 
    private UserService $userService; 

    public function __construct(UserService $userService) { 
        $this->userService = $userService; 
    }

    private function wantsJson(): bool {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
    }

    private function redirectIfAuthenticated(): void {
        if (AuthHelper::isAuthenticated()) {
            header("Location: " . URL_FRONTEND . "/pages/map.html", true, 302);
            exit;
        }
    }

    private function rejectIfAuthenticated(): void {
        if (AuthHelper::isAuthenticated()) {
            http_response_code(409);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Ești deja autentificat.'));
            exit;
        }
    }

    public function handleRegister(): void {
        $wantsJson = $this->wantsJson();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->redirectIfAuthenticated();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$wantsJson) {
                $this->redirectIfAuthenticated();
            } else {
                $this->rejectIfAuthenticated();
            }
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(new ApiResponseDTO(status: 'error', message: 'Toate câmpurile sunt necesare.'));
                    return;
                }

                $_SESSION['register_error'] = 'Toate câmpurile sunt necesare.';
                return;
            }

            if ($password !== $password_confirm) {
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(new ApiResponseDTO(status: 'error', message: 'Parolele nu se potrivesc.'));
                    return;
                }

                $_SESSION['register_error'] = 'Parolele nu se potrivesc.';
                return;
            }

            try {
                $this->userService->registerUser([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password
                ]);

                if ($wantsJson) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Cont creat cu succes! Poți să te conectezi acum.',
                        'redirect' => URL_FRONTEND . "/pages/login.html"
                    ]);
                    return;
                }

                $_SESSION['register_success'] = 'Cont creat cu succes! Poți să te conectezi acum.';
                $locationString = "Location: " . URL_FRONTEND . "/pages/login.html"; 
                header($locationString, true, 302);
                exit;
            } catch (Throwable $e) {
                LogService::instance()->error("[REGISTER ERROR] " . $e->getMessage());
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage()));
                    return;
                }

                $_SESSION['register_error'] = $e->getMessage();
                return;
            }
        }

    }

    public function handleLogin(): void {
        $wantsJson = $this->wantsJson();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->redirectIfAuthenticated();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$wantsJson) {
                $this->redirectIfAuthenticated();
            } else {
                $this->rejectIfAuthenticated();
            }
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(new ApiResponseDTO(status: 'error', message: 'Email și parolă sunt necesare.'));
                    return;
                }

                $_SESSION['login_error'] = 'Email și parolă sunt necesare.';
                return;
            }

            try {
                $user = $this->userService->authenticateUser($email, $password);
            } catch (Throwable $e) {
                $user = null;
            }

            if (!$user) {
                if ($wantsJson) {
                    http_response_code(401);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(new ApiResponseDTO(status: 'error', message: 'Email sau parolă incorectă.'));
                    return;
                }

                $_SESSION['login_error'] = 'Email sau parolă incorectă.';
                return;
            }

            // Alocarea datelor în sesiune
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['username'] = $user->username;

            session_regenerate_id(true);

            if ($wantsJson) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Autentificare reușită.',
                    'redirect' => URL_FRONTEND . '/pages/map.html'
                ]);
                return;
            }

            $locationString = "Location: " . URL_FRONTEND . "/pages/map.html"; 
            header($locationString, true, 302);
            exit;
        }

    }

    public function handleLogout(): void {
        $_SESSION = [];
    
        if (ini_get("session.use_cookies")) {
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'None'
            ]);
        }
    
        session_destroy();
    
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Delogare reușită']);
        exit;
    }

    public function getUserStatus(): void {
        header('Content-Type: application/json; charset=UTF-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Utilizator neautentificat'));
            return;
        }

        $user = $this->userService->getUserById($_SESSION['user_id']);

        if (!$user) {
            http_response_code(404);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Utilizator nu găsit'));
            return;
        }

        echo json_encode(new ApiResponseDTO(status: 'success', data: $user));
    }

    public function adminListUsers(): void {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $users = $this->userService->getAllUsersForAdmin();

            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', data: $users));
        } catch (Exception $e) {
            LogService::instance()->error("[ADMIN LIST USERS ERROR] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la încărcarea utilizatorilor.'));
        }
    }

    public function adminGetUser(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanId = trim((string)$id);
        if (empty($cleanId)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'ID-ul utilizatorului lipsește.'));
            return;
        }

        try {
            $user = $this->userService->getUserById($cleanId);

            if (!$user) {
                http_response_code(404);
                echo json_encode(new ApiResponseDTO(status: 'error', message: 'Utilizatorul nu a fost găsit.'));
                return;
            }

            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', data: $user));
        } catch (Exception $e) {
            LogService::instance()->error("[ADMIN GET USER ERROR] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Eroare la încărcarea utilizatorului.'));
        }
    }

    public function adminUpdateRole(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanId = trim((string)$id);
        if (empty($cleanId)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'ID-ul utilizatorului lipsește.'));
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $role = $input['role'] ?? null;

        if (empty($role)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'Rolul este necesar.'));
            return;
        }

        try {
            $this->userService->updateUserRole($cleanId, strtoupper($role));

            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Rolul utilizatorului a fost actualizat cu succes.'));
        } catch (Exception $e) {
            LogService::instance()->error("[ADMIN UPDATE ROLE ERROR] " . $e->getMessage());
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage()));
        }
    }

    public function adminDeleteUser(string $id): void {
        header('Content-Type: application/json; charset=UTF-8');

        $cleanId = trim((string)$id);
        if (empty($cleanId)) {
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: 'ID-ul utilizatorului lipsește.'));
            return;
        }

        try {
            $this->userService->deleteUser($cleanId);

            http_response_code(200);
            echo json_encode(new ApiResponseDTO(status: 'success', message: 'Utilizatorul a fost șters cu succes.'));
        } catch (Exception $e) {
            LogService::instance()->error("[ADMIN DELETE USER ERROR] " . $e->getMessage());
            http_response_code(400);
            echo json_encode(new ApiResponseDTO(status: 'error', message: $e->getMessage()));
        }
    }
}