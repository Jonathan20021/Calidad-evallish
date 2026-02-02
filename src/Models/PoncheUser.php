<?php

namespace App\Models;

use App\Config\PoncheDatabase;
use PDO;

class PoncheUser
{
    private $db;

    public function __construct()
    {
        $this->db = PoncheDatabase::getInstance()->getConnection();
    }

    public function findByUsername(string $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByUsernameAny(string $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getMapByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            SELECT id, username, full_name, role, is_active
            FROM users
            WHERE id IN ($placeholders)
        ");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }
        return $map;
    }

    public function getByRoles(array $roles, bool $activeOnly = true): array
    {
        if (empty($roles)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $normalizedRoles = array_map([$this, 'normalizeRoleValue'], $roles);
        $activeClause = $activeOnly ? ' AND is_active = 1' : '';
        $stmt = $this->db->prepare("
            SELECT id, username, full_name, role, is_active, created_at
            FROM users
            WHERE UPPER(role) IN ($placeholders)$activeClause
        ");
        $stmt->execute($normalizedRoles);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, full_name, password, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['username'],
            $data['full_name'],
            $data['password'],
            $this->normalizeRoleValue($data['role'] ?? 'AGENT'),
            $data['is_active'] ?? 1
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET full_name = ?, role = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['full_name'],
            $this->normalizeRoleValue($data['role'] ?? 'AGENT'),
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$password, $id]);
    }

    public function setActive(int $id, int $active): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([(int) $active, $id]);
    }

    public function verifyPassword(string $password, string $stored): bool
    {
        if ($this->looksLikeHash($stored)) {
            return password_verify($password, $stored);
        }
        return hash_equals($stored, $password);
    }

    public function toSessionUser(array $user): array
    {
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => strtolower($user['role']),
            'active' => (int) ($user['is_active'] ?? 1)
        ];
    }

    private function looksLikeHash(string $value): bool
    {
        if (str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$') || str_starts_with($value, '$2b$')) {
            return true;
        }
        if (str_starts_with($value, '$argon2i$') || str_starts_with($value, '$argon2id$')) {
            return true;
        }
        return false;
    }

    private function normalizeRoleValue(string $role): string
    {
        return strtoupper(trim($role));
    }
}
