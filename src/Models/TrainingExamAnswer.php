<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingExamAnswer
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_exam_answers (question_id, answer_text, score, feedback)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['question_id'],
            $data['answer_text'] ?? null,
            $data['score'] ?? null,
            $data['feedback'] ?? null
        ]);
    }
}
