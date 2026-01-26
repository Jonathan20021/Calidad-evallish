<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class CallAnalytics
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    public function findByCallId($callId, $model)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM call_ai_analytics
            WHERE call_id = ? AND model = ?
            LIMIT 1
        ");
        $stmt->execute([$callId, $model]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO call_ai_analytics (call_id, model, score, summary, metrics_json, raw_response_json)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['call_id'],
            $data['model'],
            $data['score'],
            $data['summary'],
            $data['metrics_json'],
            $data['raw_response_json']
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE call_ai_analytics
            SET score = ?, summary = ?, metrics_json = ?, raw_response_json = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['score'],
            $data['summary'],
            $data['metrics_json'],
            $data['raw_response_json'],
            $id
        ]);
    }

    private function ensureTable(): void
    {
        try {
            $this->db->query("SELECT 1 FROM call_ai_analytics LIMIT 1");
        } catch (\Throwable $e) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS call_ai_analytics (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    call_id INT NOT NULL,
                    model VARCHAR(100) NOT NULL,
                    score DECIMAL(5,2) NULL,
                    summary TEXT,
                    metrics_json LONGTEXT,
                    raw_response_json LONGTEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_call_model (call_id, model),
                    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }
}
