<?php

class UserService { 
    private UserRepository $userRepository; 

    public function __construct(UserRepository $userRepository) { 
        $this->userRepository = $userRepository; 
    }

    public function registerUser(array $data): void { 
        if(empty($data['name']) || empty($data['email']) || empty($data['pasword'])) { 
            throw new Exception('All fields are required!');
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT); 

        $user = new User($data['name'], $data['email'], $hashedPassword); 
        $this->userRepository->save($user); 
    }

    public function getAllUsers(): array { 
        return $this->userRepository->findAll(); 
    }
}