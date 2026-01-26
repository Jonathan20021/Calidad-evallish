<?php

namespace App\Models;

use App\Config\Database;

class ClientPortalSettings
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByClientId($clientId)
    {
        $stmt = $this->db->prepare("SELECT * FROM client_portal_settings WHERE client_id = ? LIMIT 1");
        $stmt->execute([$clientId]);
        return $stmt->fetch();
    }

    public function upsert($clientId, array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO client_portal_settings
                (client_id, show_calls, show_evaluations, show_ai_summary, show_recordings, show_agent_scores, metrics_json)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                show_calls = VALUES(show_calls),
                show_evaluations = VALUES(show_evaluations),
                show_ai_summary = VALUES(show_ai_summary),
                show_recordings = VALUES(show_recordings),
                show_agent_scores = VALUES(show_agent_scores),
                metrics_json = VALUES(metrics_json)
        ");
        $stmt->execute([
            $clientId,
            $data['show_calls'] ?? 1,
            $data['show_evaluations'] ?? 1,
            $data['show_ai_summary'] ?? 0,
            $data['show_recordings'] ?? 0,
            $data['show_agent_scores'] ?? 1,
            $data['metrics_json'] ?? null
        ]);
    }
}
