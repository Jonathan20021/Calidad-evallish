<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Config\Database;
use App\Config\PoncheDatabase;
use App\Models\QaPermission;

class SettingsController
{
    public function index()
    {
        Auth::requirePermission('settings.manage');

        $db = Database::getInstance()->getConnection();
        $poncheDb = PoncheDatabase::getInstance()->getConnection();

        $stats = [
            'campaigns' => (int) $db->query("SELECT COUNT(*) FROM campaigns")->fetchColumn(),
            'agents' => (int) $poncheDb->query("SELECT COUNT(*) FROM users WHERE UPPER(role) = 'AGENT' AND is_active = 1")->fetchColumn(),
            'qas' => (int) $poncheDb->query("SELECT COUNT(*) FROM users WHERE UPPER(role) = 'QA' AND is_active = 1")->fetchColumn(),
            'forms' => (int) $db->query("SELECT COUNT(*) FROM form_templates")->fetchColumn(),
            'calls' => (int) $db->query("SELECT COUNT(*) FROM calls")->fetchColumn(),
            'evaluations' => (int) $db->query("SELECT COUNT(*) FROM evaluations")->fetchColumn()
        ];

        $qaPermissions = (new QaPermission())->get();

        require __DIR__ . '/../Views/settings/index.php';
    }

    public function updateQaPermissions()
    {
        Auth::requirePermission('settings.manage');

        $permissions = [
            'can_view_users' => isset($_POST['can_view_users']) ? 1 : 0,
            'can_create_users' => isset($_POST['can_create_users']) ? 1 : 0,
            'can_view_clients' => isset($_POST['can_view_clients']) ? 1 : 0,
            'can_manage_clients' => isset($_POST['can_manage_clients']) ? 1 : 0
        ];

        $model = new QaPermission();
        $model->update($permissions);

        header('Location: ' . \App\Config\Config::BASE_URL . 'settings?updated=qa');
        exit;
    }
}
