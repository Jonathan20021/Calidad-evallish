<?php
/**
 * Migration: Form Template Multiple Campaigns
 * 
 * This script migrates the form_templates table from a single campaign_id
 * to a many-to-many relationship using a junction table.
 * 
 * Steps:
 * 1. Create form_template_campaigns junction table
 * 2. Migrate existing campaign_id data to junction table
 * 3. Drop campaign_id column from form_templates
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();

    echo "Starting migration: Form Template Multiple Campaigns\n";
    echo "====================================================\n\n";

    // Step 1: Create junction table
    echo "Step 1: Creating form_template_campaigns junction table...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS form_template_campaigns (
            template_id INT NOT NULL,
            campaign_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (template_id, campaign_id),
            FOREIGN KEY (template_id) REFERENCES form_templates(id) ON DELETE CASCADE,
            FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Junction table created successfully\n\n";

    // Step 2: Migrate existing data
    echo "Step 2: Migrating existing campaign assignments...\n";

    // Check if campaign_id column still exists
    $stmt = $db->query("SHOW COLUMNS FROM form_templates LIKE 'campaign_id'");
    $columnExists = $stmt->fetch();

    if ($columnExists) {
        // Get all existing templates with their campaign_id
        $stmt = $db->query("SELECT id, campaign_id FROM form_templates WHERE campaign_id IS NOT NULL");
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $migratedCount = 0;
        foreach ($templates as $template) {
            // Insert into junction table
            $insertStmt = $db->prepare("
                INSERT IGNORE INTO form_template_campaigns (template_id, campaign_id)
                VALUES (?, ?)
            ");
            $insertStmt->execute([$template['id'], $template['campaign_id']]);
            $migratedCount++;
        }

        echo "✓ Migrated {$migratedCount} campaign assignments\n\n";

        // Step 3: Drop campaign_id column
        echo "Step 3: Removing campaign_id column from form_templates...\n";

        // First, drop the foreign key constraint
        $db->exec("ALTER TABLE form_templates DROP FOREIGN KEY form_templates_ibfk_1");

        // Then drop the column
        $db->exec("ALTER TABLE form_templates DROP COLUMN campaign_id");

        echo "✓ Column removed successfully\n\n";
    } else {
        echo "⚠ campaign_id column already removed, skipping migration\n\n";
    }

    echo "====================================================\n";
    echo "Migration completed successfully!\n";
    echo "====================================================\n";

} catch (PDOException $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
