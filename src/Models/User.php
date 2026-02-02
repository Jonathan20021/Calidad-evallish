<?php

namespace App\Models;

use App\Config\Database;
use App\Models\PoncheUser;
use PDO;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureColumns();
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
        $stmt = $this->db->query("SELECT id, username, full_name, role, active, client_id, source, external_id, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getAllFiltered($filters)
    {
        $roleFilter = $filters['role'] ?? '';
        $includePonche = $roleFilter === '' || in_array($roleFilter, ['agent', 'qa'], true);
        if ($includePonche) {
            $roles = $roleFilter === '' ? ['agent', 'qa'] : [$roleFilter];
            $this->syncPoncheUsersByRoles($roles, false);
        }

        $sql = "SELECT id, username, full_name, role, active, client_id, source, external_id, created_at FROM users";
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
        $localRows = $stmt->fetchAll();

        $merged = array_map(function ($row) {
            $row['can_manage'] = ($row['source'] ?? 'quality') === 'quality';
            return $row;
        }, $localRows);

        usort($merged, function ($a, $b) {
            $dateA = $a['created_at'] ?? '';
            $dateB = $b['created_at'] ?? '';
            if ($dateA === $dateB) {
                return 0;
            }
            return $dateA < $dateB ? 1 : -1;
        });

        return $merged;
    }

    public function getByRole($role)
    {
        $normalizedRole = strtolower($role);
        if (in_array($normalizedRole, ['agent', 'qa'], true)) {
            $this->syncPoncheUsersByRoles([$normalizedRole], true);
            $stmt = $this->db->prepare("SELECT id, username, full_name, role, active, source, external_id FROM users WHERE role = ? AND active = 1 ORDER BY full_name ASC");
            $stmt->execute([$normalizedRole]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("SELECT id, username, full_name, role, active FROM users WHERE role = ? AND active = 1");
        $stmt->execute([$normalizedRole]);
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

    public function getMapByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        })));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, username, full_name, role, active, source, external_id FROM users WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }
        return $map;
    }

    public function syncFromPoncheUser(array $poncheUser): int
    {
        $externalId = (int) ($poncheUser['id'] ?? 0);
        if ($externalId <= 0) {
            return 0;
        }

        $username = trim($poncheUser['username'] ?? '') ?: ('ponche_' . $externalId);
        $fullName = trim($poncheUser['full_name'] ?? '') ?: $username;
        $role = strtolower(trim($poncheUser['role'] ?? 'agent'));
        $active = (int) ($poncheUser['is_active'] ?? 1);

        $existing = $this->findBySourceExternalId('ponche', $externalId);
        if ($existing) {
            $this->updateFromSync((int) $existing['id'], [
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role,
                'active' => $active,
                'external_id' => $externalId,
                'source' => 'ponche'
            ]);
            return (int) $existing['id'];
        }

        $byUsername = $this->findByUsernameAny($username);
        if ($byUsername) {
            $byUsernameId = (int) $byUsername['id'];
            $byUsernameSource = $byUsername['source'] ?? 'quality';
            $byUsernameRole = strtolower($byUsername['role'] ?? '');

            if ($byUsernameSource === 'quality' && in_array($byUsernameRole, ['agent', 'qa'], true)) {
                $this->updateFromSync($byUsernameId, [
                    'username' => $username,
                    'full_name' => $fullName,
                    'role' => $role,
                    'active' => $active,
                    'external_id' => $externalId
                ]);
                return $byUsernameId;
            }

            $username = $this->generatePoncheUsername($username, $externalId);
        }

        $passwordHash = password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, full_name, role, client_id, active, source, external_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $username,
            $passwordHash,
            $fullName,
            $role,
            null,
            $active,
            'ponche',
            $externalId
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function syncPoncheUsersByRoles(array $roles, bool $activeOnly = true): array
    {
        $roles = array_values(array_unique(array_map('strtolower', $roles)));
        if (empty($roles)) {
            return [];
        }

        $poncheUser = new PoncheUser();
        $rows = $poncheUser->getByRoles($roles, $activeOnly);
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $this->syncFromPoncheUser($row);
        }
        return $ids;
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    private function findBySourceExternalId(string $source, int $externalId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE source = ? AND external_id = ? LIMIT 1");
        $stmt->execute([$source, $externalId]);
        return $stmt->fetch();
    }

    private function updateFromSync(int $id, array $data): void
    {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, full_name = ?, role = ?, active = ?, source = COALESCE(?, source), external_id = COALESCE(?, external_id) WHERE id = ?");
        $stmt->execute([
            $data['username'],
            $data['full_name'],
            $data['role'],
            $data['active'],
            $data['source'] ?? null,
            $data['external_id'] ?? null,
            $id
        ]);
    }

    private function generatePoncheUsername(string $username, int $externalId): string
    {
        $candidate = $username . '_ponche_' . $externalId;
        $existing = $this->findByUsernameAny($candidate);
        if (!$existing) {
            return $candidate;
        }
        return $candidate . '_' . bin2hex(random_bytes(2));
    }

    private function ensureColumns(): void
    {
        $this->ensureColumnExists('source', "ENUM('quality','ponche') NOT NULL DEFAULT 'quality'");
        $this->ensureColumnExists('external_id', 'INT NULL');
        $this->ensureIndexExists('uidx_users_source_external', ['source', 'external_id'], true);
    }

    private function ensureColumnExists(string $column, string $definition): void
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM users LIKE ?");
        $stmt->execute([$column]);
        if (!$stmt->fetch()) {
            $this->db->exec("ALTER TABLE users ADD COLUMN $column $definition");
        }
    }

    private function ensureIndexExists(string $indexName, array $columns, bool $unique = false): void
    {
        $stmt = $this->db->prepare("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = ? LIMIT 1");
        $stmt->execute([$indexName]);
        if ($stmt->fetch()) {
            return;
        }
        $cols = implode(',', array_map(function ($col) {
            return "`{$col}`";
        }, $columns));
        $uniqueSql = $unique ? 'UNIQUE' : '';
        $this->db->exec("CREATE $uniqueSql INDEX `$indexName` ON users ($cols)");
    }
}
