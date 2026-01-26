<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\CorporateClient;
use App\Models\ClientCampaign;
use App\Models\ClientPortalSettings;
use App\Models\Campaign;
use App\Models\Call;
use App\Models\Evaluation;
use App\Config\Config;

class ClientPortalController
{
    private function formatDuration($seconds): string
    {
        if ($seconds === null || $seconds === '') {
            return '00:00';
        }
        $seconds = (int) $seconds;
        $minutes = floor($seconds / 60);
        $remaining = $seconds % 60;
        return str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT);
    }

    private function metricCatalog(): array
    {
        return [
            'total_calls' => ['label' => 'Total de llamadas', 'type' => 'count'],
            'total_evaluations' => ['label' => 'Total de evaluaciones', 'type' => 'count'],
            'evaluation_coverage' => ['label' => 'Cobertura de evaluacion', 'type' => 'percent'],
            'pending_evaluations' => ['label' => 'Pendientes por evaluar', 'type' => 'count'],
            'avg_score' => ['label' => 'Promedio de calidad', 'type' => 'percent'],
            'compliance_rate' => ['label' => 'Cumplimiento >= 85%', 'type' => 'percent'],
            'critical_fails' => ['label' => 'Fallos criticos', 'type' => 'count'],
            'top_agent' => ['label' => 'Mejor agente', 'type' => 'text'],
            'max_score' => ['label' => 'Mejor score', 'type' => 'percent'],
            'min_score' => ['label' => 'Peor score', 'type' => 'percent'],
            'avg_duration' => ['label' => 'Duracion promedio', 'type' => 'duration']
        ];
    }

    private function defaultMetrics(): array
    {
        return [
            'total_calls',
            'total_evaluations',
            'evaluation_coverage',
            'pending_evaluations',
            'avg_score',
            'compliance_rate',
            'top_agent'
        ];
    }

    private function formatMetricValue(string $key, $value, $topAgent = null): string
    {
        if ($key === 'top_agent') {
            if (!$topAgent) {
                return 'Sin datos';
            }
            $score = $topAgent['avg_score'] !== null ? number_format((float) $topAgent['avg_score'], 1) . '%' : 'N/D';
            return $topAgent['full_name'] . ' - ' . $score;
        }

        if ($value === null || $value === '') {
            return 'N/D';
        }

        if ($key === 'avg_duration') {
            return $this->formatDuration((int) round((float) $value));
        }

        if (in_array($key, ['avg_score', 'compliance_rate', 'max_score', 'min_score'], true)) {
            return number_format((float) $value, 1) . '%';
        }

        return number_format((float) $value);
    }

    public function index()
    {
        Auth::requireAnyRole(['client']);

        $user = Auth::user();
        $clientId = $user['client_id'] ?? null;
        if (!$clientId) {
            http_response_code(403);
            die('Cliente sin asignacion.');
        }

        $clientModel = new CorporateClient();
        $client = $clientModel->findById($clientId);
        if (!$client) {
            http_response_code(404);
            die('Cliente no encontrado.');
        }

        $campaignModel = new Campaign();
        $clientCampaigns = new ClientCampaign();
        $campaignIds = $clientCampaigns->getCampaignIds($clientId);
        $campaigns = $campaignModel->getByIds($campaignIds);

        $settingsModel = new ClientPortalSettings();
        $settings = $settingsModel->findByClientId($clientId);
        if (!$settings) {
            $settings = [
                'show_calls' => 1,
                'show_evaluations' => 1,
                'show_ai_summary' => 0,
                'show_recordings' => 0,
                'show_agent_scores' => 1,
                'metrics_json' => json_encode($this->defaultMetrics())
            ];
        }

        $selectedMetrics = json_decode($settings['metrics_json'] ?? '', true);
        if (!is_array($selectedMetrics) || empty($selectedMetrics)) {
            $selectedMetrics = $this->defaultMetrics();
        }

        $callModel = new Call();
        $evaluationModel = new Evaluation();

        $callCount = $callModel->getCountByCampaignIds($campaignIds);
        $avgDuration = $callModel->getAverageDurationByCampaignIds($campaignIds);
        $evaluationStats = $evaluationModel->getStatsByCampaignIds($campaignIds);
        $complianceRate = $evaluationModel->getComplianceRateByCampaignIds($campaignIds);
        $criticalFails = $evaluationModel->getCriticalFailsCountByCampaignIds($campaignIds);
        $topAgent = $evaluationModel->getTopAgentByCampaignIds($campaignIds);
        $topAgents = $evaluationModel->getTopAgentsByCampaignIds($campaignIds);
        $campaignPerformance = $evaluationModel->getCampaignAverages($campaignIds);

        $totalEvaluations = (int) ($evaluationStats['total_evaluations'] ?? 0);
        $evaluationCoverage = $callCount > 0 ? ($totalEvaluations / $callCount) * 100 : 0;
        $pendingEvaluations = max(0, $callCount - $totalEvaluations);

        $metricValues = [
            'total_calls' => $callCount,
            'total_evaluations' => $totalEvaluations,
            'evaluation_coverage' => $evaluationCoverage,
            'pending_evaluations' => $pendingEvaluations,
            'avg_score' => $evaluationStats['avg_percentage'] ?? null,
            'compliance_rate' => $complianceRate,
            'critical_fails' => $criticalFails,
            'top_agent' => $topAgent,
            'max_score' => $evaluationStats['max_percentage'] ?? null,
            'min_score' => $evaluationStats['min_percentage'] ?? null,
            'avg_duration' => $avgDuration
        ];

        $catalog = $this->metricCatalog();
        $metricCards = [];
        foreach ($selectedMetrics as $metricKey) {
            if (!isset($catalog[$metricKey])) {
                continue;
            }
            $value = $metricValues[$metricKey] ?? null;
            $metricCards[] = [
                'label' => $catalog[$metricKey]['label'],
                'value' => $this->formatMetricValue($metricKey, $value, $topAgent)
            ];
        }

        $calls = $callModel->getByCampaignIds($campaignIds, 20);
        foreach ($calls as &$call) {
            $call['date'] = date('d/m/Y H:i', strtotime($call['call_datetime']));
            $call['duration'] = $this->formatDuration($call['duration_seconds']);
            $call['recording_url'] = $call['recording_path'] ? (Config::BASE_URL . $call['recording_path']) : null;
        }
        unset($call);

        require __DIR__ . '/../Views/client_portal/index.php';
    }
}
