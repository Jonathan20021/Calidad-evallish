<?php

namespace App\Models;

use App\Config\Database;
use App\Models\User;
use PDO;

class QaRetraining
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("\n            INSERT INTO qa_retrainings\n                (campaign_id, agent_id, evaluation_id, created_by, supervisor_id, status, due_date, reinforcement_required, notes)\n            VALUES\n                (?, ?, ?, ?, ?, ?, ?, ?, ?)\n        ");

        return $stmt->execute([
            $data['campaign_id'],
            $data['agent_id'],
            $data['evaluation_id'] ?? null,
            $data['created_by'],
            $data['supervisor_id'] ?? null,
            $data['status'] ?? 'assigned',
            $data['due_date'] ?? null,
            $data['reinforcement_required'] ?? 0,
            $data['notes'] ?? null
        ]);
    }

    public function getLastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("\n            SELECT qr.*,\n                   c.name AS campaign_name\n            FROM qa_retrainings qr\n            LEFT JOIN campaigns c ON qr.campaign_id = c.id\n            WHERE qr.id = ?\n            LIMIT 1\n        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $this->attachUserNames($row);
    }

    public function findActiveByCampaignAndAgent(int $campaignId, int $agentId)
    {
        $stmt = $this->db->prepare("\n            SELECT id\n            FROM qa_retrainings\n            WHERE campaign_id = ?\n              AND agent_id = ?\n              AND status IN ('assigned', 'in_progress', 'approved')\n            ORDER BY created_at DESC\n            LIMIT 1\n        ");
        $stmt->execute([$campaignId, $agentId]);
        return $stmt->fetch();
    }

    public function getByAgentId(int $agentId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("\n            SELECT qr.*,\n                   c.name AS campaign_name\n            FROM qa_retrainings qr\n            LEFT JOIN campaigns c ON qr.campaign_id = c.id\n            WHERE qr.agent_id = ?\n            ORDER BY qr.created_at DESC\n            LIMIT :limit\n        ");
        $stmt->bindValue(1, $agentId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachUserNamesToRows($rows);
    }

    public function getRecent(int $limit = 40): array
    {
        $stmt = $this->db->prepare("\n            SELECT qr.*,\n                   c.name AS campaign_name\n            FROM qa_retrainings qr\n            LEFT JOIN campaigns c ON qr.campaign_id = c.id\n            ORDER BY qr.created_at DESC\n            LIMIT :limit\n        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachUserNamesToRows($rows);
    }

    public function getPendingReminders(string $today): array
    {
        $stmt = $this->db->prepare("\n            SELECT qr.*, c.name AS campaign_name\n            FROM qa_retrainings qr\n            LEFT JOIN campaigns c ON qr.campaign_id = c.id\n            WHERE qr.status IN ('assigned', 'in_progress')\n              AND qr.due_date IS NOT NULL\n              AND qr.due_date <= ?\n            ORDER BY qr.due_date ASC\n        ");
        $stmt->execute([$today]);
        $rows = $stmt->fetchAll();
        return $this->attachUserNamesToRows($rows);
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        $allowed = [
            'status',
            'progress_percent',
            'approved_by',
            'approved_at',
            'activation_at',
            'reminder_sent_at',
            'reminder_count',
            'reinforcement_required',
            'fail_count',
            'notes',
            'supervisor_id'
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE qa_retrainings SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function incrementReminderCount(int $id): void
    {
        $stmt = $this->db->prepare("\n            UPDATE qa_retrainings\n            SET reminder_count = COALESCE(reminder_count, 0) + 1,\n                reminder_sent_at = ?\n            WHERE id = ?\n        ");
        $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }

    private function attachUserNames($row)
    {
        if (!$row) {
            return $row;
        }

        $ids = [
            (int) ($row['agent_id'] ?? 0),
            (int) ($row['created_by'] ?? 0),
            (int) ($row['supervisor_id'] ?? 0),
            (int) ($row['approved_by'] ?? 0)
        ];
        $map = (new User())->getMapByIds($ids);

        $agentId = (int) ($row['agent_id'] ?? 0);
        $createdBy = (int) ($row['created_by'] ?? 0);
        $supervisorId = (int) ($row['supervisor_id'] ?? 0);
        $approvedBy = (int) ($row['approved_by'] ?? 0);

        $row['agent_name'] = $map[$agentId]['full_name'] ?? ('Agente #' . $agentId);
        $row['created_by_name'] = $map[$createdBy]['full_name'] ?? ('Usuario #' . $createdBy);
        $row['supervisor_name'] = $map[$supervisorId]['full_name'] ?? null;
        $row['approved_by_name'] = $map[$approvedBy]['full_name'] ?? null;

        return $row;
    }

    private function attachUserNamesToRows(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) ($row['agent_id'] ?? 0);
            $ids[] = (int) ($row['created_by'] ?? 0);
            $ids[] = (int) ($row['supervisor_id'] ?? 0);
            $ids[] = (int) ($row['approved_by'] ?? 0);
        }

        $map = (new User())->getMapByIds($ids);

        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $createdBy = (int) ($row['created_by'] ?? 0);
            $supervisorId = (int) ($row['supervisor_id'] ?? 0);
            $approvedBy = (int) ($row['approved_by'] ?? 0);

            $row['agent_name'] = $map[$agentId]['full_name'] ?? ('Agente #' . $agentId);
            $row['created_by_name'] = $map[$createdBy]['full_name'] ?? ('Usuario #' . $createdBy);
            $row['supervisor_name'] = $map[$supervisorId]['full_name'] ?? null;
            $row['approved_by_name'] = $map[$approvedBy]['full_name'] ?? null;
        }
        unset($row);

        return $rows;
    }
}
