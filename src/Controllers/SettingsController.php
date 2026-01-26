<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Config\Database;

class SettingsController
{
    public function index()
    {
        Auth::requireRole('admin');

        $db = Database::getInstance()->getConnection();

        $stats = [
            'campaigns' => (int) $db->query("SELECT COUNT(*) FROM campaigns")->fetchColumn(),
            'agents' => (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'agent' AND active = 1")->fetchColumn(),
            'qas' => (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'qa' AND active = 1")->fetchColumn(),
            'forms' => (int) $db->query("SELECT COUNT(*) FROM form_templates")->fetchColumn(),
            'calls' => (int) $db->query("SELECT COUNT(*) FROM calls")->fetchColumn(),
            'evaluations' => (int) $db->query("SELECT COUNT(*) FROM evaluations")->fetchColumn()
        ];

        require __DIR__ . '/../Views/settings/index.php';
    }
}
