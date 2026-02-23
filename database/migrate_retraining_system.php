<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Config\Database;

Config::init();

try {
    $db = Database::getInstance()->getConnection();

    $db->exec("\n        CREATE TABLE IF NOT EXISTS qa_retrainings (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            campaign_id INT NOT NULL,\n            agent_id INT NOT NULL,\n            evaluation_id INT NULL,\n            created_by INT NOT NULL,\n            supervisor_id INT NULL,\n            status ENUM('assigned', 'in_progress', 'approved', 'failed', 'active_in_production') NOT NULL DEFAULT 'assigned',\n            progress_percent DECIMAL(5,2) DEFAULT 0.00,\n            due_date DATE NULL,\n            approved_by INT NULL,\n            approved_at DATETIME NULL,\n            activation_at DATETIME NULL,\n            reminder_sent_at DATETIME NULL,\n            reminder_count INT NOT NULL DEFAULT 0,\n            reinforcement_required TINYINT(1) NOT NULL DEFAULT 0,\n            fail_count INT NOT NULL DEFAULT 0,\n            notes TEXT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,\n            FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,\n            FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE SET NULL,\n            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,\n            FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL,\n            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n    ");

    $db->exec("\n        CREATE TABLE IF NOT EXISTS qa_retraining_modules (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            retraining_id INT NOT NULL,\n            title VARCHAR(180) NOT NULL,\n            lesson_text TEXT NULL,\n            detected_error VARCHAR(255) NULL,\n            sequence_order INT NOT NULL DEFAULT 1,\n            pass_score DECIMAL(5,2) NOT NULL DEFAULT 80.00,\n            quiz_question TEXT NULL,\n            quiz_type ENUM('text', 'mcq') NOT NULL DEFAULT 'text',\n            options_json TEXT NULL,\n            correct_answer TEXT NULL,\n            is_required TINYINT(1) NOT NULL DEFAULT 1,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            FOREIGN KEY (retraining_id) REFERENCES qa_retrainings(id) ON DELETE CASCADE\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n    ");

    $db->exec("\n        CREATE TABLE IF NOT EXISTS qa_retraining_progress (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            module_id INT NOT NULL,\n            retraining_id INT NOT NULL,\n            agent_id INT NOT NULL,\n            status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',\n            score DECIMAL(5,2) NULL,\n            answer_text TEXT NULL,\n            attempts INT NOT NULL DEFAULT 0,\n            completed_at DATETIME NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            UNIQUE KEY uniq_retraining_module_agent (module_id, agent_id),\n            FOREIGN KEY (module_id) REFERENCES qa_retraining_modules(id) ON DELETE CASCADE,\n            FOREIGN KEY (retraining_id) REFERENCES qa_retrainings(id) ON DELETE CASCADE,\n            FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n    ");

    echo "OK: QA retraining tables created/verified.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
