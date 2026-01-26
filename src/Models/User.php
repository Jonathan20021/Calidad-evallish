<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByUsernameAny($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT id, username, full_name, role, active, client_id, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getAllFiltered($filters)
    {
        $sql = "SELECT id, username, full_name, role, active, client_id, created_at FROM users";
        $conditions = [];
        $params = [];

        if (!empty($filters['role'])) {
            $conditions[] = "role = ?";
            $params[] = $filters['role'];
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $conditions[] = "active = ?";
            $params[] = (int) $filters['status'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = "(username LIKE ? OR full_name LIKE ?)";
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByRole($role)
    {
        $stmt = $this->db->prepare("SELECT id, username, full_name, role FROM users WHERE role = ? AND active = 1");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, full_name, role, client_id, active) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['full_name'],
            $data['role'],
            $data['client_id'] ?? null,
            $data['active'] ?? 1
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, full_name = ?, active = ? WHERE id = ?");
        return $stmt->execute([
            $data['username'],
            $data['full_name'],
            $data['active'] ?? 1,
            $id
        ]);
    }

    public function updateAdmin($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, full_name = ?, role = ?, client_id = ?, active = ? WHERE id = ?");
        return $stmt->execute([
            $data['username'],
            $data['full_name'],
            $data['role'],
            $data['client_id'],
            $data['active'] ?? 1,
            $id
        ]);
    }

    public function setActive($id, $active)
    {
        $stmt = $this->db->prepare("UPDATE users SET active = ? WHERE id = ?");
        return $stmt->execute([
            (int) $active,
            $id
        ]);
    }

    public function updatePassword($id, $password)
    {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([
            password_hash($password, PASSWORD_DEFAULT),
            $id
        ]);
    }

    public function getByClientId($clientId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE client_id = ? AND role = 'client' ORDER BY id ASC");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
