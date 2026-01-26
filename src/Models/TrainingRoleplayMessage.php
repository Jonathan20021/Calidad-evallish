<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingRoleplayMessage
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByRoleplayId($roleplayId, $limit = 200): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM training_roleplay_messages
            WHERE roleplay_id = ?
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $roleplayId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_roleplay_messages (roleplay_id, sender, message_text)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['roleplay_id'],
            $data['sender'],
            $data['message_text']
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
