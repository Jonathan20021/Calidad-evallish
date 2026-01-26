<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\User;
use App\Models\Campaign;

class AgentController
{
    public function index()
    {
        Auth::requireAuth(); // Open to all or just admins? Design implies management, so Admin/QA

        $userModel = new User();
        // Since User model doesn't have complex relationship methods yet, we fetch all agents manually for now
        // Enhancing User model would be better, but sticking to existing pattern first.
        $agents = $userModel->getByRole('agent');

        // Enhance agent data with campaign info if possible (needs join in User model ideally)
        // For now, listing raw agent data.

        require __DIR__ . '/../Views/agents/index.php';
    }

    public function create()
    {
        Auth::requireRole('admin');

        // Need campaigns to assign agent to (if design supports it)
        // Design shows "Campaña" column.
        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();

        require __DIR__ . '/../Views/agents/create.php';
    }

    public function store()
    {
        Auth::requireRole('admin');

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? ''; // Not in schema yet, but design has it. Ignoring for now or adding column?
        // Schema: username, password_hash, full_name, role, active
        $fullName = $_POST['full_name'] ?? '';
        $password = $_POST['password'] ?? '';
        $campaignId = $_POST['campaign_id'] ?? null; // Not in users table. Users <-> Campaigns relationship missing.
        // Ignoring Campaign assingment in DB for now, just UI placeholder?
        // Or adding updated_at/created_at

        // Simple validation
        if (empty($username) || empty($password) || empty($fullName)) {
            // Flash error?
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/create?error=missing_fields');
            exit;
        }

        $userModel = new User();
        // Check if username exists
        if ($userModel->findByUsername($username)) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents/create?error=username_exists');
            exit;
        }

        $userModel->create([
            'username' => $username,
            'password' => $password,
            'full_name' => $fullName,
            'role' => 'agent'
        ]);

        header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
    }
    public function edit()
    {
        Auth::requireRole('admin');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
            exit;
        }

        $userModel = new User();
        $agent = $userModel->findById($id);

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getActive();

        require __DIR__ . '/../Views/agents/edit.php';
    }

    public function update()
    {
        Auth::requireRole('admin');
        $id = $_POST['id'];
        $fullName = $_POST['full_name'];
        $password = $_POST['password'] ?? '';

        $userModel = new User();

        // Update basic info
        // Need to add update method to User model first
        // $userModel->update($id, ['full_name' => $fullName]);

        // If password provided, update it
        // if (!empty($password)) $userModel->updatePassword($id, $password);

        header('Location: ' . \App\Config\Config::BASE_URL . 'agents');
    }
}
