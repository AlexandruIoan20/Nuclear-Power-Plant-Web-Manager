<?php

class UserService { 
    private UserRepository $userRepository; 

    public function __construct(UserRepository $userRepository) { 
        $this->userRepository = $userRepository; 
    }

    public function registerUser(array $data): void { 
        if(empty($data['name']) || empty($data['email']) || empty($data['password'])) { 
            throw new Exception('Toate câmpurile sunt necesare!');
        }

        if (strlen($data['password']) < 6) {
            throw new Exception('Parola trebuie să aibă cel puțin 6 caractere!');
        }

        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser) {
            throw new Exception('Email-ul este deja înregistrat!');
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT); 

        $user = new User($data['name'], $data['email'], $hashedPassword); 
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

    public function getUserById($id): ?array {
        return $this->userRepository->findById($id);
    }

}