<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Campaign;
use App\Models\FormTemplate;
use App\Models\FormField;

class FormTemplateController
{

    public function index()
    {
        Auth::requirePermission('forms.view');

        $templateModel = new FormTemplate();
        $templates = $templateModel->getAllWithCampaign();

        require __DIR__ . '/../Views/form_templates/index.php';
    }

    public function create()
    {
        Auth::requirePermission('forms.manage');

        $selectedCampaignIds = isset($_GET['campaign_id']) ? [$_GET['campaign_id']] : [];
        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();

        $template = null;
        $fields = [];
        $isEditing = false;

        require __DIR__ . '/../Views/form_templates/create.php';
    }

    public function edit()
    {
        Auth::requirePermission('forms.manage');

        $templateId = $_GET['id'] ?? null;
        if (!$templateId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        $templateModel = new FormTemplate();
        $template = $templateModel->findById($templateId);
        if (!$template) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();

        // Get assigned campaigns for this template
        $assignedCampaigns = $templateModel->getCampaignsByTemplate($templateId);
        $selectedCampaignIds = array_column($assignedCampaigns, 'id');

        $fieldModel = new FormField();
        $fields = $fieldModel->getByTemplate($template['id']);
        $isEditing = true;

        require __DIR__ . '/../Views/form_templates/create.php';
    }

    public function store()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $campaignIds = $_POST['campaign_ids'] ?? [];
        $title = $_POST['title'];
        $items = json_decode($_POST['items_json'] ?? '[]', true);

        $templateModel = new FormTemplate();
        $fieldModel = new FormField();

        // 1. Create New Template
        $templateModel->create([
            'title' => $title,
            'active' => 1
        ]);

        $templateId = $templateModel->getLastInsertId();

        // 2. Assign campaigns
        if (!empty($campaignIds) && is_array($campaignIds)) {
            $templateModel->assignCampaigns($templateId, $campaignIds);
        }

        // 3. Create Fields
        if ($items && is_array($items)) {
            foreach ($items as $index => $item) {
                $fieldModel->create([
                    'template_id' => $templateId,
                    'label' => $item['label'],
                    'field_type' => $item['type'],
                    'options' => $item['options'] ?? null,
                    'max_score' => $item['max_score'] ?? 0,
                    'weight' => $item['weight'] ?? 1,
                    'field_order' => $index,
                    'required' => 1
                ]);
            }
        }

        header("Location: " . \App\Config\Config::BASE_URL . "form-templates");
    }

    public function update()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $templateId = $_POST['template_id'] ?? null;
        if (!$templateId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        $campaignIds = $_POST['campaign_ids'] ?? [];
        $title = $_POST['title'];
        $items = json_decode($_POST['items_json'] ?? '[]', true);

        $templateModel = new FormTemplate();
        $fieldModel = new FormField();

        $templateModel->updateTitle($templateId, $title);

        // Update campaign assignments
        if (is_array($campaignIds)) {
            $templateModel->assignCampaigns($templateId, $campaignIds);
        }

        $keepIds = [];
        if ($items && is_array($items)) {
            foreach ($items as $index => $item) {
                $payload = [
                    'template_id' => $templateId,
                    'label' => $item['label'],
                    'field_type' => $item['type'],
                    'options' => $item['options'] ?? null,
                    'max_score' => $item['max_score'] ?? 0,
                    'weight' => $item['weight'] ?? 1,
                    'field_order' => $index,
                    'required' => 1
                ];

                if (!empty($item['id'])) {
                    $fieldModel->update($item['id'], $payload);
                    $keepIds[] = (int) $item['id'];
                } else {
                    $fieldModel->create($payload);
                    $keepIds[] = (int) $fieldModel->getLastInsertId();
                }
            }
        }

        $fieldModel->deleteMissingByTemplate($templateId, $keepIds);

        header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates/edit?id=' . $templateId);
    }

    public function toggle()
    {
        Auth::requirePermission('forms.manage');

        $templateId = $_POST['id'] ?? null;
        $active = isset($_POST['active']) ? (int) $_POST['active'] : null;
        if ($templateId === null || $active === null) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        $templateModel = new FormTemplate();
        $templateModel->setActive($templateId, $active);

        header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
    }

    public function delete()
    {
        Auth::requirePermission('forms.manage');

        $templateId = $_POST['id'] ?? null;
        if (!$templateId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        $templateModel = new FormTemplate();
        $templateModel->delete($templateId);

        header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates?success=deleted');
        exit;
    }

    public function duplicate()
    {
        Auth::requirePermission('forms.manage');

        $templateId = $_POST['id'] ?? null;
        if (!$templateId) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        $templateModel = new FormTemplate();
        $fieldModel = new FormField();

        $template = $templateModel->findById($templateId);
        if (!$template) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates');
            exit;
        }

        // Get assigned campaigns
        $assignedCampaigns = $templateModel->getCampaignsByTemplate($templateId);
        $campaignIds = array_column($assignedCampaigns, 'id');

        $templateModel->create([
            'title' => $template['title'] . ' (Copia)',
            'description' => $template['description'] ?? '',
            'active' => 0
        ]);

        $newTemplateId = $templateModel->getLastInsertId();

        // Assign same campaigns to duplicated template
        if (!empty($campaignIds)) {
            $templateModel->assignCampaigns($newTemplateId, $campaignIds);
        }

        $fields = $fieldModel->getByTemplate($templateId);
        foreach ($fields as $index => $field) {
            $fieldModel->create([
                'template_id' => $newTemplateId,
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'options' => $field['options'],
                'max_score' => $field['max_score'],
                'weight' => $field['weight'],
                'field_order' => $index,
                'required' => $field['required']
            ]);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'form-templates/edit?id=' . $newTemplateId);
    }
}
