<?php

require_once __DIR__ . '/LogService.php';

class UserService { 
    private UserRepository $userRepository; 

    public function __construct(UserRepository $userRepository) { 
        $this->userRepository = $userRepository; 
    }

    public function registerUser(array $data): void { 
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        LogService::instance()->info("Înregistrare utilizator", ['username' => $username, 'email' => $email]);

        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
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
            LogService::instance()->warning("Încercare înregistrare cu email duplicat", ['email' => $email]);
            throw new Exception('Email-ul este deja înregistrat!');
        }

        $existingUsername = $this->userRepository->findByUsername($username);
        if ($existingUsername) {
            LogService::instance()->warning("Încercare înregistrare cu username duplicat", ['username' => $username]);
            throw new Exception('Numele de utilizator este deja luat!');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); 

        $role = str_ends_with($email, '@admin.ro') ? 'ADMIN' : 'OPERATOR';

        $user = new User($username, $firstName, $lastName, $email, $hashedPassword, null, $role); 
        $this->userRepository->save($user);

        LogService::instance()->info("Utilizator înregistrat cu succes", ['username' => $username, 'email' => $email, 'role' => $role]);
    }

    public function getAllUsers(): array { 
        return $this->userRepository->findAll(); 
    }

    public function authenticateUser(string $email, string $password): ?array {
        LogService::instance()->info("Încercare autentificare", ['email' => $email]);

        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            LogService::instance()->warning("Autentificare eșuată - utilizator negăsit", ['email' => $email]);
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            LogService::instance()->warning("Autentificare eșuată - parolă incorectă", ['email' => $email]);
            return null;
        }

        LogService::instance()->info("Autentificare reușită", ['email' => $email]);
        return $user;
    }

    public function getUserById(string $id): ?array {
        LogService::instance()->debug("Obținere utilizator după ID", ['user_id' => $id]);
        return $this->userRepository->findById($id);
    }

}