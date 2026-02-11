<?php

namespace App\Models;

use App\Config\Database;
use App\Models\User;
use PDO;

class Call
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureColumns();
    }

    public function getAll($limit = 50, $filters = [])
    {
        $where = [];
        $params = [];

        // Filter by agent
        if (!empty($filters['agent_id'])) {
            $where[] = "c.agent_id = :agent_id";
            $params[':agent_id'] = (int) $filters['agent_id'];
        }

        // Filter by campaign
        if (!empty($filters['campaign_id'])) {
            $where[] = "c.campaign_id = :campaign_id";
            $params[':campaign_id'] = (int) $filters['campaign_id'];
        }

        // Filter by project
        if (!empty($filters['project_id'])) {
            $where[] = "c.project_id = :project_id";
            $params[':project_id'] = (int) $filters['project_id'];
        }

        // Filter by call type
        if (!empty($filters['call_type'])) {
            $where[] = "c.call_type = :call_type";
            $params[':call_type'] = $filters['call_type'];
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(c.call_datetime) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(c.call_datetime) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Build WHERE clause
        $whereClause = '';
        if (!empty($where)) {
            $whereClause = 'WHERE ' . implode(' AND ', $where);
        }

        // Build base query
        $sql = "
            SELECT c.*,
                   cc.name as project_name,
                   camp.name as campaign_name,
                   e.id as evaluation_id
            FROM calls c
            LEFT JOIN corporate_clients cc ON c.project_id = cc.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.call_id = c.id
            $whereClause
        ";

        // Filter by status (evaluated/pending) - needs to be done after the join
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'evaluated') {
                $sql .= empty($where) ? " WHERE e.id IS NOT NULL" : " AND e.id IS NOT NULL";
            } elseif ($filters['status'] === 'pending') {
                $sql .= empty($where) ? " WHERE e.id IS NULL" : " AND e.id IS NULL";
            }
        }

        $sql .= " ORDER BY c.call_datetime DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);

        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachAgentNames($rows);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   cc.name as project_name,
                   camp.name as campaign_name,
                   e.id as evaluation_id
            FROM calls c
            LEFT JOIN corporate_clients cc ON c.project_id = cc.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.call_id = c.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return $row;
        }
        $rows = $this->attachAgentNames([$row]);
        return $rows[0] ?? $row;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO calls (agent_id, project_id, campaign_id, call_type, call_datetime, duration_seconds, customer_phone, lead, notes, recording_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['agent_id'],
            $data['project_id'] ?? null,
            $data['campaign_id'],
            $data['call_type'] ?? null,
            $data['call_datetime'],
            $data['duration_seconds'],
            $data['customer_phone'],
            $data['lead'] ?? null,
            $data['notes'],
            $data['recording_path']
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE calls 
            SET agent_id = ?, 
                project_id = ?, 
                campaign_id = ?, 
                call_type = ?, 
                call_datetime = ?, 
                duration_seconds = ?, 
                customer_phone = ?, 
                lead = ?, 
                notes = ?,
                recording_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['agent_id'],
            $data['project_id'] ?? null,
            $data['campaign_id'],
            $data['call_type'] ?? null,
            $data['call_datetime'],
            $data['duration_seconds'],
            $data['customer_phone'],
            $data['lead'] ?? null,
            $data['notes'],
            $data['recording_path'],
            $id
        ]);
    }

    public function deleteById($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM calls WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }

    public function getByCampaignIds(array $campaignIds, $limit = 50): array
    {
        if (empty($campaignIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("
            SELECT c.*,
                   cc.name as project_name,
                   camp.name as campaign_name,
                   e.percentage as evaluation_percentage,
                   ai.score as ai_score,
                   ai.summary as ai_summary
            FROM calls c
            LEFT JOIN corporate_clients cc ON c.project_id = cc.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.call_id = c.id
            LEFT JOIN call_ai_analytics ai ON ai.call_id = c.id AND ai.model = ?
            WHERE c.campaign_id IN ($placeholders)
            ORDER BY c.call_datetime DESC
            LIMIT ?
        ");
        $params = array_merge([\App\Config\Config::$GEMINI_MODEL], $campaignIds, [(int) $limit]);
        $index = 1;
        $lastIndex = count($params);
        foreach ($params as $value) {
            $type = $index === 1 ? PDO::PARAM_STR : PDO::PARAM_INT;
            if ($index === $lastIndex) {
                $type = PDO::PARAM_INT;
            }
            $stmt->bindValue($index, $value, $type);
            $index++;
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachAgentNames($rows);
    }

    public function getCountByCampaignIds(array $campaignIds): int
    {
        if (empty($campaignIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM calls WHERE campaign_id IN ($placeholders)");
        $stmt->execute($campaignIds);
        return (int) $stmt->fetchColumn();
    }

    public function getAverageDurationByCampaignIds(array $campaignIds): ?float
    {
        if (empty($campaignIds)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("SELECT AVG(duration_seconds) FROM calls WHERE campaign_id IN ($placeholders)");
        $stmt->execute($campaignIds);
        $value = $stmt->fetchColumn();
        return $value !== null ? (float) $value : null;
    }

    private function ensureColumns(): void
    {
        $this->ensureColumnExists('project_id', 'INT NULL');
        $this->ensureColumnExists('call_type', 'VARCHAR(80) NULL');
    }

    private function ensureColumnExists(string $column, string $definition): void
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM calls LIKE ?");
        $stmt->execute([$column]);
        if (!$stmt->fetch()) {
            $this->db->exec("ALTER TABLE calls ADD COLUMN $column $definition");
        }
    }

    private function attachAgentNames(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $agentIds = array_map(function ($row) {
            return $row['agent_id'] ?? null;
        }, $rows);

        $userModel = new User();
        $map = $userModel->getMapByIds($agentIds);

        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $user = $map[$agentId] ?? null;
            $row['agent_name'] = $user['full_name'] ?? ('Agente #' . $agentId);
            $row['agent_username'] = $user['username'] ?? null;
        }
        unset($row);

        return $rows;
    }
}
