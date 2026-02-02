<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Config\Database;
use App\Config\PoncheDatabase;

class SettingsController
{
    public function index()
    {
        Auth::requireRole('admin');

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

        require __DIR__ . '/../Views/settings/index.php';
    }
}
