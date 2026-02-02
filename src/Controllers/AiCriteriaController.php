<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\AiEvaluationCriteria;
use App\Models\Campaign;
use App\Models\CorporateClient;

class AiCriteriaController
{
    public function index()
    {
        Auth::requireRole('admin');

        $criteriaModel = new AiEvaluationCriteria();
        $campaignModel = new Campaign();
        $clientModel = new CorporateClient();

        $criteria = $criteriaModel->getAll();
        $campaigns = $campaignModel->getAll();
        $projects = $clientModel->getAll();

        $editing = null;

        require __DIR__ . '/../Views/ai_criteria/index.php';
    }

    public function edit()
    {
        Auth::requireRole('admin');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'ai-criteria');
            exit;
        }

        $criteriaModel = new AiEvaluationCriteria();
        $campaignModel = new Campaign();
        $clientModel = new CorporateClient();

        $editing = $criteriaModel->findById($id);
        if (!$editing) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'ai-criteria');
            exit;
        }

        $criteria = $criteriaModel->getAll();
        $campaigns = $campaignModel->getAll();
        $projects = $clientModel->getAll();

        require __DIR__ . '/../Views/ai_criteria/index.php';
    }

    public function store()
    {
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $criteriaText = trim($_POST['criteria_text'] ?? '');
        if ($criteriaText === '') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'ai-criteria');
            exit;
        }

        $data = [
            'project_id' => $_POST['project_id'] !== '' ? (int) $_POST['project_id'] : null,
            'campaign_id' => $_POST['campaign_id'] !== '' ? (int) $_POST['campaign_id'] : null,
            'call_type' => $_POST['call_type'] !== '' ? trim($_POST['call_type']) : null,
            'criteria_text' => $criteriaText,
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        $criteriaModel = new AiEvaluationCriteria();
        if ($id) {
            $criteriaModel->update($id, $data);
        } else {
            $criteriaModel->create($data);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'ai-criteria');
    }

    public function toggle()
    {
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $active = isset($_POST['active']) ? (int) $_POST['active'] : null;
        if (!$id || $active === null) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'ai-criteria');
            exit;
        }

        $criteriaModel = new AiEvaluationCriteria();
        $criteriaModel->setActive($id, $active);

        header('Location: ' . \App\Config\Config::BASE_URL . 'ai-criteria');
    }
}
