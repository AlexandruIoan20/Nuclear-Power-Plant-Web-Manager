<?php

class UserController { 
    private UserService $userService; 

    public function __construct(UserService $userService) { 
        $this->userService = $userService; 
    }

    public function showLoginForm(): void { 
        require __DIR__ . '/../Views/login.view.php'; 
    }

    public function showStart(): void { 
        require __DIR__ . '/../Views/start.view.php'; 
    }

    public function showRegisterForm(): void { 
        require __DIR__ . '/../Views/register.view.php'; 
    }

    public function handleRegister(): void {
        $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['status' => 'error', 'message' => 'Toate câmpurile sunt necesare.']);
                    return;
                }

                $_SESSION['register_error'] = 'Toate câmpurile sunt necesare.';
                require __DIR__ . '/../Views/register.view.php';
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
                require __DIR__ . '/../Views/register.view.php';
                return;
            }

            try {
                $this->userService->registerUser([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password
                ]);

                if ($wantsJson) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Cont creat cu succes! Poți să te conectezi acum.',
                        'redirect' => 'http://localhost:5500/login.html'
                    ]);
                    return;
                }

                $_SESSION['register_success'] = 'Cont creat cu succes! Poți să te conectezi acum.';
                header('Location: http://localhost:5500/login.html', true, 302);
                exit;
            } catch (Exception $e) {
                if ($wantsJson) {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                    return;
                }

                $_SESSION['register_error'] = $e->getMessage();
                require __DIR__ . '/../Views/register.view.php';
                return;
            }
        }

        require __DIR__ . '/../Views/register.view.php';
    }

    public function listUsers(): void {
        $users = $this->userService->getAllUsers(); 
        require __DIR__ . '/../Views/users.view.php'; 
    }

    public function handleLogin(): void {
        $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                require __DIR__ . '/../Views/login.view.php';
                return;
            }

            $user = $this->userService->authenticateUser($email, $password);

            if (!$user) {
                if ($wantsJson) {
                    http_response_code(401);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['status' => 'error', 'message' => 'Email sau parolă incorectă.']);
                    return;
                }

                $_SESSION['login_error'] = 'Email sau parolă incorectă.';
                require __DIR__ . '/../Views/login.view.php';
                return;
            }

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
                    'redirect' => 'http://localhost:5500/dashboard.html'
                ]);
                return;
            }

            header('Location: http://localhost:5500/dashboard.html', true, 302);
            exit;
        }

        require __DIR__ . '/../Views/login.view.php';
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
        header('Location: http://localhost:5500/start.html', true, 302);
        exit;
    }

    public function showDashboard(): void {
        AuthHelper::requireLogin();
        require __DIR__ . '/../Views/dashboard.view.php';
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
