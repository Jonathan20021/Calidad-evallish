<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\CorporateClient;
use App\Models\ClientCampaign;
use App\Models\ClientPortalSettings;
use App\Models\Campaign;
use App\Models\User;
use App\Config\Config;

class CorporateClientController
{
    private function metricOptions(): array
    {
        return [
            'total_calls' => 'Total de llamadas',
            'total_evaluations' => 'Total de evaluaciones',
            'evaluation_coverage' => 'Cobertura de evaluacion',
            'pending_evaluations' => 'Pendientes por evaluar',
            'avg_score' => 'Promedio de calidad',
            'compliance_rate' => 'Cumplimiento >= 85%',
            'critical_fails' => 'Fallos criticos (<70%)',
            'top_agent' => 'Mejor agente',
            'max_score' => 'Mejor score',
            'min_score' => 'Peor score',
            'avg_duration' => 'Duracion promedio'
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

    private function normalizeSettings(array $input): array
    {
        return [
            'show_calls' => isset($input['show_calls']) ? 1 : 0,
            'show_evaluations' => isset($input['show_evaluations']) ? 1 : 0,
            'show_ai_summary' => isset($input['show_ai_summary']) ? 1 : 0,
            'show_recordings' => isset($input['show_recordings']) ? 1 : 0,
            'show_agent_scores' => isset($input['show_agent_scores']) ? 1 : 0
        ];
    }

    public function index()
    {
        Auth::requireRole('admin');

        $clientModel = new CorporateClient();
        $clientCampaigns = new ClientCampaign();

        $clients = $clientModel->getAll();
        $clientIds = array_map(fn($client) => $client['id'], $clients);
        $campaignMap = $clientCampaigns->getCampaignsByClientIds($clientIds);

        require __DIR__ . '/../Views/clients/index.php';
    }

    public function create()
    {
        Auth::requireRole('admin');

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();

        $metricOptions = $this->metricOptions();
        $defaultMetrics = $this->defaultMetrics();
        $selectedCampaignIds = [];
        $selectedMetrics = $defaultMetrics;
        $settings = [
            'show_calls' => 1,
            'show_evaluations' => 1,
            'show_ai_summary' => 0,
            'show_recordings' => 0,
            'show_agent_scores' => 1
        ];

        $errors = [];
        $old = [
            'name' => '',
            'industry' => '',
            'contact_name' => '',
            'contact_email' => '',
            'active' => 1,
            'username' => '',
            'user_full_name' => '',
            'user_active' => 1
        ];

        require __DIR__ . '/../Views/clients/create.php';
    }

    public function store()
    {
        Auth::requireRole('admin');

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();
        $metricOptions = $this->metricOptions();

        $old = [
            'name' => trim($_POST['name'] ?? ''),
            'industry' => trim($_POST['industry'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0,
            'username' => trim($_POST['username'] ?? ''),
            'user_full_name' => trim($_POST['user_full_name'] ?? ''),
            'user_active' => isset($_POST['user_active']) ? 1 : 0
        ];
        $password = $_POST['password'] ?? '';
        $selectedCampaignIds = array_map('intval', $_POST['campaign_ids'] ?? []);
        $selectedMetrics = $_POST['metrics'] ?? $this->defaultMetrics();
        $settings = $this->normalizeSettings($_POST);

        $errors = [];
        if ($old['name'] === '') {
            $errors[] = 'El nombre del cliente es obligatorio.';
        }
        if ($old['username'] === '') {
            $errors[] = 'El usuario del portal es obligatorio.';
        }
        if ($password === '') {
            $errors[] = 'La contrasena del portal es obligatoria.';
        }
        if ($old['user_full_name'] === '') {
            $errors[] = 'El nombre del usuario del portal es obligatorio.';
        }

        $userModel = new User();
        if ($old['username'] !== '' && $userModel->findByUsername($old['username'])) {
            $errors[] = 'El usuario ya existe. Use otro.';
        }

        if (!empty($errors)) {
            require __DIR__ . '/../Views/clients/create.php';
            return;
        }

        $clientModel = new CorporateClient();
        $clientId = $clientModel->create([
            'name' => $old['name'],
            'industry' => $old['industry'],
            'contact_name' => $old['contact_name'],
            'contact_email' => $old['contact_email'],
            'active' => $old['active']
        ]);

        $userModel->create([
            'username' => $old['username'],
            'password' => $password,
            'full_name' => $old['user_full_name'],
            'role' => 'client',
            'client_id' => $clientId
        ]);

        $clientCampaigns = new ClientCampaign();
        $clientCampaigns->setCampaigns($clientId, $selectedCampaignIds);

        $settingsModel = new ClientPortalSettings();
        $metrics = array_values(array_intersect(array_keys($metricOptions), $selectedMetrics));
        $settingsModel->upsert($clientId, array_merge($settings, [
            'metrics_json' => json_encode($metrics, JSON_UNESCAPED_UNICODE)
        ]));

        header('Location: ' . Config::BASE_URL . 'clients');
    }

    public function edit()
    {
        Auth::requireRole('admin');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . Config::BASE_URL . 'clients');
            exit;
        }

        $clientModel = new CorporateClient();
        $client = $clientModel->findById($id);
        if (!$client) {
            header('Location: ' . Config::BASE_URL . 'clients');
            exit;
        }

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();

        $userModel = new User();
        $clientUsers = $userModel->getByClientId($id);
        $portalUser = $clientUsers[0] ?? null;

        $clientCampaigns = new ClientCampaign();
        $selectedCampaignIds = $clientCampaigns->getCampaignIds($id);

        $metricOptions = $this->metricOptions();
        $settingsModel = new ClientPortalSettings();
        $settings = $settingsModel->findByClientId($id);
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
        $selectedMetrics = json_decode($settings['metrics_json'] ?? '', true) ?: $this->defaultMetrics();

        $errors = [];

        require __DIR__ . '/../Views/clients/edit.php';
    }

    public function update()
    {
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . Config::BASE_URL . 'clients');
            exit;
        }

        $clientModel = new CorporateClient();
        $client = $clientModel->findById($id);
        if (!$client) {
            header('Location: ' . Config::BASE_URL . 'clients');
            exit;
        }

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();
        $metricOptions = $this->metricOptions();

        $clientData = [
            'name' => trim($_POST['name'] ?? ''),
            'industry' => trim($_POST['industry'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        $portalData = [
            'username' => trim($_POST['username'] ?? ''),
            'full_name' => trim($_POST['user_full_name'] ?? ''),
            'active' => isset($_POST['user_active']) ? 1 : 0
        ];
        $password = $_POST['password'] ?? '';

        $selectedCampaignIds = array_map('intval', $_POST['campaign_ids'] ?? []);
        $selectedMetrics = $_POST['metrics'] ?? $this->defaultMetrics();
        $settings = $this->normalizeSettings($_POST);

        $errors = [];
        if ($clientData['name'] === '') {
            $errors[] = 'El nombre del cliente es obligatorio.';
        }
        if ($portalData['username'] === '') {
            $errors[] = 'El usuario del portal es obligatorio.';
        }
        if ($portalData['full_name'] === '') {
            $errors[] = 'El nombre del usuario del portal es obligatorio.';
        }

        $userModel = new User();
        $clientUsers = $userModel->getByClientId($id);
        $portalUser = $clientUsers[0] ?? null;

        if ($portalData['username'] !== '') {
            $existing = $userModel->findByUsername($portalData['username']);
            if ($existing && (!$portalUser || (int) $existing['id'] !== (int) $portalUser['id'])) {
                $errors[] = 'El usuario ya existe. Use otro.';
            }
        }

        if (!empty($errors)) {
            $client = array_merge($client, $clientData);
            $selectedMetrics = $selectedMetrics;
            require __DIR__ . '/../Views/clients/edit.php';
            return;
        }

        $clientModel->update($id, $clientData);

        if ($portalUser) {
            $userModel->update($portalUser['id'], [
                'username' => $portalData['username'],
                'full_name' => $portalData['full_name'],
                'active' => $portalData['active']
            ]);
            if ($password !== '') {
                $userModel->updatePassword($portalUser['id'], $password);
            }
        } else {
            $userModel->create([
                'username' => $portalData['username'],
                'password' => $password !== '' ? $password : bin2hex(random_bytes(4)),
                'full_name' => $portalData['full_name'],
                'role' => 'client',
                'client_id' => $id
            ]);
        }

        $clientCampaigns = new ClientCampaign();
        $clientCampaigns->setCampaigns($id, $selectedCampaignIds);

        $settingsModel = new ClientPortalSettings();
        $metrics = array_values(array_intersect(array_keys($metricOptions), $selectedMetrics));
        $settingsModel->upsert($id, array_merge($settings, [
            'metrics_json' => json_encode($metrics, JSON_UNESCAPED_UNICODE)
        ]));

        header('Location: ' . Config::BASE_URL . 'clients');
    }
}
