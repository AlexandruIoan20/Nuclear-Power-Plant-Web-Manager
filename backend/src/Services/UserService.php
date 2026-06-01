<?php

class UserService { 
    private UserRepository $userRepository; 

    public function __construct(UserRepository $userRepository) { 
        $this->userRepository = $userRepository; 
    }

    public function registerUser(array $data): void { 
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            throw new Exception('Toate câmpurile sunt necesare!');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Adresa de email nu este validă!');
        }

        if (strlen($name) > 30) {
            throw new Exception('Numele de utilizator este prea lung!');
        }

        if (strlen($password) < 6) {
            throw new Exception('Parola trebuie să aibă cel puțin 6 caractere!');
        }

        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new Exception('Email-ul este deja înregistrat!');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); 

        $user = new User($name, $email, $hashedPassword); 
        $this->userRepository->save($user); 
    }

    public function getAllUsers(): array { 
        return $this->userRepository->findAll(); 
    }

    public function authenticateUser(string $email, string $password): ?array {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    public function getUserById(string $id): ?array {
        return $this->userRepository->findById($id);
    }

}