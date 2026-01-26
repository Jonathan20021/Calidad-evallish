<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class TrainingScript
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT ts.*,
                   u.full_name as created_by_name,
                   c.name as campaign_name
            FROM training_scripts ts
            JOIN users u ON ts.created_by = u.id
            LEFT JOIN campaigns c ON ts.campaign_id = c.id
            ORDER BY ts.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActive($limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT ts.*,
                   u.full_name as created_by_name,
                   c.name as campaign_name
            FROM training_scripts ts
            JOIN users u ON ts.created_by = u.id
            LEFT JOIN campaigns c ON ts.campaign_id = c.id
            WHERE ts.active = 1
            ORDER BY ts.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT ts.*,
                   u.full_name as created_by_name,
                   c.name as campaign_name
            FROM training_scripts ts
            JOIN users u ON ts.created_by = u.id
            LEFT JOIN campaigns c ON ts.campaign_id = c.id
            WHERE ts.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO training_scripts
                (title, script_text, scenario_text, persona_json, source_type, call_id, campaign_id, created_by, file_path, original_filename, active)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['script_text'],
            $data['scenario_text'] ?? null,
            $data['persona_json'] ?? null,
            $data['source_type'] ?? 'manual',
            $data['call_id'] ?? null,
            $data['campaign_id'] ?? null,
            $data['created_by'],
            $data['file_path'] ?? null,
            $data['original_filename'] ?? null,
            $data['active'] ?? 1
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
