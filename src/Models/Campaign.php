<?php

namespace App\Models;

use App\Config\Database;
use App\Models\PoncheCampaign;
use PDO;

class Campaign
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $poncheRows = $this->getPoncheCampaigns(false);
        if (!empty($poncheRows)) {
            return $poncheRows;
        }

        $stmt = $this->db->query("SELECT * FROM campaigns ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getAllWithForms()
    {
        // First ensure we have local campaigns if they come from Ponche
        $this->getPoncheCampaigns(false);

        $stmt = $this->db->query("
            SELECT 
                c.*,
                GROUP_CONCAT(t.title ORDER BY t.title SEPARATOR '||') as form_titles,
                GROUP_CONCAT(t.id ORDER BY t.title SEPARATOR ',') as form_ids
            FROM campaigns c
            LEFT JOIN form_template_campaigns ftc ON c.id = ftc.campaign_id
            LEFT JOIN form_templates t ON ftc.template_id = t.id AND t.active = 1
            GROUP BY c.id
            ORDER BY c.name ASC
        ");

        $results = $stmt->fetchAll();

        // Process the grouped forms
        foreach ($results as &$row) {
            $row['forms'] = [];
            if (!empty($row['form_ids'])) {
                $ids = explode(',', $row['form_ids']);
                $titles = explode('||', $row['form_titles']);
                foreach ($ids as $index => $id) {
                    $row['forms'][] = [
                        'id' => $id,
                        'title' => $titles[$index]
                    ];
                }
            }
        }

        return $results;
    }

    public function getActive()
    {
        $poncheRows = $this->getPoncheCampaigns(true);
        if (!empty($poncheRows)) {
            return $poncheRows;
        }

        $stmt = $this->db->query("SELECT * FROM campaigns WHERE active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $poncheCampaign = $this->getPoncheById((int) $id);
        if ($poncheCampaign) {
            return $poncheCampaign;
        }

        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $poncheRows = $this->getPoncheByIds($ids);
        if (!empty($poncheRows)) {
            return $poncheRows;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE id IN ($placeholders) ORDER BY name ASC");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO campaigns (name, description, active) VALUES (?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['active'] ?? 1
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE campaigns SET name = ?, description = ?, active = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['active'] ?? 1,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM campaigns WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function getPoncheCampaigns(bool $activeOnly): array
    {
        try {
            $ponche = new PoncheCampaign();
            $rows = $activeOnly ? $ponche->getActive() : $ponche->getAll();
            if (empty($rows)) {
                return [];
            }
            $this->syncFromPonche($rows);
            return $this->normalizePoncheRows($rows);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getPoncheById(int $id)
    {
        try {
            $ponche = new PoncheCampaign();
            $row = $ponche->findById($id);
            if ($row) {
                $this->syncFromPonche([$row]);
                return $this->normalizePoncheRow($row);
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    private function getPoncheByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }
        try {
            $ponche = new PoncheCampaign();
            $rows = [];
            foreach ($ids as $id) {
                $row = $ponche->findById($id);
                if ($row) {
                    $rows[] = $row;
                }
            }
            if (empty($rows)) {
                return [];
            }
            $this->syncFromPonche($rows);
            return $this->normalizePoncheRows($rows);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function syncFromPonche(array $rows): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO campaigns (id, name, description, active)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                description = VALUES(description),
                active = VALUES(active)
        ");

        foreach ($rows as $row) {
            $stmt->execute([
                $row['id'],
                $row['name'],
                $row['description'] ?? '',
                (int) ($row['is_active'] ?? 1)
            ]);
        }
    }

    private function normalizePoncheRows(array $rows): array
    {
        return array_map([$this, 'normalizePoncheRow'], $rows);
    }

    private function normalizePoncheRow(array $row): array
    {
        $row['active'] = (int) ($row['is_active'] ?? 1);
        return $row;
    }
}
