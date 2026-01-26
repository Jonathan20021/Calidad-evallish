<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingExamQuestion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_exam_questions
                (exam_id, question_text, question_type, options_json, correct_answer, weight)
            VALUES
                (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['exam_id'],
            $data['question_text'],
            $data['question_type'] ?? 'open',
            $data['options_json'] ?? null,
            $data['correct_answer'] ?? null,
            $data['weight'] ?? 1.0
        ]);
    }

    public function getByExamId($examId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM training_exam_questions
            WHERE exam_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$examId]);
        return $stmt->fetchAll();
    }
}
