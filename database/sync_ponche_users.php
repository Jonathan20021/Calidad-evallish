<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Models\PoncheUser;
use App\Models\User;
function section(string $title): void
{
    echo "\n=== {$title} ===\n";
}

try {
    $db = Database::getInstance()->getConnection();
    $userModel = new User();
    $poncheUser = new PoncheUser();

    section('Sincronizando usuarios de Ponche');
    $rows = $poncheUser->getByRoles(['agent', 'qa'], false);
    $count = 0;
    foreach ($rows as $row) {
        $localId = $userModel->syncFromPoncheUser($row);
        if ($localId > 0) {
            $count++;
        }
    }
    echo "Usuarios sincronizados: {$count}\n";

    section('Remapeando IDs en tablas');
    $updates = [
        "UPDATE calls c JOIN users u ON u.external_id = c.agent_id AND u.source = 'ponche' SET c.agent_id = u.id WHERE c.agent_id <> u.id",
        "UPDATE evaluations e JOIN users u ON u.external_id = e.agent_id AND u.source = 'ponche' SET e.agent_id = u.id WHERE e.agent_id <> u.id",
        "UPDATE evaluations e JOIN users u ON u.external_id = e.qa_id AND u.source = 'ponche' SET e.qa_id = u.id WHERE e.qa_id <> u.id",
        "UPDATE training_roleplays tr JOIN users u ON u.external_id = tr.agent_id AND u.source = 'ponche' SET tr.agent_id = u.id WHERE tr.agent_id <> u.id",
        "UPDATE training_roleplays tr JOIN users u ON u.external_id = tr.qa_id AND u.source = 'ponche' SET tr.qa_id = u.id WHERE tr.qa_id <> u.id",
        "UPDATE training_roleplay_coach_notes trcn JOIN users u ON u.external_id = trcn.qa_id AND u.source = 'ponche' SET trcn.qa_id = u.id WHERE trcn.qa_id <> u.id",
        "UPDATE training_roleplay_feedback trf JOIN users u ON u.external_id = trf.approved_by AND u.source = 'ponche' SET trf.approved_by = u.id WHERE trf.approved_by <> u.id",
        "UPDATE training_exams te JOIN users u ON u.external_id = te.agent_id AND u.source = 'ponche' SET te.agent_id = u.id WHERE te.agent_id <> u.id",
        "UPDATE training_exams te JOIN users u ON u.external_id = te.qa_id AND u.source = 'ponche' SET te.qa_id = u.id WHERE te.qa_id <> u.id",
        "UPDATE training_scripts ts JOIN users u ON u.external_id = ts.created_by AND u.source = 'ponche' SET ts.created_by = u.id WHERE ts.created_by <> u.id",
        "UPDATE training_rubrics tr JOIN users u ON u.external_id = tr.created_by AND u.source = 'ponche' SET tr.created_by = u.id WHERE tr.created_by <> u.id",
        "UPDATE training_notifications tn JOIN users u ON u.external_id = tn.agent_id AND u.source = 'ponche' SET tn.agent_id = u.id WHERE tn.agent_id <> u.id",
        "UPDATE training_notifications tn JOIN users u ON u.external_id = tn.qa_id AND u.source = 'ponche' SET tn.qa_id = u.id WHERE tn.qa_id <> u.id"
    ];

    foreach ($updates as $sql) {
        $affected = $db->exec($sql);
        echo "OK: {$affected} filas actualizadas.\n";
    }

    section('FK calls.agent_id');
    $stmt = $db->prepare("\n        SELECT CONSTRAINT_NAME\n        FROM information_schema.KEY_COLUMN_USAGE\n        WHERE TABLE_SCHEMA = DATABASE()\n          AND TABLE_NAME = 'calls'\n          AND COLUMN_NAME = 'agent_id'\n          AND REFERENCED_TABLE_NAME = 'users'\n    ");
    $stmt->execute();
    $fk = $stmt->fetchColumn();
    if ($fk) {
        echo "FK existente: {$fk}\n";
    } else {
        $db->exec("ALTER TABLE calls ADD CONSTRAINT calls_agent_fk FOREIGN KEY (agent_id) REFERENCES users(id)");
        echo "FK creada: calls_agent_fk\n";
    }

    echo "\nOK: sincronización completada.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
