<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Config\Database;
use App\Models\User;
use App\Models\Campaign;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController
{

    public function index()
    {
        Auth::requirePermission('reports.view');

        $db = Database::getInstance()->getConnection();
        $passThreshold = 80;
        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();

        $campaignMap = [];
        foreach ($campaigns as $campaign) {
            $campaignMap[(int) $campaign['id']] = $campaign['name'];
        }

        $selectedCampaignId = isset($_GET['campaign_id']) ? (int) $_GET['campaign_id'] : 0;
        if ($selectedCampaignId > 0 && !isset($campaignMap[$selectedCampaignId])) {
            $selectedCampaignId = 0;
        }
        $selectedCampaignName = $selectedCampaignId > 0 ? $campaignMap[$selectedCampaignId] : null;

        $evaluationFilterSql = $selectedCampaignId > 0 ? ' WHERE e.campaign_id = :campaign_id ' : '';
        $evaluationFilterParams = $selectedCampaignId > 0 ? [':campaign_id' => $selectedCampaignId] : [];
        $campaignWhereSql = $selectedCampaignId > 0 ? ' WHERE c.id = :campaign_id ' : '';
        $campaignWhereParams = $selectedCampaignId > 0 ? [':campaign_id' => $selectedCampaignId] : [];

        // Overall KPIs
        $overallStats = $this->fetchOne($db, "
            SELECT
                COUNT(*) as total_evaluations,
                AVG(percentage) as avg_score,
                MIN(percentage) as min_score,
                MAX(percentage) as max_score,
                (AVG(percentage >= {$passThreshold}) * 100) as pass_rate,
                AVG(call_duration) as avg_duration
            FROM evaluations e
            {$evaluationFilterSql}
        ", $evaluationFilterParams);

        // Score distribution
        $scoreDistribution = $this->fetchOne($db, "
            SELECT
                SUM(percentage >= 95) as bucket_95,
                SUM(percentage >= 90 AND percentage < 95) as bucket_90,
                SUM(percentage >= 80 AND percentage < 90) as bucket_80,
                SUM(percentage >= 70 AND percentage < 80) as bucket_70,
                SUM(percentage < 70) as bucket_0
            FROM evaluations e
            {$evaluationFilterSql}
        ", $evaluationFilterParams);

        // Recent evaluations
        $recentEvaluations = $this->fetchAllRows($db, "
            SELECT
                e.id,
                e.percentage,
                e.created_at,
                e.agent_id,
                e.qa_id,
                c.name as campaign_name
            FROM evaluations e
            JOIN campaigns c ON c.id = e.campaign_id
            {$evaluationFilterSql}
            ORDER BY e.created_at DESC
            LIMIT 10
        ", $evaluationFilterParams);
        $recentEvaluations = $this->attachNames($recentEvaluations);

        // Stats by Campaign
        $campaignStats = $this->fetchAllRows($db, "
            SELECT 
                c.name as campaign_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score,
                MIN(e.percentage) as min_score,
                MAX(e.percentage) as max_score
            FROM campaigns c
            LEFT JOIN evaluations e ON c.id = e.campaign_id
            {$campaignWhereSql}
            GROUP BY c.id, c.name
            HAVING total_evaluations > 0
            ORDER BY avg_score DESC
        ", $campaignWhereParams);

        // Stats by Campaign (Top 5)
        $topCampaigns = $this->fetchAllRows($db, "
            SELECT 
                c.name as campaign_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM campaigns c
            JOIN evaluations e ON c.id = e.campaign_id
            {$evaluationFilterSql}
            GROUP BY c.id, c.name
            ORDER BY avg_score DESC
            LIMIT 5
        ", $evaluationFilterParams);

        // Stats by Agent (Top 5)
        $topAgents = $this->fetchAllRows($db, "
            SELECT 
                e.agent_id,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            {$evaluationFilterSql}
            GROUP BY e.agent_id
            ORDER BY avg_score DESC
            LIMIT 5
        ", $evaluationFilterParams);
        $topAgents = $this->attachAgentNames($topAgents);

        // Stats by Agent (Bottom 5)
        $bottomAgents = $this->fetchAllRows($db, "
            SELECT 
                e.agent_id,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            {$evaluationFilterSql}
            GROUP BY e.agent_id
            HAVING total_evaluations >= 3
            ORDER BY avg_score ASC
            LIMIT 5
        ", $evaluationFilterParams);
        $bottomAgents = $this->attachAgentNames($bottomAgents);

        // QA performance
        $qaStats = $this->fetchAllRows($db, "
            SELECT 
                e.qa_id,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            {$evaluationFilterSql}
            GROUP BY e.qa_id
            ORDER BY avg_score DESC
        ", $evaluationFilterParams);
        $qaStats = $this->attachQaNames($qaStats);

        // Monthly trend (last 6 months)
        $monthlyTrend = $this->fetchAllRows($db, "
            SELECT 
                DATE_FORMAT(e.created_at, '%Y-%m') as period,
                COUNT(*) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            {$evaluationFilterSql}
            GROUP BY period
            ORDER BY period DESC
            LIMIT 6
        ", $evaluationFilterParams);
        $monthlyTrend = array_reverse($monthlyTrend);

        require __DIR__ . '/../Views/reports/index.php';
    }

    public function topEvaluators()
    {
        Auth::requirePermission('reports.top_evaluators');

        $evaluationModel = new \App\Models\Evaluation();
        $qaRanking = $evaluationModel->getQaRanking();

        $selectedQaId = $_GET['qa_id'] ?? null;
        $qaEvaluations = [];
        $selectedQa = null;

        if ($selectedQaId) {
            $qaEvaluations = $evaluationModel->getByQaId($selectedQaId);
            $userModel = new \App\Models\User();
            $selectedQa = $userModel->findById($selectedQaId);
        }

        require __DIR__ . '/../Views/reports/top_evaluators.php';
    }

    public function exportPdf()
    {
        Auth::requirePermission('reports.view');

        $db = Database::getInstance()->getConnection();
        $passThreshold = 80;
        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();
        $campaignMap = [];
        foreach ($campaigns as $campaign) {
            $campaignMap[(int) $campaign['id']] = $campaign['name'];
        }

        $selectedCampaignId = isset($_GET['campaign_id']) ? (int) $_GET['campaign_id'] : 0;
        if ($selectedCampaignId > 0 && !isset($campaignMap[$selectedCampaignId])) {
            $selectedCampaignId = 0;
        }
        $selectedCampaignName = $selectedCampaignId > 0 ? $campaignMap[$selectedCampaignId] : null;

        $evaluationFilterSql = $selectedCampaignId > 0 ? ' WHERE e.campaign_id = :campaign_id ' : '';
        $evaluationFilterParams = $selectedCampaignId > 0 ? [':campaign_id' => $selectedCampaignId] : [];
        $campaignWhereSql = $selectedCampaignId > 0 ? ' WHERE c.id = :campaign_id ' : '';
        $campaignWhereParams = $selectedCampaignId > 0 ? [':campaign_id' => $selectedCampaignId] : [];

        $overallStats = $this->fetchOne($db, "
            SELECT
                COUNT(*) as total_evaluations,
                AVG(percentage) as avg_score,
                MIN(percentage) as min_score,
                MAX(percentage) as max_score,
                (AVG(percentage >= {$passThreshold}) * 100) as pass_rate
            FROM evaluations e
            {$evaluationFilterSql}
        ", $evaluationFilterParams);

        $campaignStats = $this->fetchAllRows($db, "
            SELECT
                c.name as campaign_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM campaigns c
            LEFT JOIN evaluations e ON c.id = e.campaign_id
            {$campaignWhereSql}
            GROUP BY c.id, c.name
            HAVING total_evaluations > 0
            ORDER BY avg_score DESC
        ", $campaignWhereParams);

        $topAgents = $this->fetchAllRows($db, "
            SELECT 
                e.agent_id,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            {$evaluationFilterSql}
            GROUP BY e.agent_id
            ORDER BY avg_score DESC
            LIMIT 10
        ", $evaluationFilterParams);
        $topAgents = $this->attachAgentNames($topAgents);

        $qaStats = $this->fetchAllRows($db, "
            SELECT 
                e.qa_id,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            {$evaluationFilterSql}
            GROUP BY e.qa_id
            ORDER BY avg_score DESC
        ", $evaluationFilterParams);
        $qaStats = $this->attachQaNames($qaStats);

        ob_start();
        require __DIR__ . '/../Views/reports/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Reporte-Calidad-' . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    private function attachNames(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }
        $agentIds = array_column($rows, 'agent_id');
        $qaIds = array_column($rows, 'qa_id');
        $map = (new User())->getMapByIds(array_merge($agentIds, $qaIds));
        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $qaId = (int) ($row['qa_id'] ?? 0);
            $row['agent_name'] = $map[$agentId]['full_name'] ?? ('Agente #' . $agentId);
            $row['qa_name'] = $map[$qaId]['full_name'] ?? ('QA #' . $qaId);
        }
        unset($row);
        return $rows;
    }

    private function fetchOne($db, string $sql, array $params = []): array
    {
        if (empty($params)) {
            $stmt = $db->query($sql);
        } else {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
        $row = $stmt->fetch();
        return $row ?: [];
    }

    private function fetchAllRows($db, string $sql, array $params = []): array
    {
        if (empty($params)) {
            $stmt = $db->query($sql);
        } else {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
        return $stmt->fetchAll();
    }

    private function attachAgentNames(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }
        $ids = array_column($rows, 'agent_id');
        $map = (new User())->getMapByIds($ids);
        foreach ($rows as &$row) {
            $agentId = (int) ($row['agent_id'] ?? 0);
            $row['agent_name'] = $map[$agentId]['full_name'] ?? ('Agente #' . $agentId);
        }
        unset($row);
        return $rows;
    }

    private function attachQaNames(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }
        $ids = array_column($rows, 'qa_id');
        $map = (new User())->getMapByIds($ids);
        foreach ($rows as &$row) {
            $qaId = (int) ($row['qa_id'] ?? 0);
            $row['qa_name'] = $map[$qaId]['full_name'] ?? ('QA #' . $qaId);
        }
        unset($row);
        return $rows;
    }
}
