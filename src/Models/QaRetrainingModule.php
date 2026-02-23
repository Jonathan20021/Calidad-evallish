<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class QaRetrainingModule
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("\n            INSERT INTO qa_retraining_modules\n                (retraining_id, title, lesson_text, detected_error, sequence_order, pass_score, quiz_question, quiz_type, options_json, correct_answer, is_required)\n            VALUES\n                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n        ");

        return $stmt->execute([
            $data['retraining_id'],
            $data['title'],
            $data['lesson_text'] ?? null,
            $data['detected_error'] ?? null,
            $data['sequence_order'],
            $data['pass_score'] ?? 80,
            $data['quiz_question'] ?? null,
            $data['quiz_type'] ?? 'text',
            $data['options_json'] ?? null,
            $data['correct_answer'] ?? null,
            $data['is_required'] ?? 1
        ]);
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM qa_retraining_modules WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByRetrainingId(int $retrainingId): array
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_modules\n            WHERE retraining_id = ?\n            ORDER BY sequence_order ASC, id ASC\n        ");
        $stmt->execute([$retrainingId]);
        return $stmt->fetchAll();
    }

    public function getRequiredCountByRetrainingId(int $retrainingId): int
    {
        $stmt = $this->db->prepare("\n            SELECT COUNT(*) AS total\n            FROM qa_retraining_modules\n            WHERE retraining_id = ?\n              AND is_required = 1\n        ");
        $stmt->execute([$retrainingId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function getNextModule(int $retrainingId, int $sequenceOrder)
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_modules\n            WHERE retraining_id = ? AND sequence_order > ?\n            ORDER BY sequence_order ASC\n            LIMIT 1\n        ");
        $stmt->execute([$retrainingId, $sequenceOrder]);
        return $stmt->fetch();
    }

    public function hasModulesAfter(int $retrainingId, int $sequenceOrder): bool
    {
        $stmt = $this->db->prepare("\n            SELECT 1\n            FROM qa_retraining_modules\n            WHERE retraining_id = ? AND sequence_order > ?\n            LIMIT 1\n        ");
        $stmt->execute([$retrainingId, $sequenceOrder]);
        return (bool) $stmt->fetchColumn();
    }
}
