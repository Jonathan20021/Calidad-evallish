<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingRubricItem
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_rubric_items (rubric_id, label, weight)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['rubric_id'],
            $data['label'],
            $data['weight'] ?? 1.0
        ]);
    }

    public function getByRubricId($rubricId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM training_rubric_items
            WHERE rubric_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([(int) $rubricId]);
        return $stmt->fetchAll();
    }
}
