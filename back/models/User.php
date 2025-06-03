<?php


class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $password_hash;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (username, email, password_hash) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password_hash);

        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT id, username, email FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
