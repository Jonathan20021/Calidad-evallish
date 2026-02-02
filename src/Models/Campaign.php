<?php

namespace App\Models;

use App\Config\Database;
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
        $stmt = $this->db->query("SELECT * FROM campaigns ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getActive()
    {
        $stmt = $this->db->query("SELECT * FROM campaigns WHERE active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
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
}
