<?php

namespace App\Models;

use App\Config\Database;

class CorporateClient
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT cc.*, u.username as portal_username, u.full_name as portal_user_name, u.active as portal_user_active
            FROM corporate_clients cc
            LEFT JOIN users u ON u.client_id = cc.id AND u.role = 'client'
            ORDER BY cc.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM corporate_clients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO corporate_clients (name, industry, contact_name, contact_email, active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['industry'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_email'] ?? null,
            $data['active'] ?? 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE corporate_clients
            SET name = ?, industry = ?, contact_name = ?, contact_email = ?, active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['industry'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_email'] ?? null,
            $data['active'] ?? 1,
            $id
        ]);
    }
}
