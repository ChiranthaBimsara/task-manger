<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing fields"]);
            return;
        }

        if ($this->userModel->create($data['username'], $data['password'])) {
            echo json_encode(["message" => "User registered"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Registration failed"]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $user = $this->userModel->findByUsername($data['username'] ?? '');

        if ($user && password_verify($data['password'], $user['password'])) {
            $token = bin2hex(random_bytes(16));
            $this->userModel->updateToken($user['id'], $token);
            echo json_encode(["token" => $token]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid credentials"]);
        }
    }
}