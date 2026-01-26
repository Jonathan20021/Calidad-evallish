<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingExam
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_exams
                (agent_id, qa_id, campaign_id, title, status, prompt_context, public_token, public_enabled)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['agent_id'],
            $data['qa_id'],
            $data['campaign_id'] ?? null,
            $data['title'],
            $data['status'] ?? 'assigned',
            $data['prompt_context'] ?? null,
            $data['public_token'] ?? null,
            $data['public_enabled'] ?? 0
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT te.*,
                   a.full_name as agent_name,
                   qa.full_name as qa_name,
                   c.name as campaign_name
            FROM training_exams te
            JOIN users a ON te.agent_id = a.id
            JOIN users qa ON te.qa_id = qa.id
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            WHERE te.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByAgentId($agentId, $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT te.*,
                   qa.full_name as qa_name,
                   c.name as campaign_name
            FROM training_exams te
            JOIN users qa ON te.qa_id = qa.id
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            WHERE te.agent_id = ?
            ORDER BY te.created_at DESC
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
            SELECT te.*,
                   a.full_name as agent_name,
                   qa.full_name as qa_name,
                   c.name as campaign_name
            FROM training_exams te
            JOIN users a ON te.agent_id = a.id
            JOIN users qa ON te.qa_id = qa.id
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            ORDER BY te.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(status = 'completed') as completed,
                AVG(percentage) as avg_percentage
            FROM training_exams
        ");
        $row = $stmt->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'avg_percentage' => $row['avg_percentage'] !== null ? (float) $row['avg_percentage'] : null
        ];
    }

    public function updateStatus($id, array $data): bool
    {
        $fields = [];
        $params = [];

        if (array_key_exists('status', $data)) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
        }
        if (array_key_exists('public_token', $data)) {
            $fields[] = 'public_token = ?';
            $params[] = $data['public_token'];
        }
        if (array_key_exists('public_enabled', $data)) {
            $fields[] = 'public_enabled = ?';
            $params[] = (int) $data['public_enabled'];
        }
        if (array_key_exists('total_score', $data)) {
            $fields[] = 'total_score = ?';
            $params[] = $data['total_score'];
        }
        if (array_key_exists('max_score', $data)) {
            $fields[] = 'max_score = ?';
            $params[] = $data['max_score'];
        }
        if (array_key_exists('percentage', $data)) {
            $fields[] = 'percentage = ?';
            $params[] = $data['percentage'];
        }
        if (array_key_exists('ai_summary', $data)) {
            $fields[] = 'ai_summary = ?';
            $params[] = $data['ai_summary'];
        }
        if (array_key_exists('completed_at', $data)) {
            $fields[] = 'completed_at = ?';
            $params[] = $data['completed_at'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE training_exams SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function findByPublicToken(string $token)
    {
        $stmt = $this->db->prepare("
            SELECT te.*,
                   a.full_name as agent_name,
                   qa.full_name as qa_name,
                   c.name as campaign_name
            FROM training_exams te
            JOIN users a ON te.agent_id = a.id
            JOIN users qa ON te.qa_id = qa.id
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            WHERE te.public_token = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
}
