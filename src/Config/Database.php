<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $conn;

    private $host = '192.185.46.27';
    private $user = 'hhempeos_calidad';
    private $pass = 'Evallish.2026';
    private $dbname = 'hhempeos_calidad';

    private function __construct()
    {
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
