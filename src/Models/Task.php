<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Task
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getAll($userId, $page = 1, $limit = 5, $status = null)
    {
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $where = " WHERE user_id = :user_id AND is_deleted = 0 ";
        $params = array(':user_id' => $userId);

        if ($status !== null && $status !== '') {
            $where .= " AND status = :status ";
            $params[':status'] = $status;
        }

        $countSql = "SELECT COUNT(*) AS total FROM tasks" . $where;
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $countRow = $countStmt->fetch();
        $total = (int)$countRow['total'];

        $sql = "SELECT id, user_id, title, description, status, created_at, updated_at
                FROM tasks
                {$where}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        $tasks = $stmt->fetchAll();

        return array(
            'data' => $tasks,
            'pagination' => array(
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int)ceil($total / $limit)
            )
        );
    }

    public function create($userId, $title, $description = '')
    {
        $sql = "INSERT INTO tasks (user_id, title, description, status)
                VALUES (:user_id, :title, :description, 'pending')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(
            ':user_id' => $userId,
            ':title' => $title,
            ':description' => $description
        ));

        return (int)$this->conn->lastInsertId();
    }

    public function findById($id, $userId)
    {
        $sql = "SELECT id, user_id, title, description, status, created_at, updated_at
                FROM tasks
                WHERE id = :id AND user_id = :user_id AND is_deleted = 0
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(
            ':id' => $id,
            ':user_id' => $userId
        ));

        return $stmt->fetch();
    }

    public function update($id, $userId, $data)
    {
        $fields = array();
        $params = array(
            ':id' => $id,
            ':user_id' => $userId
        );

        if (isset($data['title'])) {
            $fields[] = "title = :title";
            $params[':title'] = trim((string)$data['title']);
        }

        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params[':description'] = trim((string)$data['description']);
        }

        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function softDelete($id, $userId)
    {
        $sql = "UPDATE tasks
                SET is_deleted = 1
                WHERE id = :id AND user_id = :user_id AND is_deleted = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(
            ':id' => $id,
            ':user_id' => $userId
        ));

        return $stmt->rowCount() > 0;
    }
}