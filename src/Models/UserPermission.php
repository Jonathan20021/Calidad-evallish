<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class UserPermission
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    public function getByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM user_permissions WHERE user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'user_id' => (int) $row['user_id'],
            'can_view_users' => (int) ($row['can_view_users'] ?? 0),
            'can_create_users' => (int) ($row['can_create_users'] ?? 0),
            'can_view_clients' => (int) ($row['can_view_clients'] ?? 0),
            'can_manage_clients' => (int) ($row['can_manage_clients'] ?? 0),
            'can_view_campaigns' => (int) ($row['can_view_campaigns'] ?? 0),
            'can_manage_campaigns' => (int) ($row['can_manage_campaigns'] ?? 0),
            'can_view_evaluations' => (int) ($row['can_view_evaluations'] ?? 0),
            'can_create_evaluations' => (int) ($row['can_create_evaluations'] ?? 0),
            'can_view_reports' => (int) ($row['can_view_reports'] ?? 0),
            'can_manage_settings' => (int) ($row['can_manage_settings'] ?? 0),
            'can_view_training' => (int) ($row['can_view_training'] ?? 0),
            'can_manage_training' => (int) ($row['can_manage_training'] ?? 0),
            'can_view_agents' => (int) ($row['can_view_agents'] ?? 0),
            'can_manage_agents' => (int) ($row['can_manage_agents'] ?? 0),
            'can_view_forms' => (int) ($row['can_view_forms'] ?? 0),
            'can_manage_forms' => (int) ($row['can_manage_forms'] ?? 0),
            'can_view_ai_criteria' => (int) ($row['can_view_ai_criteria'] ?? 0),
            'can_manage_ai_criteria' => (int) ($row['can_manage_ai_criteria'] ?? 0),
            'can_view_calls' => (int) ($row['can_view_calls'] ?? 0),
            'can_manage_calls' => (int) ($row['can_manage_calls'] ?? 0),
            'can_view_top_evaluators' => (int) ($row['can_view_top_evaluators'] ?? 0),
        ];
    }

    public function createOrUpdate(int $userId, array $permissions): bool
    {
        $existing = $this->getByUserId($userId);

        if ($existing) {
            return $this->update($userId, $permissions);
        }

        return $this->create($userId, $permissions);
    }

    private function create(int $userId, array $permissions): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO user_permissions (
                user_id, 
                can_view_users, 
                can_create_users, 
                can_view_clients, 
                can_manage_clients,
                can_view_campaigns,
                can_manage_campaigns,
                can_view_evaluations,
                can_create_evaluations,
                can_view_reports,
                can_manage_settings,
                can_view_training,
                can_manage_training,
                can_view_agents,
                can_manage_agents,
                can_view_forms,
                can_manage_forms,
                can_view_ai_criteria,
                can_manage_ai_criteria,
                can_view_calls,
                can_manage_calls,
                can_view_top_evaluators
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        return $stmt->execute([
            $userId,
            (int) ($permissions['can_view_users'] ?? 0),
            (int) ($permissions['can_create_users'] ?? 0),
            (int) ($permissions['can_view_clients'] ?? 0),
            (int) ($permissions['can_manage_clients'] ?? 0),
            (int) ($permissions['can_view_campaigns'] ?? 0),
            (int) ($permissions['can_manage_campaigns'] ?? 0),
            (int) ($permissions['can_view_evaluations'] ?? 0),
            (int) ($permissions['can_create_evaluations'] ?? 0),
            (int) ($permissions['can_view_reports'] ?? 0),
            (int) ($permissions['can_manage_settings'] ?? 0),
            (int) ($permissions['can_view_training'] ?? 0),
            (int) ($permissions['can_manage_training'] ?? 0),
            (int) ($permissions['can_view_agents'] ?? 0),
            (int) ($permissions['can_manage_agents'] ?? 0),
            (int) ($permissions['can_view_forms'] ?? 0),
            (int) ($permissions['can_manage_forms'] ?? 0),
            (int) ($permissions['can_view_ai_criteria'] ?? 0),
            (int) ($permissions['can_manage_ai_criteria'] ?? 0),
            (int) ($permissions['can_view_calls'] ?? 0),
            (int) ($permissions['can_manage_calls'] ?? 0),
            (int) ($permissions['can_view_top_evaluators'] ?? 0),
        ]);
    }

    private function update(int $userId, array $permissions): bool
    {
        $stmt = $this->db->prepare('
            UPDATE user_permissions SET
                can_view_users = ?,
                can_create_users = ?,
                can_view_clients = ?,
                can_manage_clients = ?,
                can_view_campaigns = ?,
                can_manage_campaigns = ?,
                can_view_evaluations = ?,
                can_create_evaluations = ?,
                can_view_reports = ?,
                can_manage_settings = ?,
                can_view_training = ?,
                can_manage_training = ?,
                can_view_agents = ?,
                can_manage_agents = ?,
                can_view_forms = ?,
                can_manage_forms = ?,
                can_view_ai_criteria = ?,
                can_manage_ai_criteria = ?,
                can_view_calls = ?,
                can_manage_calls = ?,
                can_view_top_evaluators = ?
            WHERE user_id = ?
        ');

        return $stmt->execute([
            (int) ($permissions['can_view_users'] ?? 0),
            (int) ($permissions['can_create_users'] ?? 0),
            (int) ($permissions['can_view_clients'] ?? 0),
            (int) ($permissions['can_manage_clients'] ?? 0),
            (int) ($permissions['can_view_campaigns'] ?? 0),
            (int) ($permissions['can_manage_campaigns'] ?? 0),
            (int) ($permissions['can_view_evaluations'] ?? 0),
            (int) ($permissions['can_create_evaluations'] ?? 0),
            (int) ($permissions['can_view_reports'] ?? 0),
            (int) ($permissions['can_manage_settings'] ?? 0),
            (int) ($permissions['can_view_training'] ?? 0),
            (int) ($permissions['can_manage_training'] ?? 0),
            (int) ($permissions['can_view_agents'] ?? 0),
            (int) ($permissions['can_manage_agents'] ?? 0),
            (int) ($permissions['can_view_forms'] ?? 0),
            (int) ($permissions['can_manage_forms'] ?? 0),
            (int) ($permissions['can_view_ai_criteria'] ?? 0),
            (int) ($permissions['can_manage_ai_criteria'] ?? 0),
            (int) ($permissions['can_view_calls'] ?? 0),
            (int) ($permissions['can_manage_calls'] ?? 0),
            (int) ($permissions['can_view_top_evaluators'] ?? 0),
            $userId
        ]);
    }

    public function delete(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM user_permissions WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }

    private function ensureTable(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'user_permissions'");
        if (!$stmt->fetch()) {
            $this->db->exec("
                CREATE TABLE user_permissions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    can_view_users TINYINT(1) DEFAULT 0,
                    can_create_users TINYINT(1) DEFAULT 0,
                    can_view_clients TINYINT(1) DEFAULT 0,
                    can_manage_clients TINYINT(1) DEFAULT 0,
                    can_view_campaigns TINYINT(1) DEFAULT 0,
                    can_manage_campaigns TINYINT(1) DEFAULT 0,
                    can_view_evaluations TINYINT(1) DEFAULT 0,
                    can_create_evaluations TINYINT(1) DEFAULT 0,
                    can_view_reports TINYINT(1) DEFAULT 0,
                    can_manage_settings TINYINT(1) DEFAULT 0,
                    can_view_training TINYINT(1) DEFAULT 0,
                    can_manage_training TINYINT(1) DEFAULT 0,
                    can_view_agents TINYINT(1) DEFAULT 0,
                    can_manage_agents TINYINT(1) DEFAULT 0,
                    can_view_forms TINYINT(1) DEFAULT 0,
                    can_manage_forms TINYINT(1) DEFAULT 0,
                    can_view_ai_criteria TINYINT(1) DEFAULT 0,
                    can_manage_ai_criteria TINYINT(1) DEFAULT 0,
                    can_view_calls TINYINT(1) DEFAULT 0,
                    can_manage_calls TINYINT(1) DEFAULT 0,
                    can_view_top_evaluators TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    UNIQUE KEY uidx_user_permissions (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } else {
            // Check if column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM user_permissions LIKE 'can_view_top_evaluators'");
            if (!$stmt->fetch()) {
                $this->db->exec("ALTER TABLE user_permissions ADD COLUMN can_view_top_evaluators TINYINT(1) DEFAULT 0 AFTER can_manage_calls");
            }
        }
    }
}
