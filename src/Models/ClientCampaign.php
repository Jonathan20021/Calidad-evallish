<?php

namespace App\Models;

use App\Config\Database;

class ClientCampaign
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCampaignIds($clientId): array
    {
        $stmt = $this->db->prepare("SELECT campaign_id FROM client_campaigns WHERE client_id = ?");
        $stmt->execute([$clientId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function getCampaignsByClientIds(array $clientIds): array
    {
        if (empty($clientIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($clientIds), '?'));
        $stmt = $this->db->prepare("
            SELECT cc.client_id, c.id, c.name
            FROM client_campaigns cc
            JOIN campaigns c ON c.id = cc.campaign_id
            WHERE cc.client_id IN ($placeholders)
            ORDER BY c.name ASC
        ");
        $stmt->execute($clientIds);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['client_id']][] = $row;
        }

        return $grouped;
    }

    public function setCampaigns($clientId, array $campaignIds): void
    {
        $this->db->prepare("DELETE FROM client_campaigns WHERE client_id = ?")->execute([$clientId]);

        if (empty($campaignIds)) {
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO client_campaigns (client_id, campaign_id) VALUES (?, ?)");
        foreach ($campaignIds as $campaignId) {
            $stmt->execute([$clientId, $campaignId]);
        }
    }
}
