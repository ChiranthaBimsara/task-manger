<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : '127.0.0.1';
        $db   = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'task_manager';
        $user = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root';
        $pass = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '';

        $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        );

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getConnection()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance->conn;
    }
}