<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Campaign;

class CampaignController
{

    public function index()
    {
        Auth::requireRole('admin');

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();

        require __DIR__ . '/../Views/campaigns/index.php';
    }

    public function create()
    {
        Auth::requireRole('admin');
        require __DIR__ . '/../Views/campaigns/create.php';
    }

    public function store()
    {
        Auth::requireRole('admin');

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name)) {
            return;
        }

        $campaignModel = new Campaign();
        $campaignModel->create([
            'name' => $name,
            'description' => $description,
            'active' => 1
        ]);
        $campaignId = $campaignModel->getLastInsertId();
        if ($campaignId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates/create?campaign_id=' . $campaignId);
            return;
        }
        header('Location: ' . \App\Config\Config::BASE_URL . 'campaigns');
    }

    public function edit()
    {
        Auth::requireRole('admin');
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'campaigns');
            exit;
        }

        $campaignModel = new Campaign();
        $campaign = $campaignModel->findById($id);

        if (!$campaign) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'campaigns');
            exit;
        }

        require __DIR__ . '/../Views/campaigns/edit.php';
    }

    public function update()
    {
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $active = isset($_POST['active']) ? 1 : 0;

        if (!$id || empty($name)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'campaigns');
            exit;
        }

        $campaignModel = new Campaign();
        $campaignModel->update($id, [
            'name' => $name,
            'description' => $description,
            'active' => $active
        ]);

        header('Location: ' . \App\Config\Config::BASE_URL . 'campaigns');
    }
}
