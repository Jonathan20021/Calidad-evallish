<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class EvaluationFeedback
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO evaluation_feedback_history
                (evaluation_id, qa_id, general_comments, strengths, action_type, improvement_areas, improvement_plan, tasks_commitments,
                 feedback_confirmed, feedback_confirmed_at, feedback_evidence_path, feedback_evidence_name, feedback_evidence_note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['evaluation_id'],
            $data['qa_id'],
            $data['general_comments'] ?? '',
            $data['strengths'] ?? null,
            $data['action_type'] ?? null,
            $data['improvement_areas'] ?? null,
            $data['improvement_plan'] ?? null,
            $data['tasks_commitments'] ?? null,
            $data['feedback_confirmed'] ?? 0,
            $data['feedback_confirmed_at'] ?? null,
            $data['feedback_evidence_path'] ?? null,
            $data['feedback_evidence_name'] ?? null,
            $data['feedback_evidence_note'] ?? null
        ]);
    }

    public function update($id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE evaluation_feedback_history
            SET general_comments = ?, 
                strengths = ?,
                action_type = ?, 
                improvement_areas = ?, 
                improvement_plan = ?, 
                tasks_commitments = ?,
                feedback_confirmed = ?, 
                feedback_confirmed_at = ?, 
                feedback_evidence_path = ?, 
                feedback_evidence_name = ?, 
                feedback_evidence_note = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['general_comments'] ?? '',
            $data['strengths'] ?? null,
            $data['action_type'] ?? null,
            $data['improvement_areas'] ?? null,
            $data['improvement_plan'] ?? null,
            $data['tasks_commitments'] ?? null,
            $data['feedback_confirmed'] ?? 0,
            $data['feedback_confirmed_at'] ?? null,
            $data['feedback_evidence_path'] ?? null,
            $data['feedback_evidence_name'] ?? null,
            $data['feedback_evidence_note'] ?? null,
            (int) $id
        ]);
    }

    public function findById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM evaluation_feedback_history WHERE id = ?");
        $stmt->execute([(int) $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByEvaluationId($evaluationId): array
    {
        $stmt = $this->db->prepare("
            SELECT ef.*, u.full_name as qa_name
            FROM evaluation_feedback_history ef
            JOIN users u ON ef.qa_id = u.id
            WHERE ef.evaluation_id = ?
            ORDER BY ef.created_at DESC
        ");
        $stmt->execute([(int) $evaluationId]);
        return $stmt->fetchAll();
    }

    public function deleteByEvaluationId($evaluationId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM evaluation_feedback_history WHERE evaluation_id = ?");
        return $stmt->execute([(int) $evaluationId]);
    }
}
