<?php

namespace App\Config;

use PDO;
use PDOException;

class PoncheDatabase
{
    private static $instance = null;
    private $conn;

    private $host = '192.185.46.27';
    private $user = 'hhempeos_ponche';
    private $pass = 'Hugo##2025#';
    private $dbname = 'hhempeos_ponche';

    private function __construct()
    {
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
