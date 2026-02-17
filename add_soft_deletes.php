<?php
require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    $tables = ['evaluations', 'form_templates', 'campaigns'];

    foreach ($tables as $table) {
        echo "Processing table: $table\n";

        // Check for updated_at
        $stmt = $db->query("SHOW COLUMNS FROM $table LIKE 'updated_at'");
        if (!$stmt->fetch()) {
            $db->exec("ALTER TABLE $table ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "  Added updated_at to $table table.\n";
        }

        // Check for deleted_at
        $stmt = $db->query("SHOW COLUMNS FROM $table LIKE 'deleted_at'");
        if (!$stmt->fetch()) {
            $db->exec("ALTER TABLE $table ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
            echo "  Added deleted_at to $table table.\n";
        } else {
            echo "  deleted_at already exists in $table table.\n";
        }
    }

    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
