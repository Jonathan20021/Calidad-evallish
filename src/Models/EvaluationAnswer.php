<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class EvaluationAnswer
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByEvaluationId($evaluationId)
    {
        $stmt = $this->db->prepare("
            SELECT ea.*, 
                   ff.label as field_label, 
                   ff.weight as field_weight,
                   ff.field_type
            FROM evaluation_answers ea
            JOIN form_fields ff ON ea.field_id = ff.id
            WHERE ea.evaluation_id = ?
            ORDER BY ff.field_order ASC
        ");
        $stmt->execute([$evaluationId]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO evaluation_answers (evaluation_id, field_id, score_given, text_answer, comment) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['evaluation_id'],
            $data['field_id'],
            $data['score_given'] ?? null,
            $data['text_answer'] ?? null,
            $data['comment'] ?? ''
        ]);
    }

    public function getWeakAreasByAgent($agentId, $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT ff.id as field_id,
                   ff.label as field_label,
                   AVG(ea.score_given) as avg_score,
                   COUNT(*) as total
            FROM evaluation_answers ea
            JOIN evaluations e ON ea.evaluation_id = e.id
            JOIN form_fields ff ON ea.field_id = ff.id
            WHERE e.agent_id = ?
              AND ea.score_given IS NOT NULL
            GROUP BY ff.id, ff.label
            ORDER BY avg_score ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $agentId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
