<?php

namespace App\Models;

use App\Config\Database;

class AiEvaluationCriteria
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT aec.*,
                   cc.name as project_name,
                   camp.name as campaign_name
            FROM ai_evaluation_criteria aec
            LEFT JOIN corporate_clients cc ON aec.project_id = cc.id
            LEFT JOIN campaigns camp ON aec.campaign_id = camp.id
            ORDER BY aec.active DESC, aec.updated_at DESC, aec.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM ai_evaluation_criteria WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_evaluation_criteria (project_id, campaign_id, call_type, criteria_text, active)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['project_id'] ?? null,
            $data['campaign_id'] ?? null,
            $data['call_type'] ?? null,
            $data['criteria_text'],
            $data['active'] ?? 1
        ]);
    }

    public function update($id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE ai_evaluation_criteria
            SET project_id = ?,
                campaign_id = ?,
                call_type = ?,
                criteria_text = ?,
                active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['project_id'] ?? null,
            $data['campaign_id'] ?? null,
            $data['call_type'] ?? null,
            $data['criteria_text'],
            $data['active'] ?? 1,
            (int) $id
        ]);
    }

    public function setActive($id, int $active): bool
    {
        $stmt = $this->db->prepare("UPDATE ai_evaluation_criteria SET active = ? WHERE id = ?");
        return $stmt->execute([$active, (int) $id]);
    }

    public function getMatching($projectId, $campaignId, $callType): array
    {
        $stmt = $this->db->prepare("
            SELECT aec.*,
                   cc.name as project_name,
                   camp.name as campaign_name,
                   (
                       (aec.project_id IS NOT NULL) +
                       (aec.campaign_id IS NOT NULL) +
                       (aec.call_type IS NOT NULL)
                   ) as specificity
            FROM ai_evaluation_criteria aec
            LEFT JOIN corporate_clients cc ON aec.project_id = cc.id
            LEFT JOIN campaigns camp ON aec.campaign_id = camp.id
            WHERE aec.active = 1
              AND (aec.project_id IS NULL OR aec.project_id = ?)
              AND (aec.campaign_id IS NULL OR aec.campaign_id = ?)
              AND (aec.call_type IS NULL OR aec.call_type = ?)
            ORDER BY
                (aec.project_id = ?) DESC,
                (aec.campaign_id = ?) DESC,
                (aec.call_type = ?) DESC,
                specificity DESC,
                aec.id DESC
        ");
        $stmt->execute([
            $projectId,
            $campaignId,
            $callType,
            $projectId,
            $campaignId,
            $callType
        ]);
        return $stmt->fetchAll();
    }

    private function ensureTable(): void
    {
        try {
            $this->db->query("SELECT 1 FROM ai_evaluation_criteria LIMIT 1");
        } catch (\Throwable $e) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS ai_evaluation_criteria (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    project_id INT NULL,
                    campaign_id INT NULL,
                    call_type VARCHAR(80) NULL,
                    criteria_text TEXT NOT NULL,
                    active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (project_id) REFERENCES corporate_clients(id) ON DELETE SET NULL,
                    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }
}
