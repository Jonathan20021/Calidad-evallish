<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Evaluation;
use App\Models\Campaign;

class DashboardController
{

    public function index()
    {
        Auth::requireAuth();

        $user = Auth::user();
        $evaluationModel = new Evaluation();
        $campaignModel = new Campaign();

        $recentEvaluations = $evaluationModel->getAll(5);
        $activeCampaigns = $campaignModel->getActive();
        $stats = $evaluationModel->getStats();
        $complianceRate = $evaluationModel->getComplianceRate();
        $topAgent = $evaluationModel->getTopAgent();
        $criticalFails = $evaluationModel->getCriticalFailsCount();

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
