<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Task {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll($userId, $page = 1, $limit = 5, $status = null) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM tasks WHERE user_id = :user_id AND is_deleted = 0";
        $params = [':user_id' => $userId];

        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($userId, $title, $description) {
        $sql = "INSERT INTO tasks (user_id, title, description, status) VALUES (:user_id, :title, :description, 'pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':description' => $description
        ]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $userId, $data) {
        $fields = [];
        $params = [':id' => $id, ':user_id' => $userId];

        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params[':title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params[':status'] = $data['status'];
        }

        if (empty($fields)) {
            return false; // No fields to update
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function findById($id, $userId) {
        $sql = "SELECT * FROM tasks WHERE id = :id AND user_id = :user_id AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch();
    }

    public function softDelete($id, $userId) {
        $sql = "UPDATE tasks SET is_deleted = 1 WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount();
    }
}