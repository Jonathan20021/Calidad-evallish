<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class FormTemplate
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByCampaign($campaignId)
    {
        $stmt = $this->db->prepare("SELECT * FROM form_templates WHERE campaign_id = ? AND active = 1 ORDER BY id DESC");
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public function getAllWithCampaign()
    {
        $stmt = $this->db->query("
            SELECT t.*, c.name as campaign_name
            FROM form_templates t
            JOIN campaigns c ON t.campaign_id = c.id
            ORDER BY c.name, t.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM form_templates WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO form_templates (campaign_id, title, description, active) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['campaign_id'],
            $data['title'],
            $data['description'] ?? '',
            $data['active'] ?? 1
        ]);
    }

    public function deactivateByCampaign($campaignId)
    {
        $stmt = $this->db->prepare("UPDATE form_templates SET active = 0 WHERE campaign_id = ?");
        return $stmt->execute([$campaignId]);
    }

    public function updateTitle($id, $title, $description = '')
    {
        $stmt = $this->db->prepare("UPDATE form_templates SET title = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$title, $description, $id]);
    }

    public function setActive($id, $active)
    {
        $stmt = $this->db->prepare("UPDATE form_templates SET active = ? WHERE id = ?");
        return $stmt->execute([(int) $active, $id]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
