<?php
require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    $sql = "ALTER TABLE evaluations ADD COLUMN evaluation_type ENUM('remota', 'presencial', 'llamada', 'chat') NULL AFTER form_template_id";
    $db->exec($sql);
    echo "Migration successful: evaluation_type column added to evaluations table.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
