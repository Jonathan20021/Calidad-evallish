<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\PoncheUser;
use App\Models\User;
use App\Models\Campaign;

class AgentController
{
    public function index()
    {
        Auth::requireAuth();

        $userModel = new PoncheUser();
        $agents = $userModel->getByRoles(['AGENT', 'QA'], false);
        $agents = array_map(function ($row) {
            $row['role'] = strtolower($row['role']);
            $row['active'] = (int) ($row['is_active'] ?? 1);
            return $row;
        }, $agents);

        require __DIR__ . '/../Views/agents/index.php';
    }

    public function create()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();

        require __DIR__ . '/../Views/agents/create.php';
    }

    public function store()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = strtoupper(trim($_POST['role'] ?? 'AGENT'));
        $active = isset($_POST['active']) ? 1 : 0;

        if ($username === '' || $password === '' || $fullName === '') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/create?error=missing_fields');
            exit;
        }

        if (!in_array($role, ['AGENT', 'QA'], true)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/create?error=invalid_role');
            exit;
        }

        $userModel = new PoncheUser();
        if ($userModel->findByUsernameAny($username)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/create?error=username_exists');
            exit;
        }
        $localUser = new \App\Models\User();
        if ($localUser->findByUsernameAny($username)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/create?error=username_exists');
            exit;
        }

        $userModel->create([
            'username' => $username,
            'password' => $password,
            'full_name' => $fullName,
            'role' => $role,
            'is_active' => $active
        ]);

        $poncheUser = $userModel->findByUsernameAny($username);
        if ($poncheUser) {
            (new User())->syncFromPoncheUser($poncheUser);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
    }

    public function edit()
    {
        Auth::requireAnyRole(['admin', 'qa']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
            exit;
        }

        $userModel = new PoncheUser();
        $agent = $userModel->findById((int) $id);
        if (!$agent) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
            exit;
        }
        $agent['role'] = strtolower($agent['role']);
        $agent['active'] = (int) ($agent['is_active'] ?? 1);

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();

        require __DIR__ . '/../Views/agents/edit.php';
    }

    public function update()
    {
        Auth::requireAnyRole(['admin', 'qa']);
        $id = (int) ($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = strtoupper(trim($_POST['role'] ?? 'AGENT'));
        $active = isset($_POST['active']) ? 1 : 0;

        if ($id === 0 || $fullName === '') {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
            exit;
        }

        if (!in_array($role, ['AGENT', 'QA'], true)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/edit?id=' . $id . '&error=invalid_role');
            exit;
        }

        $userModel = new PoncheUser();
        $userModel->update($id, [
            'full_name' => $fullName,
            'role' => $role,
            'is_active' => $active
        ]);

        if ($password !== '') {
            $userModel->updatePassword($id, $password);
        }

        $poncheUser = $userModel->findById($id);
        if ($poncheUser) {
            (new User())->syncFromPoncheUser($poncheUser);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
    }

    public function toggle()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $active = isset($_POST['active']) ? (int) $_POST['active'] : null;

        if ($id === 0 || ($active !== 0 && $active !== 1)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
            exit;
        }

        $userModel = new PoncheUser();
        $userModel->setActive($id, $active);

        $poncheUser = $userModel->findById($id);
        if ($poncheUser) {
            (new User())->syncFromPoncheUser($poncheUser);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
    }
}
