<?php

class UserService { 
    private UserRepository $userRepository; 

    public function __construct(UserRepository $userRepository) { 
        $this->userRepository = $userRepository; 
    }

    public function registerUser(array $data): void { 
        if(empty($data['name']) || empty($data['email']) || empty($data['password'])) { 
            throw new Exception('All fields are required!');
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