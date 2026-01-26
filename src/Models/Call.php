<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Call
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.full_name as agent_name,
                   camp.name as campaign_name,
                   e.id as evaluation_id
            FROM calls c
            JOIN users u ON c.agent_id = u.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.call_id = c.id
            ORDER BY c.call_datetime DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.full_name as agent_name,
                   camp.name as campaign_name,
                   e.id as evaluation_id
            FROM calls c
            JOIN users u ON c.agent_id = u.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.call_id = c.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO calls (agent_id, campaign_id, call_datetime, duration_seconds, customer_phone, notes, recording_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['agent_id'],
            $data['campaign_id'],
            $data['call_datetime'],
            $data['duration_seconds'],
            $data['customer_phone'],
            $data['notes'],
            $data['recording_path']
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function getByCampaignIds(array $campaignIds, $limit = 50): array
    {
        if (empty($campaignIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.full_name as agent_name,
                   camp.name as campaign_name,
                   e.percentage as evaluation_percentage,
                   ai.score as ai_score,
                   ai.summary as ai_summary
            FROM calls c
            JOIN users u ON c.agent_id = u.id
            JOIN campaigns camp ON c.campaign_id = camp.id
            LEFT JOIN evaluations e ON e.call_id = c.id
            LEFT JOIN call_ai_analytics ai ON ai.call_id = c.id AND ai.model = ?
            WHERE c.campaign_id IN ($placeholders)
            ORDER BY c.call_datetime DESC
            LIMIT ?
        ");
        $params = array_merge([\App\Config\Config::GEMINI_MODEL], $campaignIds, [(int) $limit]);
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
        return $stmt->fetchAll();
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
}
