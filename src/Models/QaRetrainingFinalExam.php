<?php

namespace App\Models;

use App\Config\Database;

class QaRetrainingFinalExam
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("\n            INSERT INTO qa_retraining_final_exams\n                (retraining_id, agent_id, min_score, status, question_payload_json)\n            VALUES\n                (?, ?, ?, ?, ?)\n        ");

        return $stmt->execute([
            $data['retraining_id'],
            $data['agent_id'],
            $data['min_score'] ?? 80,
            $data['status'] ?? 'pending',
            $data['question_payload_json'] ?? null
        ]);
    }

    public function findByRetrainingAndAgent(int $retrainingId, int $agentId)
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_final_exams\n            WHERE retraining_id = ? AND agent_id = ?\n            LIMIT 1\n        ");
        $stmt->execute([$retrainingId, $agentId]);
        return $stmt->fetch();
    }

    public function updateById(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $allowed = ['min_score', 'score', 'status', 'question_payload_json', 'answer_payload_json', 'feedback_text', 'attempts', 'completed_at'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE qa_retraining_final_exams SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }
}
