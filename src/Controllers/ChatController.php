<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Chat;
use App\Models\Campaign;
use App\Models\User;
use App\Models\CorporateClient;

class ChatController
{
    public function index()
    {
        Auth::requirePermission('calls.view'); // Reusing calls permission for chats

        $chatModel = new Chat();
        $filters = [
            'agent_id' => $_GET['agent_id'] ?? null,
            'campaign_id' => $_GET['campaign_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];

        $chats = $chatModel->getAll(50, $filters);

        $userModel = new User();
        $agents = $userModel->getByRole('agent');

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();

        require __DIR__ . '/../Views/chats/index.php';
    }

    public function create()
    {
        Auth::requirePermission('calls.view');

        $userModel = new User();
        $agents = $userModel->getByRole('agent');

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();

        $clientModel = new CorporateClient();
        $projects = $clientModel->getAll();

        require __DIR__ . '/../Views/chats/create.php';
    }

    public function store()
    {
        Auth::requirePermission('calls.view');

        $screenshotPath = null;
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/chats/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['screenshot']['name']);
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $uploadDir . $fileName)) {
                $screenshotPath = 'uploads/chats/' . $fileName;
            }
        }

        $chatModel = new Chat();
        $chatId = $chatModel->create([
            'agent_id' => $_POST['agent_id'],
            'campaign_id' => $_POST['campaign_id'],
            'project_id' => $_POST['project_id'] ?: null,
            'chat_date' => $_POST['chat_date'],
            'customer_identifier' => $_POST['customer_identifier'],
            'screenshot_path' => $screenshotPath,
            'notes' => $_POST['notes'] ?? ''
        ]);

        header('Location: ' . \App\Config\Config::BASE_URL . 'chats');
    }

    public function show()
    {
        Auth::requirePermission('calls.view');

        $id = $_GET['id'] ?? null;
        $chatModel = new Chat();
        $chat = $chatModel->findById($id);

        if (!$chat) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'chats');
            exit;
        }

        require __DIR__ . '/../Views/chats/show.php';
    }

    public function delete()
    {
        Auth::requirePermission('calls.view');

        $id = $_POST['id'] ?? null;
        if ($id) {
            $chatModel = new Chat();
            $chat = $chatModel->findById($id);
            if ($chat && $chat['screenshot_path']) {
                $fullPath = __DIR__ . '/../../public/' . $chat['screenshot_path'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $chatModel->deleteById($id);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'chats');
    }
}
