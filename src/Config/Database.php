<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $conn;

    private $host;
    private $user;
    private $pass;
    private $dbname;

    private function __construct()
    {
        // Cargar variables de entorno
        $this->host = $this->getEnv('DB_HOST', '192.185.46.27');
        $this->user = $this->getEnv('DB_USER', 'hhempeos_calidad');
        $this->pass = $this->getEnv('DB_PASS', 'Evallish.2026');
        $this->dbname = $this->getEnv('DB_NAME', 'hhempeos_calidad');

        try {
            // First connect without database selected to check/create it
            $this->conn = new PDO("mysql:host={$this->host};charset=utf8mb4", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if not exists
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Now use the database
            $this->conn->exec("USE `{$this->dbname}`");

            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    private function getEnv(string $key, string $default = ''): string
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
