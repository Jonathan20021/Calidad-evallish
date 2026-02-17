<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Evaluation;
use App\Models\FormTemplate;
use App\Models\Campaign;
use App\Config\Config;

class RecycleBinController
{
    public function index()
    {
        Auth::requirePermission('settings.view');

        $evaluationModel = new Evaluation();
        $templateModel = new FormTemplate();
        $campaignModel = new Campaign();

        $user = Auth::user();
        $deletedEvaluations = $evaluationModel->getDeleted(50, $user['id'], $user['role']);
        $deletedTemplates = $templateModel->getDeleted();
        $deletedCampaigns = $campaignModel->getDeleted();

        require __DIR__ . '/../Views/recycle_bin/index.php';
    }

    public function restore()
    {
        Auth::requirePermission('settings.view');

        $type = $_GET['type'] ?? null;
        $id = $_GET['id'] ?? null;

        if (!$type || !$id) {
            header('Location: ' . Config::BASE_URL . 'recycle-bin');
            exit;
        }

        switch ($type) {
            case 'evaluation':
                (new Evaluation())->restoreById($id);
                break;
            case 'template':
                (new FormTemplate())->restore($id);
                break;
            case 'campaign':
                (new Campaign())->restore($id);
                break;
        }

        header('Location: ' . Config::BASE_URL . 'recycle-bin?restored=1');
    }

    public function deletePermanently()
    {
        Auth::requirePermission('settings.view');

        $type = $_GET['type'] ?? null;
        $id = $_GET['id'] ?? null;

        if (!$type || !$id) {
            header('Location: ' . Config::BASE_URL . 'recycle-bin');
            exit;
        }

        switch ($type) {
            case 'evaluation':
                (new Evaluation())->permanentlyDeleteById($id);
                break;
            case 'template':
                (new FormTemplate())->permanentlyDelete($id);
                break;
            case 'campaign':
                (new Campaign())->permanentlyDelete($id);
                break;
        }

        header('Location: ' . Config::BASE_URL . 'recycle-bin?deleted=1');
    }
}
