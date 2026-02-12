<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\User;
use App\Models\CorporateClient;
use App\Models\PoncheUser;
use App\Models\UserPermission;

class UserController
{
    private const ROLES = ['admin', 'client'];
    private const FILTER_ROLES = ['admin', 'client', 'qa', 'agent'];

    public function index()
    {
        Auth::requirePermission('users.view');

        $filters = [
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'q' => trim($_GET['q'] ?? '')
        ];
        if ($filters['role'] !== '' && !in_array($filters['role'], self::FILTER_ROLES, true)) {
            $filters['role'] = '';
        }

        $userModel = new User();
        $users = $userModel->getAllFiltered($filters);
        $clientModel = new CorporateClient();
        $clientMap = [];
        foreach ($clientModel->getAll() as $client) {
            $clientMap[(int) $client['id']] = $client['name'];
        }

        require __DIR__ . '/../Views/users/index.php';
    }

    public function create()
    {
        Auth::requirePermission('users.create');

        $clientModel = new CorporateClient();
        $clients = $clientModel->getAll();

        require __DIR__ . '/../Views/users/create.php';
    }

    public function store()
    {
        Auth::requirePermission('users.create');

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        $clientId = $_POST['client_id'] ?? null;
        $active = isset($_POST['active']) ? 1 : 0;

        if ($role !== 'client') {
            $clientId = null;
        }

        if ($username === '' || $fullName === '' || $password === '') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/create?error=missing_fields');
            exit;
        }

        if (!in_array($role, self::ROLES, true)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/create?error=invalid_role');
            exit;
        }

        if ($role === 'client' && !$clientId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/create?error=missing_client');
            exit;
        }

        $userModel = new User();
        $poncheUserModel = new PoncheUser();
        if ($userModel->findByUsernameAny($username)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/create?error=username_exists');
            exit;
        }
        if ($poncheUserModel->findByUsernameAny($username)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/create?error=username_exists');
            exit;
        }

        $userModel->create([
            'username' => $username,
            'password' => $password,
            'full_name' => $fullName,
            'role' => $role,
            'client_id' => $clientId,
            'active' => $active
        ]);

        header('Location: ' . \App\Config\Config::BASE_URL . 'users');
    }

    public function edit()
    {
        Auth::requirePermission('users.create');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        $userModel = new User();
        $user = $userModel->findById($id);
        if (!$user) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        $clientModel = new CorporateClient();
        $clients = $clientModel->getAll();

        require __DIR__ . '/../Views/users/edit.php';
    }

    public function update()
    {
        Auth::requirePermission('users.create');

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        $clientId = $_POST['client_id'] ?? null;
        $active = isset($_POST['active']) ? 1 : 0;

        if ($role !== 'client') {
            $clientId = null;
        }

        if ($username === '' || $fullName === '') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/edit?id=' . $id . '&error=missing_fields');
            exit;
        }

        if (!in_array($role, self::ROLES, true)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/edit?id=' . $id . '&error=invalid_role');
            exit;
        }

        if ($role === 'client' && !$clientId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/edit?id=' . $id . '&error=missing_client');
            exit;
        }

        $userModel = new User();
        $existing = $userModel->findByUsernameAny($username);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/edit?id=' . $id . '&error=username_exists');
            exit;
        }
        $poncheUserModel = new PoncheUser();
        $poncheExisting = $poncheUserModel->findByUsernameAny($username);
        if ($poncheExisting) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users/edit?id=' . $id . '&error=username_exists');
            exit;
        }

        $userModel->updateAdmin($id, [
            'username' => $username,
            'full_name' => $fullName,
            'role' => $role,
            'client_id' => $clientId,
            'active' => $active
        ]);

        if ($password !== '') {
            $userModel->updatePassword($id, $password);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'users');
    }

    public function toggle()
    {
        Auth::requirePermission('users.create');

        $id = $_POST['id'] ?? null;
        $active = isset($_POST['active']) ? (int) $_POST['active'] : null;

        if (!$id || ($active !== 0 && $active !== 1)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        $currentUser = Auth::user();
        if ($currentUser && (int) $currentUser['id'] === (int) $id && $active === 0) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users?error=self_disable');
            exit;
        }

        $userModel = new User();
        $userModel->setActive($id, $active);

        header('Location: ' . \App\Config\Config::BASE_URL . 'users');
    }

    public function editPermissions()
    {
        Auth::requirePermission('users.create');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        // Solo usuarios de ponche (excepto agents) pueden tener permisos configurables
        $source = $user['source'] ?? 'quality';
        $role = strtolower($user['role'] ?? '');

        if ($source !== 'ponche' || $role === 'agent') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users?error=invalid_user_permissions');
            exit;
        }

        $permissionModel = new UserPermission();
        $permissions = $permissionModel->getByUserId($id);

        // Si no tiene permisos, inicializar con valores por defecto
        if ($permissions === null) {
            $permissions = [
                'can_view_users' => 0,
                'can_create_users' => 0,
                'can_view_clients' => 0,
                'can_manage_clients' => 0,
                'can_view_campaigns' => 0,
                'can_manage_campaigns' => 0,
                'can_view_evaluations' => 0,
                'can_create_evaluations' => 0,
                'can_view_reports' => 0,
                'can_manage_settings' => 0,
                'can_view_training' => 0,
                'can_manage_training' => 0,
                'can_view_agents' => 0,
                'can_manage_agents' => 0,
                'can_view_forms' => 0,
                'can_manage_forms' => 0,
                'can_view_ai_criteria' => 0,
                'can_manage_ai_criteria' => 0,
                'can_view_calls' => 0,
                'can_manage_calls' => 0,
            ];
        }

        require __DIR__ . '/../Views/users/edit_permissions.php';
    }

    public function updatePermissions()
    {
        Auth::requirePermission('users.create');

        $id = $_POST['id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users');
            exit;
        }

        // Solo usuarios de ponche (excepto agents) pueden tener permisos configurables
        $source = $user['source'] ?? 'quality';
        $role = strtolower($user['role'] ?? '');

        if ($source !== 'ponche' || $role === 'agent') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'users?error=invalid_user_permissions');
            exit;
        }

        $permissions = [
            'can_view_users' => isset($_POST['can_view_users']) ? 1 : 0,
            'can_create_users' => isset($_POST['can_create_users']) ? 1 : 0,
            'can_view_clients' => isset($_POST['can_view_clients']) ? 1 : 0,
            'can_manage_clients' => isset($_POST['can_manage_clients']) ? 1 : 0,
            'can_view_campaigns' => isset($_POST['can_view_campaigns']) ? 1 : 0,
            'can_manage_campaigns' => isset($_POST['can_manage_campaigns']) ? 1 : 0,
            'can_view_evaluations' => isset($_POST['can_view_evaluations']) ? 1 : 0,
            'can_create_evaluations' => isset($_POST['can_create_evaluations']) ? 1 : 0,
            'can_view_reports' => isset($_POST['can_view_reports']) ? 1 : 0,
            'can_manage_settings' => isset($_POST['can_manage_settings']) ? 1 : 0,
            'can_view_training' => isset($_POST['can_view_training']) ? 1 : 0,
            'can_manage_training' => isset($_POST['can_manage_training']) ? 1 : 0,
            'can_view_agents' => isset($_POST['can_view_agents']) ? 1 : 0,
            'can_manage_agents' => isset($_POST['can_manage_agents']) ? 1 : 0,
            'can_view_forms' => isset($_POST['can_view_forms']) ? 1 : 0,
            'can_manage_forms' => isset($_POST['can_manage_forms']) ? 1 : 0,
            'can_view_ai_criteria' => isset($_POST['can_view_ai_criteria']) ? 1 : 0,
            'can_manage_ai_criteria' => isset($_POST['can_manage_ai_criteria']) ? 1 : 0,
            'can_view_calls' => isset($_POST['can_view_calls']) ? 1 : 0,
            'can_manage_calls' => isset($_POST['can_manage_calls']) ? 1 : 0,
        ];

        $permissionModel = new UserPermission();
        $permissionModel->createOrUpdate($id, $permissions);

        header('Location: ' . \App\Config\Config::BASE_URL . 'users/permissions/' . $id . '?success=1');
    }
}
