<?php

class UserController { 
    private UserService $userService; 

    public function __construct(UserService $userService) { 
        $this->userService = $userService; 
    }

    public function showStart(): void { 
        require __DIR__ . '/../Views/start.view.php'; 
    }

    public function showRegisterForm(): void { 
        require __DIR__ . '/../Views/register.view.php'; 
    }

    public function handleRegister(): void { 
        try { 
            $this->userService->registerUser($_POST); 

            header("Location: /users"); 
            exit; 
        } catch(Exception $e) { 
            echo "Error at register: " . htmlspecialchars($e->getMessage()); 
        }
    }

    public function listUsers(): void {
        $users = $this->userService->getAllUsers(); 
        require __DIR__ . '/../Views/users.view.php'; 
    }
}