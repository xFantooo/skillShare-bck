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
            INSERT INTO `user` (username, avatar,email , `role`, password_hash, created_at)
            VALUES (?, ?, ?, ?, ?, ?);");

           return $stmt->execute([
                $User->getUsername(),
                $User->getAvatar(), 
                $User->getEmail(),
                json_encode($User->getRole()),
                $User->getPasswordHash(),
                $User->getCreatedAt()
                
            ]);

    }
    

     public function saveAvatar(string $avatarFilename): bool {
        // requête préparée obligatoire!!!
        $stmt = $this->pdo->prepare("
            UPDATE SET avatar `user` VALUES (?);");

           return $stmt->execute([
                $User->getUsername(),
                $User->getAvatar(), 
                $User->getEmail(),
                json_encode($User->getRole()),
                $User->getPasswordHash(),
                $User->getCreatedAt()
                
            ]);

    }
}