<?php

declare(strict_types=1);

namespace App\repository;

use App\core\Database;
use App\models\User;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnexion();
    }

    public function save(User $User): bool {
        // requête préparée obligatoire!!!
        $stmt = $this->pdo->prepare("
            INSERT INTO `user` (username, avatar,email , `role`, password, created_at, email_token, is_verified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

           return $stmt->execute([
                $User->getUsername(),
                $User->getAvatar(), 
                $User->getEmail(),
                json_encode($User->getRole()),
                $User->getPassword(),
                $User->getCreatedAt(),
                $User->getEmailToken(),
                (int)$User->getIsVerified()

                
            ]);

    }
    public function findUserByUserName(string $username): ?User {
         $stmt = $this->pdo->prepare("SELECT * FROM `user` WHERE username = ?");
        $stmt->execute([$username]);
        $data = $stmt->fetch();
        if (!$data) return null;
        $user = new User($data);
        $user ->setId((int)$data['id_user']);
        $user->setVerifiedAt((new \DateTime())->format('Y-m-d H:i:s'));
        $user->setRole(json_decode($data['role'],true));
        

      

        return $user;
    }
    public function findUserByEmail(string $email): ?User {
         $stmt = $this->pdo->prepare("SELECT * FROM `user` WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        if (!$data) return null;
        $user = new User($data);
        $user ->setId((int)$data['id_user']);
        $user->setVerifiedAt((new \DateTime())->format('Y-m-d H:i:s'));
        $user->setRole(json_decode($data['role'],true));
        

      

        return $user;
    }

    public function findUserByToken($token): User {
        $stmt = $this->pdo->prepare("SELECT * FROM `user` WHERE email_token = ?");
        $stmt->execute([$token]);
        $data = $stmt->fetch();
        $user = new User($data);
        $user ->setId((int)$data['id_user']);
        $user->setVerifiedAt((new \DateTime())->format('Y-m-d H:i:s'));
        $user->setRole(json_decode($data['role'],true));
        

      

        return $user;
    }

      public function update(User $user): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE user SET 
            username = ?, 
            email = ?, 
            role = ?, 
            is_verified = ?, 
            email_token = ?,
            verified_at = ?,
            password = ?,
            avatar = ?
            WHERE id_user = ?"
        );

        return $stmt->execute([
            $user->getUserName(),
            $user->getEmail(),
            json_encode($user->getRole()),
            (int)$user->getIsVerified(),
            $user->getEmailToken(),
            $user->getVerifiedAt(),
            $user->getPassword(),
            $user->getAvatar(),
            $user->getId()
        ]);
    }
}