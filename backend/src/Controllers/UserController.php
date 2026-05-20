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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
                $_SESSION['register_error'] = 'Toate câmpurile sunt necesare.';
                require __DIR__ . '/../Views/register.view.php';
                return;
            }

            if ($password !== $password_confirm) {
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

                $_SESSION['register_success'] = 'Cont creat cu succes! Poți să te conectezi acum.';
                header('Location: http://127.0.0.1:8081/login', true, 302);
                exit;
            } catch (Exception $e) {
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['login_error'] = 'Email și parolă sunt necesare.';
                require __DIR__ . '/../Views/login.view.php';
                return;
            }

            $user = $this->userService->authenticateUser($email, $password);

            if (!$user) {
                $_SESSION['login_error'] = 'Email sau parolă incorectă.';
                require __DIR__ . '/../Views/login.view.php';
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            header('Location: http://127.0.0.1:8081/start', true, 302);
            exit;
        }

        require __DIR__ . '/../Views/login.view.php';
    }

    public function handleLogout(): void {
        session_destroy();
        header('Location: http://127.0.0.1:8081/start', true, 302);
        exit;
    }

    public function showDashboard(): void {
        AuthHelper::requireLogin();
        require __DIR__ . '/../Views/dashboard.view.php';
    }

    public function getUserStatus(): void {
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
