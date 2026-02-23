<?php

namespace App\Models;

use App\Config\Database;

class QaRetrainingSimulation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("\n            INSERT INTO qa_retraining_simulations\n                (retraining_id, agent_id, simulation_type, title, scenario_text, checklist_json, feedback_mode, min_score, status)\n            VALUES\n                (?, ?, ?, ?, ?, ?, ?, ?, ?)\n        ");

        return $stmt->execute([
            $data['retraining_id'],
            $data['agent_id'],
            $data['simulation_type'],
            $data['title'],
            $data['scenario_text'] ?? null,
            $data['checklist_json'] ?? null,
            $data['feedback_mode'] ?? 'auto',
            $data['min_score'] ?? 80,
            $data['status'] ?? 'pending'
        ]);
    }

    public function getByRetrainingAndAgent(int $retrainingId, int $agentId): array
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_simulations\n            WHERE retraining_id = ?\n              AND agent_id = ?\n            ORDER BY id ASC\n        ");
        $stmt->execute([$retrainingId, $agentId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM qa_retraining_simulations WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateById(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        $allowed = [
            'checklist_json',
            'feedback_mode',
            'transcript_text',
            'score',
            'status',
            'feedback_text',
            'reviewed_by',
            'reviewed_at',
            'completed_at'
        ];

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
        $stmt = $this->db->prepare("UPDATE qa_retraining_simulations SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function countCompleted(int $retrainingId, int $agentId): int
    {
        $stmt = $this->db->prepare("\n            SELECT COUNT(*)\n            FROM qa_retraining_simulations\n            WHERE retraining_id = ?\n              AND agent_id = ?\n              AND status = 'completed'\n        ");
        $stmt->execute([$retrainingId, $agentId]);
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(int $retrainingId, int $agentId): int
    {
        $stmt = $this->db->prepare("\n            SELECT COUNT(*)\n            FROM qa_retraining_simulations\n            WHERE retraining_id = ?\n              AND agent_id = ?\n        ");
        $stmt->execute([$retrainingId, $agentId]);
        return (int) $stmt->fetchColumn();
    }
}
