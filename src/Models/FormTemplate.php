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
        $stmt = $this->db->prepare("
            SELECT DISTINCT t.* 
            FROM form_templates t
            JOIN form_template_campaigns ftc ON t.id = ftc.template_id
            WHERE ftc.campaign_id = ? AND t.active = 1 
            ORDER BY t.id DESC
        ");
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public function getAllWithCampaign()
    {
        $stmt = $this->db->query("
            SELECT 
                t.*,
                GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') as campaign_names,
                GROUP_CONCAT(c.id ORDER BY c.name SEPARATOR ',') as campaign_ids
            FROM form_templates t
            LEFT JOIN form_template_campaigns ftc ON t.id = ftc.template_id
            LEFT JOIN campaigns c ON ftc.campaign_id = c.id
            GROUP BY t.id
            ORDER BY t.created_at DESC
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
        $stmt = $this->db->prepare("INSERT INTO form_templates (title, description, active) VALUES (?, ?, ?)");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['active'] ?? 1
        ]);
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

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM form_templates WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function assignCampaigns($templateId, $campaignIds)
    {
        // First, remove all existing campaign assignments
        $deleteStmt = $this->db->prepare("DELETE FROM form_template_campaigns WHERE template_id = ?");
        $deleteStmt->execute([$templateId]);

        // Then, insert new campaign assignments
        if (!empty($campaignIds) && is_array($campaignIds)) {
            $insertStmt = $this->db->prepare("
                INSERT INTO form_template_campaigns (template_id, campaign_id)
                VALUES (?, ?)
            ");

            foreach ($campaignIds as $campaignId) {
                $insertStmt->execute([$templateId, $campaignId]);
            }
        }

        return true;
    }

    public function getCampaignsByTemplate($templateId)
    {
        $stmt = $this->db->prepare("
            SELECT c.*
            FROM campaigns c
            JOIN form_template_campaigns ftc ON c.id = ftc.campaign_id
            WHERE ftc.template_id = ?
            ORDER BY c.name
        ");
        $stmt->execute([$templateId]);
        return $stmt->fetchAll();
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
