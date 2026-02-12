<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Updating user_permissions table ===\n";

    $columns = [
        'can_view_agents' => 'TINYINT(1) DEFAULT 0',
        'can_manage_agents' => 'TINYINT(1) DEFAULT 0',
        'can_view_forms' => 'TINYINT(1) DEFAULT 0',
        'can_manage_forms' => 'TINYINT(1) DEFAULT 0',
        'can_view_ai_criteria' => 'TINYINT(1) DEFAULT 0',
        'can_manage_ai_criteria' => 'TINYINT(1) DEFAULT 0',
        'can_view_calls' => 'TINYINT(1) DEFAULT 0',
        'can_manage_calls' => 'TINYINT(1) DEFAULT 0'
    ];

    foreach ($columns as $column => $definition) {
        $stmt = $db->query("SHOW COLUMNS FROM user_permissions LIKE '$column'");
        if (!$stmt->fetch()) {
            echo "Adding column $column...\n";
            $db->exec("ALTER TABLE user_permissions ADD COLUMN $column $definition AFTER can_manage_training");
        } else {
            echo "Column $column already exists.\n";
        }
    }

    echo "=== Update completed ===\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
