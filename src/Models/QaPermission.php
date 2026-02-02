<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class QaPermission
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get(): array
    {
        $stmt = $this->db->query('SELECT * FROM qa_permissions LIMIT 1');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if (!$row) {
            $this->db->exec('INSERT INTO qa_permissions (can_view_users, can_create_users, can_view_clients, can_manage_clients) VALUES (0, 0, 0, 0)');
            $stmt = $this->db->query('SELECT * FROM qa_permissions LIMIT 1');
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        }

        return [
            'can_view_users' => (int) ($row['can_view_users'] ?? 0),
            'can_create_users' => (int) ($row['can_create_users'] ?? 0),
            'can_view_clients' => (int) ($row['can_view_clients'] ?? 0),
            'can_manage_clients' => (int) ($row['can_manage_clients'] ?? 0)
        ];
    }

    public function update(array $data): bool
    {
        $id = $this->getId();
        if ($id === null) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE qa_permissions SET can_view_users = ?, can_create_users = ?, can_view_clients = ?, can_manage_clients = ? WHERE id = ?');
        return $stmt->execute([
            (int) ($data['can_view_users'] ?? 0),
            (int) ($data['can_create_users'] ?? 0),
            (int) ($data['can_view_clients'] ?? 0),
            (int) ($data['can_manage_clients'] ?? 0),
            $id
        ]);
    }

    private function getId(): ?int
    {
        $stmt = $this->db->query('SELECT id FROM qa_permissions LIMIT 1');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        if (!$row) {
            $this->db->exec('INSERT INTO qa_permissions (can_view_users, can_create_users, can_view_clients, can_manage_clients) VALUES (0, 0, 0, 0)');
            $stmt = $this->db->query('SELECT id FROM qa_permissions LIMIT 1');
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        }

        return $row ? (int) $row['id'] : null;
    }
}
