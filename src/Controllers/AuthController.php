<?php

namespace App\Controllers;

use App\Models\User;
use Exception;

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return is_array($data) ? $data : array();
    }

    public function register()
    {
        try {
            $data = $this->getJsonInput();

            $username = trim(isset($data['username']) ? $data['username'] : '');
            $password = trim(isset($data['password']) ? $data['password'] : '');

            if ($username === '' || $password === '') {
                http_response_code(400);
                echo json_encode(array('error' => 'Username and password are required'));
                return;
            }

            if (strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(array('error' => 'Username must be at least 3 characters'));
                return;
            }

            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(array('error' => 'Password must be at least 6 characters'));
                return;
            }

            $existing = $this->userModel->findByUsername($username);
            if ($existing) {
                http_response_code(409);
                echo json_encode(array('error' => 'Username already exists'));
                return;
            }

            $created = $this->userModel->create($username, $password);

            if (!$created) {
                http_response_code(500);
                echo json_encode(array('error' => 'Registration failed'));
                return;
            }

            http_response_code(201);
            echo json_encode(array('message' => 'User registered successfully'));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function login()
    {
        try {
            $data = $this->getJsonInput();

            $username = trim(isset($data['username']) ? $data['username'] : '');
            $password = trim(isset($data['password']) ? $data['password'] : '');

            if ($username === '' || $password === '') {
                http_response_code(400);
                echo json_encode(array('error' => 'Username and password are required'));
                return;
            }

            $user = $this->userModel->findByUsername($username);

            if (!$user || !password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode(array('error' => 'Invalid credentials'));
                return;
            }

            $token = bin2hex(random_bytes(32));
            $this->userModel->updateToken((int)$user['id'], $token);

            echo json_encode(array(
                'message' => 'Login successful',
                'token' => $token,
                'user' => array(
                    'id' => (int)$user['id'],
                    'username' => $user['username']
                )
            ));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function logout()
    {
        try {
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
                return;
            }

            $token = $matches[1];
            $user = $this->userModel->findByToken($token);

            if (!$user) {
                http_response_code(401);
                echo json_encode(array('error' => 'Invalid token'));
                return;
            }

            $this->userModel->updateToken((int)$user['id'], null);

            echo json_encode(array('message' => 'Logged out successfully'));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }
}