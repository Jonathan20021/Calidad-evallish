<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Config\Database;
use App\Config\PoncheDatabase;

Config::init();

echo "=== Test de Conexión a Base de Datos ===\n\n";

// Test Database Principal
try {
    echo "Conectando a Base de Datos Principal...\n";
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT DATABASE() as db, USER() as user, VERSION() as version");
    $info = $stmt->fetch();
    echo "✓ Conectado a: {$info['db']}\n";
    echo "✓ Usuario: {$info['user']}\n";
    echo "✓ MySQL Version: {$info['version']}\n\n";
} catch (Exception $e) {
    echo "✗ Error en DB Principal: " . $e->getMessage() . "\n\n";
}

// Test PoncheDatabase
try {
    echo "Conectando a Base de Datos Ponche...\n";
    $poncheDb = PoncheDatabase::getInstance();
    $conn = $poncheDb->getConnection();
    $stmt = $conn->query("SELECT DATABASE() as db, USER() as user");
    $info = $stmt->fetch();
    echo "✓ Conectado a: {$info['db']}\n";
    echo "✓ Usuario: {$info['user']}\n\n";
} catch (Exception $e) {
    echo "✗ Error en DB Ponche: " . $e->getMessage() . "\n\n";
}

echo "=== Test Completado ===\n";
