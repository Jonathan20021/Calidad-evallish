<?php

/**
 * Migración: Crear tabla user_permissions
 * 
 * Esta migración crea la tabla para gestionar permisos individuales
 * de usuarios de ponche (excepto agents)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Migración: Crear tabla user_permissions ===\n\n";
    
    // Verificar si la tabla ya existe
    $stmt = $db->query("SHOW TABLES LIKE 'user_permissions'");
    if ($stmt->fetch()) {
        echo "La tabla 'user_permissions' ya existe. No se requiere migración.\n";
        exit(0);
    }
    
    // Crear la tabla
    echo "Creando tabla 'user_permissions'...\n";
    $db->exec("
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY uidx_user_permissions (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✓ Tabla 'user_permissions' creada exitosamente.\n\n";
    echo "=== Migración completada ===\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
