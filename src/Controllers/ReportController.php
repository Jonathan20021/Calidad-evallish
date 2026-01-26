<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController
{

    public function index()
    {
        Auth::requireAuth();

        $db = Database::getInstance()->getConnection();
        $passThreshold = 80;

        // Overall KPIs
        $stmt = $db->query("
            SELECT
                COUNT(*) as total_evaluations,
                AVG(percentage) as avg_score,
                MIN(percentage) as min_score,
                MAX(percentage) as max_score,
                (AVG(percentage >= {$passThreshold}) * 100) as pass_rate,
                AVG(call_duration) as avg_duration
            FROM evaluations
        ");
        $overallStats = $stmt->fetch();

        // Score distribution
        $stmt = $db->query("
            SELECT
                SUM(percentage >= 95) as bucket_95,
                SUM(percentage >= 90 AND percentage < 95) as bucket_90,
                SUM(percentage >= 80 AND percentage < 90) as bucket_80,
                SUM(percentage >= 70 AND percentage < 80) as bucket_70,
                SUM(percentage < 70) as bucket_0
            FROM evaluations
        ");
        $scoreDistribution = $stmt->fetch();

        // Recent evaluations
        $stmt = $db->query("
            SELECT
                e.id,
                e.percentage,
                e.created_at,
                u.full_name as agent_name,
                qa.full_name as qa_name,
                c.name as campaign_name
            FROM evaluations e
            JOIN users u ON u.id = e.agent_id
            JOIN users qa ON qa.id = e.qa_id
            JOIN campaigns c ON c.id = e.campaign_id
            ORDER BY e.created_at DESC
            LIMIT 10
        ");
        $recentEvaluations = $stmt->fetchAll();

        // Stats by Campaign
        $stmt = $db->query("
            SELECT 
                c.name as campaign_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score,
                MIN(e.percentage) as min_score,
                MAX(e.percentage) as max_score
            FROM campaigns c
            LEFT JOIN evaluations e ON c.id = e.campaign_id
            GROUP BY c.id, c.name
            HAVING total_evaluations > 0
            ORDER BY avg_score DESC
        ");
        $campaignStats = $stmt->fetchAll();

        // Stats by Agent (Top 5)
        $stmt = $db->query("
            SELECT 
                u.full_name as agent_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM users u
            JOIN evaluations e ON u.id = e.agent_id
            GROUP BY u.id, u.full_name
            ORDER BY avg_score DESC
            LIMIT 5
        ");
        $topAgents = $stmt->fetchAll();

        // Stats by Agent (Bottom 5)
        $stmt = $db->query("
            SELECT 
                u.full_name as agent_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM users u
            JOIN evaluations e ON u.id = e.agent_id
            GROUP BY u.id, u.full_name
            HAVING total_evaluations >= 3
            ORDER BY avg_score ASC
            LIMIT 5
        ");
        $bottomAgents = $stmt->fetchAll();

        // QA performance
        $stmt = $db->query("
            SELECT 
                qa.full_name as qa_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM users qa
            JOIN evaluations e ON qa.id = e.qa_id
            GROUP BY qa.id, qa.full_name
            ORDER BY avg_score DESC
        ");
        $qaStats = $stmt->fetchAll();

        // Monthly trend (last 6 months)
        $stmt = $db->query("
            SELECT 
                DATE_FORMAT(e.created_at, '%Y-%m') as period,
                COUNT(*) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM evaluations e
            GROUP BY period
            ORDER BY period DESC
            LIMIT 6
        ");
        $monthlyTrend = array_reverse($stmt->fetchAll());

        require __DIR__ . '/../Views/reports/index.php';
    }

    public function exportPdf()
    {
        Auth::requireAuth();

        $db = Database::getInstance()->getConnection();
        $passThreshold = 80;

        $stmt = $db->query("
            SELECT
                COUNT(*) as total_evaluations,
                AVG(percentage) as avg_score,
                MIN(percentage) as min_score,
                MAX(percentage) as max_score,
                (AVG(percentage >= {$passThreshold}) * 100) as pass_rate
            FROM evaluations
        ");
        $overallStats = $stmt->fetch();

        $stmt = $db->query("
            SELECT
                c.name as campaign_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM campaigns c
            LEFT JOIN evaluations e ON c.id = e.campaign_id
            GROUP BY c.id, c.name
            HAVING total_evaluations > 0
            ORDER BY avg_score DESC
        ");
        $campaignStats = $stmt->fetchAll();

        $stmt = $db->query("
            SELECT 
                u.full_name as agent_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM users u
            JOIN evaluations e ON u.id = e.agent_id
            GROUP BY u.id, u.full_name
            ORDER BY avg_score DESC
            LIMIT 10
        ");
        $topAgents = $stmt->fetchAll();

        $stmt = $db->query("
            SELECT 
                qa.full_name as qa_name,
                COUNT(e.id) as total_evaluations,
                AVG(e.percentage) as avg_score
            FROM users qa
            JOIN evaluations e ON qa.id = e.qa_id
            GROUP BY qa.id, qa.full_name
            ORDER BY avg_score DESC
        ");
        $qaStats = $stmt->fetchAll();

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
}
