<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class QaRetrainingSimulationTemplate
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("\n            INSERT INTO qa_retraining_simulation_templates\n                (campaign_id, simulation_type, title, scenario_text, checklist_json, feedback_mode, min_score, active, created_by)\n            VALUES\n                (?, ?, ?, ?, ?, ?, ?, ?, ?)\n        ");

        return $stmt->execute([
            $data['campaign_id'] ?? null,
            $data['simulation_type'],
            $data['title'],
            $data['scenario_text'],
            $data['checklist_json'] ?? null,
            $data['feedback_mode'] ?? 'auto',
            $data['min_score'] ?? 80,
            $data['active'] ?? 1,
            $data['created_by']
        ]);
    }

    public function updateById(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $allowed = ['campaign_id', 'simulation_type', 'title', 'scenario_text', 'checklist_json', 'feedback_mode', 'min_score', 'active'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE qa_retraining_simulation_templates SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM qa_retraining_simulation_templates WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function setActive(int $id, int $active): bool
    {
        $stmt = $this->db->prepare("UPDATE qa_retraining_simulation_templates SET active = ? WHERE id = ?");
        return $stmt->execute([$active, $id]);
    }

    public function getAllWithCampaign(int $limit = 200): array
    {
        $stmt = $this->db->prepare("\n            SELECT t.*, c.name AS campaign_name\n            FROM qa_retraining_simulation_templates t\n            LEFT JOIN campaigns c ON t.campaign_id = c.id\n            ORDER BY t.active DESC, t.updated_at DESC\n            LIMIT :limit\n        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveForCampaign(int $campaignId): array
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM qa_retraining_simulation_templates\n            WHERE active = 1\n              AND (campaign_id = ? OR campaign_id IS NULL)\n            ORDER BY (campaign_id = ?) ASC, id ASC\n        ");
        $stmt->execute([$campaignId, $campaignId]);
        return $stmt->fetchAll();
    }
}
