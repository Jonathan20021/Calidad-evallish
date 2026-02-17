<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Chat
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 50, $filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['agent_id'])) {
            $where[] = "c.agent_id = :agent_id";
            $params[':agent_id'] = (int) $filters['agent_id'];
        }

        if (!empty($filters['campaign_id'])) {
            $where[] = "c.campaign_id = :campaign_id";
            $params[':campaign_id'] = (int) $filters['campaign_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(c.chat_date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(c.chat_date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT c.*,
                   cc.name as project_name,
                   camp.name as campaign_name,
                   e.id as evaluation_id,
                   e.percentage as evaluation_percentage
            FROM chats c
            LEFT JOIN corporate_clients cc ON c.project_id = cc.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.chat_id = c.id
            $whereClause
            ORDER BY c.chat_date DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->attachAgentNames($stmt->fetchAll());
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   cc.name as project_name,
                   camp.name as campaign_name,
                   e.id as evaluation_id
            FROM chats c
            LEFT JOIN corporate_clients cc ON c.project_id = cc.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.chat_id = c.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row)
            return null;

        $rows = $this->attachAgentNames([$row]);
        return $rows[0];
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO chats (agent_id, campaign_id, project_id, chat_date, customer_identifier, screenshot_path, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['agent_id'],
            $data['campaign_id'],
            $data['project_id'] ?? null,
            $data['chat_date'],
            $data['customer_identifier'] ?? null,
            $data['screenshot_path'] ?? null,
            $data['notes'] ?? null
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE chats 
            SET agent_id = ?, 
                campaign_id = ?, 
                project_id = ?, 
                chat_date = ?, 
                customer_identifier = ?, 
                screenshot_path = ?, 
                notes = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['agent_id'],
            $data['campaign_id'],
            $data['project_id'] ?? null,
            $data['chat_date'],
            $data['customer_identifier'] ?? null,
            $data['screenshot_path'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    public function deleteById($id)
    {
        $stmt = $this->db->prepare("DELETE FROM chats WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }

    private function attachAgentNames(array $rows): array
    {
        if (empty($rows))
            return $rows;

        $agentIds = array_unique(array_filter(array_column($rows, 'agent_id')));
        if (empty($agentIds))
            return $rows;

        $userModel = new User();
        $map = $userModel->getMapByIds($agentIds);

        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $user = $map[$agentId] ?? null;
            $row['agent_name'] = $user['full_name'] ?? ('Agente #' . $agentId);
        }
        return $rows;
    }
}
