<?php
// 1. Set Headers immediately
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. Handle Preflight Requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. Suppress HTML errors and handle Fatal Errors
ini_set('display_errors', 0);
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(["error" => "Fatal Error: " . $error['message']]);
    }
});

// 4. Global Exception Handler
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
});

// 5. Check for Dependencies
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(["error" => "Vendor folder missing. Please run 'setup.bat' or 'composer install'."]);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\TaskController;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // .env might be missing, continue with defaults
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($uri, '/'));

// Basic Routing
if ($parts[0] === 'auth') {
    $auth = new AuthController();
    if ($parts[1] === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->register();
    } elseif ($parts[1] === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->login();
    }
} 
elseif ($parts[0] === 'tasks') {
    $taskController = new TaskController();
    $id = $parts[1] ?? null;
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $taskController->index();
            break;
        case 'POST':
            $taskController->store();
            break;
        case 'PUT':
            if ($id) $taskController->update($id);
            break;
        case 'DELETE':
            if ($id) $taskController->delete($id);
            break;
    }
} elseif ($parts[0] === 'task_manager') {
    try {
        $db = \App\Config\Database::getConnection();
        echo json_encode(["message" => "Database connected successfully!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    }
}