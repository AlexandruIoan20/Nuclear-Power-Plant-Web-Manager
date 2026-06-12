<?php

require_once __DIR__ . '/../Entities/User.php';

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
            $users[] = $this->mapRowToEntity($row);
        }

        return $users; 
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare("SELECT id, username, first_name, last_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findById(string $id): ?User {
        $stmt = $this->db->prepare("SELECT id, username, first_name, last_name, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findByUsername(string $username): ?User {
        $stmt = $this->db->prepare("SELECT id, username, email, password_hash, role FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findAllForAdmin(): array {
        $stmt = $this->db->query("SELECT id, username, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC");
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->mapRowToEntity($row);
        }
        return $users;
    }

    private function mapRowToEntity(array $row): User {
        return new User(
            username: $row['username'] ?? '',
            firstName: $row['first_name'] ?? '',
            lastName: $row['last_name'] ?? '',
            email: $row['email'] ?? '',
            passwordHash: $row['password_hash'] ?? '',
            id: $row['id'] ?? null,
            role: $row['role'] ?? 'OPERATOR',
        );
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