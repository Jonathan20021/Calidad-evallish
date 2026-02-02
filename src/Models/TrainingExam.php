<?php

namespace App\Models;

use App\Config\Database;
use App\Models\User;
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
                   c.name as campaign_name
            FROM training_exams te
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            WHERE te.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $this->attachUserNames($row);
    }

    public function getByAgentId($agentId, $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT te.*,
                   c.name as campaign_name
            FROM training_exams te
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            WHERE te.agent_id = ?
            ORDER BY te.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(1, $agentId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachUserNamesToRows($rows);
    }

    public function getRecent($limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT te.*,
                   c.name as campaign_name
            FROM training_exams te
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            ORDER BY te.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachUserNamesToRows($rows);
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
                   c.name as campaign_name
            FROM training_exams te
            LEFT JOIN campaigns c ON te.campaign_id = c.id
            WHERE te.public_token = ?
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $this->attachUserNames($row);
    }

    private function attachUserNames($row)
    {
        if (!$row) {
            return $row;
        }

        $userModel = new User();
        $map = $userModel->getMapByIds([(int) ($row['agent_id'] ?? 0), (int) ($row['qa_id'] ?? 0)]);

        $agentId = (int) ($row['agent_id'] ?? 0);
        $qaId = (int) ($row['qa_id'] ?? 0);
        $row['agent_name'] = $map[$agentId]['full_name'] ?? ('Agente #' . $agentId);
        $row['qa_name'] = $map[$qaId]['full_name'] ?? ('QA #' . $qaId);
        return $row;
    }

    private function attachUserNamesToRows(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $ids = [];
        foreach ($rows as $row) {
            if (isset($row['agent_id'])) {
                $ids[] = (int) $row['agent_id'];
            }
            if (isset($row['qa_id'])) {
                $ids[] = (int) $row['qa_id'];
            }
        }
        $map = (new User())->getMapByIds($ids);

        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $qaId = (int) ($row['qa_id'] ?? 0);
            $row['agent_name'] = $map[$agentId]['full_name'] ?? ('Agente #' . $agentId);
            $row['qa_name'] = $map[$qaId]['full_name'] ?? ('QA #' . $qaId);
        }
        unset($row);

        return $rows;
    }
}
