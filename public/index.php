<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode([
        'error' => "PHP Error: {$message} in {$file} on line {$line}"
    ]);
    exit;
});

set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    exit;
});

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode([
        'error' => "Vendor folder missing. Run composer install first."
    ]);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Config\Database;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base folder path if project is inside /task-manger/public
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$path = $requestUri;

if ($scriptName !== '/' && strpos($path, $scriptName) === 0) {
    $path = substr($path, strlen($scriptName));
}

$path = trim($path, '/');
$parts = $path === '' ? [] : explode('/', $path);

// Home/info endpoint
if ($path === '' || $path === 'index.php') {
    echo json_encode([
        'message' => 'Task Manager API is running'
    ]);
    exit;
}

// Support requests routed as index.php/auth/login
if (!empty($parts) && $parts[0] === 'index.php') {
    array_shift($parts);
}

$resource = $parts[0] ?? '';
$id = isset($parts[1]) ? (int) $parts[1] : null;

// DB test route
if ($resource === 'db-test') {
    try {
        Database::getConnection();
        echo json_encode(['message' => 'Database connected successfully']);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($resource === 'auth') {
    $auth = new AuthController();
    $action = $parts[1] ?? '';

    if ($action === 'register' && $method === 'POST') {
        $auth->register();
        exit;
    }

    if ($action === 'login' && $method === 'POST') {
        $auth->login();
        exit;
    }

    if ($action === 'logout' && $method === 'POST') {
        $auth->logout();
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Auth route not found']);
    exit;
}

if ($resource === 'tasks') {
    $taskController = new TaskController();

    if ($method === 'GET' && $id === null) {
        $taskController->index();
        exit;
    }

    if ($method === 'GET' && $id !== null) {
        $taskController->show($id);
        exit;
    }

    if ($method === 'POST' && $id === null) {
        $taskController->store();
        exit;
    }

    if ($method === 'PUT' && $id !== null) {
        $taskController->update($id);
        exit;
    }

    if ($method === 'DELETE' && $id !== null) {
        $taskController->delete($id);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Task route not found']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found']);