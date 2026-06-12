<?php

class UserService { 
    private UserRepository $userRepository; 

    public function __construct(UserRepository $userRepository) { 
        $this->userRepository = $userRepository; 
    }

    public function registerUser(array $data): void { 
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || $password === '') {
            throw new Exception('Toate câmpurile sunt necesare!');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Adresa de email nu este validă!');
        }

        if (strlen($firstName) > 50) {
            throw new Exception('Prenumele este prea lung!');
        }

        if (strlen($lastName) > 50) {
            throw new Exception('Numele este prea lung!');
        }

        if (strlen($username) > 30) {
            throw new Exception('Numele de utilizator este prea lung!');
        }

        if (strlen($password) < 6) {
            throw new Exception('Parola trebuie să aibă cel puțin 6 caractere!');
        }

        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new Exception('Email-ul este deja înregistrat!');
        }

        $existingUsername = $this->userRepository->findByUsername($username);
        if ($existingUsername) {
            throw new Exception('Numele de utilizator este deja luat!');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); 

        $role = str_ends_with($email, '@admin.ro') ? 'ADMIN' : 'OPERATOR';

        $user = new User($username, $firstName, $lastName, $email, $hashedPassword, null, $role); 
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

    public function getAllUsersForAdmin(): array {
        return $this->userRepository->findAllForAdmin();
    }

    public function updateUserRole(string $id, string $role): void {
        $validRoles = ['ADMIN', 'ENGINEER', 'OPERATOR'];

        if (!in_array($role, $validRoles, true)) {
            throw new Exception('Rolul specificat nu este valid. Valorile acceptate: ' . implode(', ', $validRoles));
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new Exception('Utilizatorul nu a fost găsit.');
        }

        if ($user['id'] === $_SESSION['user_id']) {
            throw new Exception('Nu îți poți schimba propriul rol.');
        }

        if ($user['role'] === 'ADMIN' && $role !== 'ADMIN') {
            $adminCount = $this->userRepository->countByRole('ADMIN');
            if ($adminCount <= 1) {
                throw new Exception('Nu poți schimba rolul ultimului administrator.');
            }
        }

        $this->userRepository->updateRole($id, $role);
    }

    public function deleteUser(string $id): void {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new Exception('Utilizatorul nu a fost găsit.');
        }

        if ($user['id'] === $_SESSION['user_id']) {
            throw new Exception('Nu îți poți șterge propriul cont.');
        }

        if ($user['role'] === 'ADMIN') {
            $adminCount = $this->userRepository->countByRole('ADMIN');
            if ($adminCount <= 1) {
                throw new Exception('Nu poți șterge ultimul administrator.');
            }
        }

        $this->userRepository->delete($id);
    }
}