<?php

class UserRepository { 
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function save (User $user): void { 
        $stmt = $this->db->prepare("INSERT INTO users (username, first_name, last_name, email, password_hash, role) VALUES (:username, :first_name, :last_name, :email, :password_hash, :role)"); 
        $stmt->execute([
            'username' => $user->getUsername(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(), 
            'password_hash' => $user->getPasswordHash(),
            'role' => $user->getRole() 
        ]); 
    }

    public function findAll(): array { 
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC"); 
        $users = []; 

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
            $users[] = new User(
                $row['username'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['password_hash'],
                $row['id'],
                $row['role']
            );
        }

        return $users; 
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT id, username, first_name, last_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findById(string $id): ?array {
        $stmt = $this->db->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT id, username, email, password_hash, role FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findAllForAdmin(): array {
        $stmt = $this->db->query("SELECT id, username, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRole(string $id, string $role): void {
        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute(['role' => $role, 'id' => $id]);
    }

    public function delete(string $id): void {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function countByRole(string $role): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }
}