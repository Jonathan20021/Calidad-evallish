<?php

namespace App\Models;

use App\Config\Database;

class TrainingNotification
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_notifications (type, agent_id, qa_id, status, payload_json)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['type'],
            $data['agent_id'] ?? null,
            $data['qa_id'] ?? null,
            $data['status'] ?? 'pending',
            $data['payload_json'] ?? null
        ]);
    }
}
