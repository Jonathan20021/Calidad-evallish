<?php

namespace App\Models;

use App\Config\Database;
use App\Models\User;
use PDO;

class Evaluation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, 
                   c.name as campaign_name,
                   ft.title as form_title
            FROM evaluations e
            JOIN campaigns c ON e.campaign_id = c.id
            JOIN form_templates ft ON e.form_template_id = ft.id
            ORDER BY e.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $this->attachUserNames($rows);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, 
                   c.name as campaign_name,
                   ft.title as form_title
            FROM evaluations e
            JOIN campaigns c ON e.campaign_id = c.id
            JOIN form_templates ft ON e.form_template_id = ft.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return $row;
        }
        $rows = $this->attachUserNames([$row]);
        return $rows[0] ?? $row;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO evaluations (call_id, agent_id, qa_id, campaign_id, form_template_id, call_date, call_duration, total_score, max_possible_score, percentage, general_comments, action_type, improvement_areas, improvement_plan, tasks_commitments, feedback_confirmed, feedback_confirmed_at, feedback_evidence_path, feedback_evidence_name, feedback_evidence_note) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['call_id'] ?? null,
            $data['agent_id'],
            $data['qa_id'],
            $data['campaign_id'],
            $data['form_template_id'],
            $data['call_date'] ?? date('Y-m-d'),
            $data['call_duration'] ?? null,
            $data['total_score'],
            $data['max_possible_score'],
            $data['percentage'],
            $data['general_comments'] ?? '',
            $data['action_type'] ?? null,
            $data['improvement_areas'] ?? null,
            $data['improvement_plan'] ?? null,
            $data['tasks_commitments'] ?? null,
            $data['feedback_confirmed'] ?? 0,
            $data['feedback_confirmed_at'] ?? null,
            $data['feedback_evidence_path'] ?? null,
            $data['feedback_evidence_name'] ?? null,
            $data['feedback_evidence_note'] ?? null
        ]);
    }

    public function update($id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE evaluations
            SET call_id = ?,
                agent_id = ?,
                qa_id = ?,
                campaign_id = ?,
                form_template_id = ?,
                call_date = ?,
                call_duration = ?,
                total_score = ?,
                max_possible_score = ?,
                percentage = ?,
                general_comments = ?,
                action_type = ?,
                improvement_areas = ?,
                improvement_plan = ?,
                tasks_commitments = ?,
                feedback_confirmed = ?,
                feedback_confirmed_at = ?,
                feedback_evidence_path = ?,
                feedback_evidence_name = ?,
                feedback_evidence_note = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['call_id'] ?? null,
            $data['agent_id'],
            $data['qa_id'],
            $data['campaign_id'],
            $data['form_template_id'],
            $data['call_date'] ?? date('Y-m-d'),
            $data['call_duration'] ?? null,
            $data['total_score'],
            $data['max_possible_score'],
            $data['percentage'],
            $data['general_comments'] ?? '',
            $data['action_type'] ?? null,
            $data['improvement_areas'] ?? null,
            $data['improvement_plan'] ?? null,
            $data['tasks_commitments'] ?? null,
            $data['feedback_confirmed'] ?? 0,
            $data['feedback_confirmed_at'] ?? null,
            $data['feedback_evidence_path'] ?? null,
            $data['feedback_evidence_name'] ?? null,
            $data['feedback_evidence_note'] ?? null,
            $id
        ]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function getStats()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_evaluations,
                AVG(percentage) as avg_percentage,
                MAX(percentage) as max_percentage,
                MIN(percentage) as min_percentage
            FROM evaluations
        ");
        return $stmt->fetch();
    }

    public function getComplianceRate($threshold = 85)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as passed FROM evaluations WHERE percentage >= ?");
        $stmt->execute([$threshold]);
        $passed = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM evaluations");
        $total = $stmt->fetchColumn();

        return ($total > 0) ? ($passed / $total) * 100 : 0;
    }

    public function getTopAgent()
    {
        $stmt = $this->db->query("
            SELECT e.agent_id, AVG(e.percentage) as avg_score
            FROM evaluations e
            GROUP BY e.agent_id
            ORDER BY avg_score DESC
            LIMIT 1
        ");
        $row = $stmt->fetch();
        if (!$row) {
            return $row;
        }
        $users = (new User())->getMapByIds([$row['agent_id']]);
        $row['full_name'] = $users[(int) $row['agent_id']]['full_name'] ?? ('Agente #' . $row['agent_id']);
        return $row;
    }

    public function getCriticalFailsCount($threshold = 70)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM evaluations WHERE percentage < ?");
        $stmt->execute([$threshold]);
        return $stmt->fetchColumn();
    }

    public function getStatsByCampaignIds(array $campaignIds): array
    {
        if (empty($campaignIds)) {
            return [
                'total_evaluations' => 0,
                'avg_percentage' => null,
                'max_percentage' => null,
                'min_percentage' => null
            ];
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_evaluations,
                AVG(percentage) as avg_percentage,
                MAX(percentage) as max_percentage,
                MIN(percentage) as min_percentage
            FROM evaluations
            WHERE campaign_id IN ($placeholders)
        ");
        $stmt->execute($campaignIds);
        return $stmt->fetch();
    }

    public function getComplianceRateByCampaignIds(array $campaignIds, $threshold = 85): float
    {
        if (empty($campaignIds)) {
            return 0.0;
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $passedStmt = $this->db->prepare("SELECT COUNT(*) FROM evaluations WHERE percentage >= ? AND campaign_id IN ($placeholders)");
        $passedStmt->execute(array_merge([$threshold], $campaignIds));
        $passed = (int) $passedStmt->fetchColumn();

        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM evaluations WHERE campaign_id IN ($placeholders)");
        $totalStmt->execute($campaignIds);
        $total = (int) $totalStmt->fetchColumn();

        return $total > 0 ? ($passed / $total) * 100 : 0.0;
    }

    public function getCriticalFailsCountByCampaignIds(array $campaignIds, $threshold = 70): int
    {
        if (empty($campaignIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM evaluations WHERE percentage < ? AND campaign_id IN ($placeholders)");
        $stmt->execute(array_merge([$threshold], $campaignIds));
        return (int) $stmt->fetchColumn();
    }

    public function getTopAgentByCampaignIds(array $campaignIds)
    {
        if (empty($campaignIds)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("
            SELECT e.agent_id, AVG(e.percentage) as avg_score, COUNT(*) as total
            FROM evaluations e
            WHERE e.campaign_id IN ($placeholders)
            GROUP BY e.agent_id
            ORDER BY avg_score DESC
            LIMIT 1
        ");
        $stmt->execute($campaignIds);
        $row = $stmt->fetch();
        if (!$row) {
            return $row;
        }
        $users = (new User())->getMapByIds([$row['agent_id']]);
        $row['full_name'] = $users[(int) $row['agent_id']]['full_name'] ?? ('Agente #' . $row['agent_id']);
        return $row;
    }

    public function getTopAgentsByCampaignIds(array $campaignIds, $limit = 5): array
    {
        if (empty($campaignIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("
            SELECT e.agent_id, AVG(e.percentage) as avg_score, COUNT(*) as total
            FROM evaluations e
            WHERE e.campaign_id IN ($placeholders)
            GROUP BY e.agent_id
            ORDER BY avg_score DESC
            LIMIT ?
        ");
        $params = array_merge($campaignIds, [(int) $limit]);
        $index = 1;
        foreach ($params as $value) {
            $type = $index === count($params) ? PDO::PARAM_INT : PDO::PARAM_INT;
            $stmt->bindValue($index, $value, $type);
            $index++;
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            return $rows;
        }
        $ids = array_column($rows, 'agent_id');
        $users = (new User())->getMapByIds($ids);
        foreach ($rows as &$row) {
            $row['full_name'] = $users[(int) $row['agent_id']]['full_name'] ?? ('Agente #' . $row['agent_id']);
        }
        unset($row);
        return $rows;
    }

    public function getCampaignAverages(array $campaignIds): array
    {
        if (empty($campaignIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($campaignIds), '?'));
        $stmt = $this->db->prepare("
            SELECT c.id, c.name, AVG(e.percentage) as avg_percentage, COUNT(*) as total_evaluations
            FROM evaluations e
            JOIN campaigns c ON e.campaign_id = c.id
            WHERE e.campaign_id IN ($placeholders)
            GROUP BY c.id, c.name
            ORDER BY avg_percentage DESC
        ");
        $stmt->execute($campaignIds);
        return $stmt->fetchAll();
    }

    public function getRecentByAgent($agentId, $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   c.name as campaign_name
            FROM evaluations e
            JOIN campaigns c ON e.campaign_id = c.id
            WHERE e.agent_id = ?
            ORDER BY e.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $agentId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopEvaluatedCalls($limit = 10, $campaignId = null): array
    {
        $sql = "
            SELECT e.id as evaluation_id,
                   e.percentage,
                   e.created_at,
                   c.id as call_id,
                   c.call_datetime,
                   c.recording_path,
                   e.agent_id,
                   camp.name as campaign_name
            FROM evaluations e
            JOIN calls c ON e.call_id = c.id
            JOIN campaigns camp ON e.campaign_id = camp.id
            WHERE c.recording_path IS NOT NULL
        ";
        $params = [];
        if ($campaignId) {
            $sql .= " AND e.campaign_id = ?";
            $params[] = $campaignId;
        }
        $sql .= " ORDER BY e.percentage DESC, e.created_at DESC LIMIT ?";
        $params[] = (int) $limit;

        $stmt = $this->db->prepare($sql);
        $index = 1;
        foreach ($params as $value) {
            $type = $index === count($params) ? PDO::PARAM_INT : PDO::PARAM_INT;
            $stmt->bindValue($index, $value, $type);
            $index++;
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            return $rows;
        }
        $ids = array_column($rows, 'agent_id');
        $users = (new User())->getMapByIds($ids);
        foreach ($rows as &$row) {
            $row['agent_name'] = $users[(int) $row['agent_id']]['full_name'] ?? ('Agente #' . $row['agent_id']);
        }
        unset($row);
        return $rows;
    }

    public function findByCallId($callId)
    {
        $stmt = $this->db->prepare("SELECT * FROM evaluations WHERE call_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$callId]);
        return $stmt->fetch();
    }

    private function attachUserNames(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $agentIds = [];
        $qaIds = [];
        foreach ($rows as $row) {
            if (isset($row['agent_id'])) {
                $agentIds[] = (int) $row['agent_id'];
            }
            if (isset($row['qa_id'])) {
                $qaIds[] = (int) $row['qa_id'];
            }
        }

        $userModel = new User();
        $agentMap = $userModel->getMapByIds($agentIds);
        $qaMap = $userModel->getMapByIds($qaIds);

        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $qaId = (int) ($row['qa_id'] ?? 0);
            $row['agent_name'] = $agentMap[$agentId]['full_name'] ?? ('Agente #' . $agentId);
            $row['qa_name'] = $qaMap[$qaId]['full_name'] ?? ('QA #' . $qaId);
        }
        unset($row);

        return $rows;
    }
}
