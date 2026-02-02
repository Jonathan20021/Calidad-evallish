<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
function section(string $title): void
{
    echo "\n=== {$title} ===\n";
}

try {
    $db = Database::getInstance()->getConnection();

    section('Conexión');
    $dbName = $db->query('SELECT DATABASE()')->fetchColumn();
    echo "DB activa: {$dbName}\n";

    section('Tablas requeridas');
    $requiredTables = [
        'corporate_clients',
        'users',
        'campaigns',
        'calls',
        'evaluations',
        'form_templates',
        'form_fields',
        'client_campaigns',
        'client_portal_settings',
        'call_ai_analytics',
        'ai_evaluation_criteria'
    ];

    $stmt = $db->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (" . implode(',', array_fill(0, count($requiredTables), '?')) . ")");
    $stmt->execute($requiredTables);
    $found = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_values(array_diff($requiredTables, $found));

    echo "Encontradas: " . implode(', ', $found) . "\n";
    echo $missing ? ("Faltan: " . implode(', ', $missing) . "\n") : "No faltan tablas.\n";

    section('FK en calls.agent_id -> users');
    $stmt = $db->prepare("\n        SELECT CONSTRAINT_NAME\n        FROM information_schema.KEY_COLUMN_USAGE\n        WHERE TABLE_SCHEMA = DATABASE()\n          AND TABLE_NAME = 'calls'\n          AND COLUMN_NAME = 'agent_id'\n          AND REFERENCED_TABLE_NAME = 'users'\n    ");
    $stmt->execute();
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($constraints)) {
        echo "No hay FK en calls.agent_id hacia users.\n";
    } else {
        echo "FK encontrada(s): " . implode(', ', $constraints) . "\n";
    }

    section('Agentes de llamadas sin usuario');
    $stmt = $db->query("\n        SELECT c.agent_id, COUNT(*) AS total\n        FROM calls c\n        LEFT JOIN users u ON u.id = c.agent_id\n        WHERE u.id IS NULL\n        GROUP BY c.agent_id\n        ORDER BY total DESC\n        LIMIT 20\n    ");
    $rows = $stmt->fetchAll();
    if (empty($rows)) {
        echo "Todos los agent_id de calls existen en users.\n";
    } else {
        foreach ($rows as $row) {
            echo "agent_id={$row['agent_id']} => {$row['total']} llamadas\n";
        }
    }

    section('SHOW CREATE TABLE calls');
    $stmt = $db->query('SHOW CREATE TABLE calls');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo $row['Create Table'] . "\n";
    }

    echo "\nOK: verificación completada.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
