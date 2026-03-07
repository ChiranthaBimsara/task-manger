<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function create($username, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':username' => $username, ':password' => $hash]);
    }

    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function updateToken($id, $token) {
        $sql = "UPDATE users SET api_token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':token' => $token, ':id' => $id]);
    }

    public function findByToken($token) {
        $sql = "SELECT * FROM users WHERE api_token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }
}