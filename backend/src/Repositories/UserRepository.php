<?php

class UserRepository { 
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function save (User $user): void { 
        $stmt = $this->db->prepare("INSERT INTO users (username, first_name, last_name, email, password_hash, role) VALUES (:username, :first_name, :last_name, :email, :password_hash, :role)"); 
        $stmt->execute([
            'username' => $user->getName(),
            'first_name' => $user->getName(),
            'last_name' => 'User',
            'email' => $user->getEmail(), 
            'password_hash' => $user->getPasswordHash(),
            'role' => $user->getRole() 
        ]); 
    }

    public function findAll(): array { 
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC"); 
        $users = []; 

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
            
            $users[] = new User($row['username'], $row['email'], $row['password_hash'], $row['id'], $row['role']); 
        }

        return $users; 
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findById($id): ?array {
        $stmt = $this->db->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}