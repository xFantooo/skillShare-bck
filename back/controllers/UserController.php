<?php
require_once './models/User.php';

class UserController {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function create($data) {
        $user = new User($this->db);
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($user->create()) {
            return ['message' => 'Utilisateur créé'];
        } else {
            http_response_code(400);
            return ['error' => 'Erreur de création'];
        }
    }

    public function list() {
        $user = new User($this->db);
        $stmt = $user->getAll();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
