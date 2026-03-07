<?php
namespace App\Controllers;

use App\Models\Task;
use App\Models\User;

class TaskController {
    private $taskModel;
    private $userModel;

    public function __construct() {
        $this->taskModel = new Task();
        $this->userModel = new User();
    }

    private function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $user = $this->userModel->findByToken($token);
            if ($user) return $user['id'];
        }
        
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    public function index() {
        $userId = $this->authenticate();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? null;
        
        $tasks = $this->taskModel->getAll($userId, $page, 5, $status);
        echo json_encode($tasks);
    }

    public function store() {
        $userId = $this->authenticate();
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['title']) || trim($data['title']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input. 'title' is required."]);
            return;
        }
        
        $id = $this->taskModel->create($userId, $data['title'], $data['description'] ?? '');
        echo json_encode(["id" => $id, "message" => "Task created"]);
    }

    public function update($id) {
        $userId = $this->authenticate();
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input."]);
            return;
        }

        if (isset($data['status']) && !in_array($data['status'], ['pending', 'in_progress', 'completed'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid status. Must be one of: pending, in_progress, completed."]);
            return;
        }
        
        if ($this->taskModel->update($id, $userId, $data)) {
            echo json_encode(["message" => "Task updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Task not found or no fields to update"]);
        }
    }

    public function delete($id) {
        $userId = $this->authenticate();
        if ($this->taskModel->softDelete($id, $userId)) {
            echo json_encode(["message" => "Task deleted"]);
        }
    }
}