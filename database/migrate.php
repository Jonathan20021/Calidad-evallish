<?php

require_once __DIR__ . '/../public/index.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');

    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }

    echo "OK: Database schema created successfully!\n";
    echo "OK: Default admin user created (username: admin, password: admin123)\n";
    echo "OK: Sample campaigns inserted\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
