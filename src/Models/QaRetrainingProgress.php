<?php

namespace App\Models;

use App\Config\Database;

class QaRetrainingProgress
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByModuleAndAgent(int $moduleId, int $agentId)
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_progress\n            WHERE module_id = ? AND agent_id = ?\n            LIMIT 1\n        ");
        $stmt->execute([$moduleId, $agentId]);
        return $stmt->fetch();
    }

    public function upsert(array $data): bool
    {
        $existing = $this->findByModuleAndAgent((int) $data['module_id'], (int) $data['agent_id']);

        if ($existing) {
            $stmt = $this->db->prepare("\n                UPDATE qa_retraining_progress\n                SET status = ?,\n                    score = ?,\n                    answer_text = ?,\n                    attempts = ?,\n                    completed_at = ?,\n                    updated_at = CURRENT_TIMESTAMP\n                WHERE id = ?\n            ");
            return $stmt->execute([
                $data['status'],
                $data['score'] ?? null,
                $data['answer_text'] ?? null,
                $data['attempts'] ?? ($existing['attempts'] + 1),
                $data['completed_at'] ?? null,
                $existing['id']
            ]);
        }

        $stmt = $this->db->prepare("\n            INSERT INTO qa_retraining_progress\n                (module_id, retraining_id, agent_id, status, score, answer_text, attempts, completed_at)\n            VALUES\n                (?, ?, ?, ?, ?, ?, ?, ?)\n        ");
        return $stmt->execute([
            $data['module_id'],
            $data['retraining_id'],
            $data['agent_id'],
            $data['status'],
            $data['score'] ?? null,
            $data['answer_text'] ?? null,
            $data['attempts'] ?? 1,
            $data['completed_at'] ?? null
        ]);
    }

    public function getByRetrainingAndAgent(int $retrainingId, int $agentId): array
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_progress\n            WHERE retraining_id = ?\n              AND agent_id = ?\n        ");
        $stmt->execute([$retrainingId, $agentId]);
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['module_id']] = $row;
        }

        return $map;
    }

    public function getCompletedRequiredCount(int $retrainingId, int $agentId): int
    {
        $stmt = $this->db->prepare("\n            SELECT COUNT(*) AS total\n            FROM qa_retraining_progress p\n            INNER JOIN qa_retraining_modules m ON m.id = p.module_id\n            WHERE p.retraining_id = ?\n              AND p.agent_id = ?\n              AND p.status = 'completed'\n              AND m.is_required = 1\n        ");
        $stmt->execute([$retrainingId, $agentId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
