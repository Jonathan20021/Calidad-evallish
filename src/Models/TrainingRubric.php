<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingRubric
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllActive(): array
    {
        $stmt = $this->db->query("
            SELECT tr.*,
                   c.name as campaign_name,
                   u.full_name as created_by_name
            FROM training_rubrics tr
            LEFT JOIN campaigns c ON tr.campaign_id = c.id
            JOIN users u ON tr.created_by = u.id
            WHERE tr.active = 1
            ORDER BY tr.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT tr.*,
                   c.name as campaign_name
            FROM training_rubrics tr
            LEFT JOIN campaigns c ON tr.campaign_id = c.id
            WHERE tr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_rubrics (campaign_id, title, active, created_by)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['campaign_id'] ?? null,
            $data['title'],
            $data['active'] ?? 1,
            $data['created_by']
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
