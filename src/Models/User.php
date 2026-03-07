<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function create($username, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(array(
            ':username' => $username,
            ':password' => $hash
        ));
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(':username' => $username));

        return $stmt->fetch();
    }

    public function updateToken($id, $token)
    {
        $sql = "UPDATE users SET api_token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(array(
            ':token' => $token,
            ':id' => $id
        ));
    }

    public function findByToken($token)
    {
        $sql = "SELECT * FROM users WHERE api_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(':token' => $token));

        return $stmt->fetch();
    }

    public function findById($id)
    {
        $sql = "SELECT id, username, created_at FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(':id' => $id));

        return $stmt->fetch();
    }
}