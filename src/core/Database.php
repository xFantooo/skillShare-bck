<?php

declare(strict_types=1);

namespace App\core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnexion() 
    { 
        if (self::$pdo === null) {
        $env = $_ENV; 
        if(!isset($env['DB_HOST'], $env['DB_NAME'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_PORT'])) {
            throw new \RuntimeException('Database configuration is not set in the environment variables.');
        }
        $dbHost = $env['DB_HOST'];
        $dbName = $env['DB_NAME'];
        $dbUser = $env['DB_USER'];
        $dbPassword = $env['DB_PASSWORD'];
        $dbPort = $env['DB_PORT'] ?? '3306'; 

        try {
           self::$pdo = new PDO(
            "mysql:host=$dbHost;dbname=$dbName;port=$dbPort;charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                
            ]
            );
        } catch (\PDOException $pdoEx) {
            die('Database connection failed: ' . $pdoEx->getMessage());
            
        }

    }
    //rend disponible la connexion PDO
    return self::$pdo;
    }

    
    
}