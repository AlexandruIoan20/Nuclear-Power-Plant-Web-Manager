<?php

class UserRepository { 
    private PDO $db; 

    public function __construct(PDO $db) { 
        $this->db = $db; 
    }

    public function save (User $user): void { 
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)"); 
        $stmt->execute([
            'name' => $user->getName(), 
            'email' => $user->getEmail(), 
            'password_hash' => $user->getPasswordHash() 
        ]); 

        $user->setId((int)$this->db->lastInsertId()); 
    }

    public function findAll(): array { 
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC"); 
        $users = []; 

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
            $users[] = new User($row['name'], $row['email'], $row['passowrd_hash'], $row['id']); 
        }

        return $users; 
    }

}