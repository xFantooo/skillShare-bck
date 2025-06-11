<?php

namespace App\Models;

class User
{
    private int $id;
    private string $username;
    private string $avatar = "default_avatar.png";
    private string $email;
    private array $role = ["ROLE_USER"];
    private string $password;
    private string $created_at;


    public function __construct(array $data = [])
    {
       
        $this->username = $data['username'] ?? '';
        
        $this->email = $data['email'] ?? '';
        
        $this->password = $data['password'] ?? '';
        
    }







    public function getId(): int { return $this->id; }
    public function setId(int $id): self { $this->id = $id; return $this; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }

    public function getAvatar(): string { return $this->avatar; }
    public function setAvatar(string $avatar): self { $this->avatar = $avatar; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getRole(): array { return $this->role; }
    public function setRole(array $role): self { $this->role = $role; return $this; }

    public function getPasswordHash(): string { return $this->password; }
    public function setPasswordHash(string $passwordHash): self { $this->password = $passwordHash; return $this; }

   

    public function getCreatedAt(): string { return $this->created_at; }
    public function setCreatedAt(string $created_at): self { $this->created_at = $created_at; return $this; }
}