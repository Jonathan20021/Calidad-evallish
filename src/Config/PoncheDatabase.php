<?php

namespace App\Config;

use PDO;
use PDOException;

class PoncheDatabase
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
        $this->host = $this->getEnv('PONCHE_DB_HOST', '192.185.46.27');
        $this->user = $this->getEnv('PONCHE_DB_USER', 'hhempeos_ponche');
        $this->pass = $this->getEnv('PONCHE_DB_PASS', 'Hugo##2025#');
        $this->dbname = $this->getEnv('PONCHE_DB_NAME', 'hhempeos_ponche');

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Ponche connection failed: " . $e->getMessage());
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
            self::$instance = new PoncheDatabase();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
