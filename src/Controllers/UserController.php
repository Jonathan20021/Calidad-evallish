<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\User;
use App\Models\CorporateClient;
use App\Models\PoncheUser;

class UserController
{
    private const ROLES = ['admin', 'client'];

    public function index()
    {
        Auth::requirePermission('users.view');

        $filters = [
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'q' => trim($_GET['q'] ?? '')
        ];
        if ($filters['role'] !== '' && !in_array($filters['role'], self::ROLES, true)) {
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
        Auth::requirePermission('users.view');

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
        Auth::requirePermission('users.view');

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
        Auth::requirePermission('users.view');

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
}
