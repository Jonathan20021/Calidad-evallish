<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingRoleplay
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_roleplays
                (script_id, agent_id, qa_id, campaign_id, status, score, ai_summary, objectives_text, tone_text, obstacles_text, rubric_id, ai_actions_json, qa_plan_text, started_at)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['script_id'] ?? null,
            $data['agent_id'],
            $data['qa_id'] ?? null,
            $data['campaign_id'] ?? null,
            $data['status'] ?? 'active',
            $data['score'] ?? null,
            $data['ai_summary'] ?? null,
            $data['objectives_text'] ?? null,
            $data['tone_text'] ?? null,
            $data['obstacles_text'] ?? null,
            $data['rubric_id'] ?? null,
            $data['ai_actions_json'] ?? null,
            $data['qa_plan_text'] ?? null,
            $data['started_at'] ?? date('Y-m-d H:i:s')
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT tr.*,
                   ts.title as script_title,
                   ts.script_text,
                   ts.scenario_text,
                   ts.persona_json,
                   a.full_name as agent_name,
                   qa.full_name as qa_name,
                   c.name as campaign_name,
                   r.title as rubric_title
            FROM training_roleplays tr
            LEFT JOIN training_scripts ts ON tr.script_id = ts.id
            JOIN users a ON tr.agent_id = a.id
            LEFT JOIN users qa ON tr.qa_id = qa.id
            LEFT JOIN campaigns c ON tr.campaign_id = c.id
            LEFT JOIN training_rubrics r ON tr.rubric_id = r.id
            WHERE tr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByAgentId($agentId, $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT tr.*,
                   ts.title as script_title,
                   c.name as campaign_name
            FROM training_roleplays tr
            LEFT JOIN training_scripts ts ON tr.script_id = ts.id
            LEFT JOIN campaigns c ON tr.campaign_id = c.id
            WHERE tr.agent_id = ?
            ORDER BY tr.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(1, $agentId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecent($limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT tr.*,
                   ts.title as script_title,
                   a.full_name as agent_name,
                   qa.full_name as qa_name,
                   c.name as campaign_name
            FROM training_roleplays tr
            LEFT JOIN training_scripts ts ON tr.script_id = ts.id
            JOIN users a ON tr.agent_id = a.id
            LEFT JOIN users qa ON tr.qa_id = qa.id
            LEFT JOIN campaigns c ON tr.campaign_id = c.id
            ORDER BY tr.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentByAgent($agentId, $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT tr.*,
                   ts.title as script_title
            FROM training_roleplays tr
            LEFT JOIN training_scripts ts ON tr.script_id = ts.id
            WHERE tr.agent_id = ?
            ORDER BY tr.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $agentId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($id, array $data): bool
    {
        $fields = [];
        $params = [];

        if (array_key_exists('status', $data)) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
        }
        if (array_key_exists('score', $data)) {
            $fields[] = 'score = ?';
            $params[] = $data['score'];
        }
        if (array_key_exists('ai_summary', $data)) {
            $fields[] = 'ai_summary = ?';
            $params[] = $data['ai_summary'];
        }
        if (array_key_exists('ai_actions_json', $data)) {
            $fields[] = 'ai_actions_json = ?';
            $params[] = $data['ai_actions_json'];
        }
        if (array_key_exists('qa_plan_text', $data)) {
            $fields[] = 'qa_plan_text = ?';
            $params[] = $data['qa_plan_text'];
        }
        if (array_key_exists('ended_at', $data)) {
            $fields[] = 'ended_at = ?';
            $params[] = $data['ended_at'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE training_roleplays SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(status = 'completed') as completed,
                AVG(score) as avg_score
            FROM training_roleplays
        ");
        $row = $stmt->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'avg_score' => $row['avg_score'] !== null ? (float) $row['avg_score'] : null
        ];
    }
}
