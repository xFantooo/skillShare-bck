<?php
class Database {
    private $conn;

    public function connect() {
        $host = getenv('DB_HOST');
        $db = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }

        return $this->conn;
    }
}
