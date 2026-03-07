<?php

namespace App\Controllers;

use App\Models\Task;
use App\Models\User;
use Exception;

class TaskController
{
    private $taskModel;
    private $userModel;

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->userModel = new User();
    }

    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return is_array($data) ? $data : array();
    }

    private function authenticate()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : array();
        $authHeader = '';

        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }

        if (!preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(array('error' => 'Unauthorized'));
            exit;
        }

        $token = $matches[1];
        $user = $this->userModel->findByToken($token);

        if (!$user) {
            http_response_code(401);
            echo json_encode(array('error' => 'Invalid or expired token'));
            exit;
        }

        return (int)$user['id'];
    }

    public function index()
    {
        try {
            $userId = $this->authenticate();

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $status = isset($_GET['status']) ? $_GET['status'] : null;

            if ($status !== null && $status !== '' && !in_array($status, array('pending', 'in_progress', 'completed'), true)) {
                http_response_code(400);
                echo json_encode(array('error' => 'Invalid status filter'));
                return;
            }

            $result = $this->taskModel->getAll($userId, $page, $limit, $status);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function show($id)
    {
        try {
            $userId = $this->authenticate();
            $task = $this->taskModel->findById($id, $userId);

            if (!$task) {
                http_response_code(404);
                echo json_encode(array('error' => 'Task not found'));
                return;
            }

            echo json_encode($task);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function store()
    {
        try {
            $userId = $this->authenticate();
            $data = $this->getJsonInput();

            $title = trim(isset($data['title']) ? $data['title'] : '');
            $description = trim(isset($data['description']) ? $data['description'] : '');

            if ($title === '') {
                http_response_code(400);
                echo json_encode(array('error' => 'Title is required'));
                return;
            }

            $id = $this->taskModel->create($userId, $title, $description);

            http_response_code(201);
            echo json_encode(array(
                'message' => 'Task created successfully',
                'id' => $id
            ));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function update($id)
    {
        try {
            $userId = $this->authenticate();
            $data = $this->getJsonInput();

            if (isset($data['title']) && trim((string)$data['title']) === '') {
                http_response_code(400);
                echo json_encode(array('error' => 'Title cannot be empty'));
                return;
            }

            if (isset($data['status']) && !in_array($data['status'], array('pending', 'in_progress', 'completed'), true)) {
                http_response_code(400);
                echo json_encode(array('error' => 'Invalid status. Use pending, in_progress, or completed'));
                return;
            }

            $updated = $this->taskModel->update($id, $userId, $data);

            if (!$updated) {
                http_response_code(404);
                echo json_encode(array('error' => 'Task not found or no changes made'));
                return;
            }

            echo json_encode(array('message' => 'Task updated successfully'));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function delete($id)
    {
        try {
            $userId = $this->authenticate();
            $deleted = $this->taskModel->softDelete($id, $userId);

            if (!$deleted) {
                http_response_code(404);
                echo json_encode(array('error' => 'Task not found'));
                return;
            }

            echo json_encode(array('message' => 'Task deleted successfully'));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }
}