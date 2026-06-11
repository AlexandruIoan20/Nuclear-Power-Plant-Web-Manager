<?php

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
            header("Location: " . URL_FRONTEND . "/pages/dashboard.html", true, 302);
            exit;
        }
    }

    private function rejectIfAuthenticated(): void {
        if (AuthHelper::isAuthenticated()) {
            http_response_code(409);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'Ești deja autentificat.']);
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
                    echo json_encode(['status' => 'error', 'message' => 'Toate câmpurile sunt necesare.']);
                    return;
                }

                $_SESSION['register_error'] = 'Toate câmpurile sunt necesare.';
                return;
            }

            if ($password !== $password_confirm) {
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['status' => 'error', 'message' => 'Parolele nu se potrivesc.']);
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
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
                    echo json_encode(['status' => 'error', 'message' => 'Email și parolă sunt necesare.']);
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
                    echo json_encode(['status' => 'error', 'message' => 'Email sau parolă incorectă.']);
                    return;
                }

                $_SESSION['login_error'] = 'Email sau parolă incorectă.';
                return;
            }

            // Alocarea datelor în sesiune
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            session_regenerate_id(true);

            if ($wantsJson) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Autentificare reușită.',
                    'redirect' => URL_FRONTEND . '/pages/dashboard.html'
                ]);
                return;
            }

            $locationString = "Location: " . URL_FRONTEND . "/pages/dashboard.html"; 
            header($locationString, true, 302);
            exit;
        }

    }

    public function handleLogout(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $cookieParams = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure'],
                $cookieParams['httponly']
            );
        }

        session_destroy();
        $locationString = "Location: " . URL_FRONTEND . "/pages/start.html"; 
        header($locationString, true, 302);
        exit;
    }

    public function getUserStatus(): void {
        header('Content-Type: application/json; charset=UTF-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Utilizator neautentificat']);
            return;
        }

        $user = $this->userService->getUserById($_SESSION['user_id']);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Utilizator nu găsit']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ]);
    }
}