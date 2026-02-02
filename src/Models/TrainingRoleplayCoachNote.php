<?php

namespace App\Models;

use App\Config\Database;
use App\Models\User;
use PDO;

class TrainingRoleplayCoachNote
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_roleplay_coach_notes (roleplay_id, qa_id, note_text)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['roleplay_id'],
            $data['qa_id'],
            $data['note_text']
        ]);
    }

    public function getByRoleplayId($roleplayId, $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT trcn.*
            FROM training_roleplay_coach_notes trcn
            WHERE trcn.roleplay_id = ?
            ORDER BY trcn.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $roleplayId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            return $rows;
        }
        $ids = array_column($rows, 'qa_id');
        $map = (new User())->getMapByIds($ids);
        foreach ($rows as &$row) {
            $qaId = (int) ($row['qa_id'] ?? 0);
            $row['qa_name'] = $map[$qaId]['full_name'] ?? ('QA #' . $qaId);
        }
        unset($row);
        return $rows;
    }
}
