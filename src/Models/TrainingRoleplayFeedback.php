<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingRoleplayFeedback
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_roleplay_feedback
                (roleplay_id, message_id, score, feedback, checklist_json)
            VALUES
                (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['roleplay_id'],
            $data['message_id'],
            $data['score'] ?? null,
            $data['feedback'] ?? null,
            $data['checklist_json'] ?? null
        ]);
    }

    public function getByRoleplayId($roleplayId, $limit = 200): array
    {
        $stmt = $this->db->prepare("
            SELECT trf.*, trm.sender, trm.message_text
            FROM training_roleplay_feedback trf
            JOIN training_roleplay_messages trm ON trf.message_id = trm.id
            WHERE trf.roleplay_id = ?
            ORDER BY trf.created_at ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $roleplayId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAverageScoreByRoleplayId($roleplayId): ?float
    {
        $stmt = $this->db->prepare("
            SELECT AVG(COALESCE(qa_score, score))
            FROM training_roleplay_feedback
            WHERE roleplay_id = ? AND (score IS NOT NULL OR qa_score IS NOT NULL)
        ");
        $stmt->execute([(int) $roleplayId]);
        $value = $stmt->fetchColumn();
        return $value !== null ? (float) $value : null;
    }

    public function updateQaReview($id, array $data): bool
    {
        $fields = [];
        $params = [];

        if (array_key_exists('qa_score', $data)) {
            $fields[] = 'qa_score = ?';
            $params[] = $data['qa_score'];
        }
        if (array_key_exists('qa_feedback', $data)) {
            $fields[] = 'qa_feedback = ?';
            $params[] = $data['qa_feedback'];
        }
        if (array_key_exists('qa_checklist_json', $data)) {
            $fields[] = 'qa_checklist_json = ?';
            $params[] = $data['qa_checklist_json'];
        }
        if (array_key_exists('approved_by', $data)) {
            $fields[] = 'approved_by = ?';
            $params[] = $data['approved_by'];
        }
        if (array_key_exists('approved_at', $data)) {
            $fields[] = 'approved_at = ?';
            $params[] = $data['approved_at'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE training_roleplay_feedback SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
