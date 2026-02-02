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
        Auth::requireAnyRole(['admin', 'qa']);

        $templateModel = new FormTemplate();
        $templates = $templateModel->getAllWithCampaign();

        require __DIR__ . '/../Views/form_templates/index.php';
    }

    public function create()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $campaignId = $_GET['campaign_id'] ?? null;
        $campaignModel = new Campaign();
        $campaigns = $campaignModel->getAll();
        $campaign = $campaignId ? $campaignModel->findById($campaignId) : null;

        $template = null;
        $fields = [];
        $isEditing = false;

        require __DIR__ . '/../Views/form_templates/create.php';
    }

    public function edit()
    {
        Auth::requireAnyRole(['admin', 'qa']);

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
        $campaign = $campaignModel->findById($template['campaign_id']);
        $campaigns = $campaignModel->getAll();

        $fieldModel = new FormField();
        $fields = $fieldModel->getByTemplate($template['id']);
        $isEditing = true;

        require __DIR__ . '/../Views/form_templates/create.php';
    }

    public function store()
    {
        Auth::requireAnyRole(['admin', 'qa']);

        $campaignId = $_POST['campaign_id'];
        $title = $_POST['title'];
        $items = json_decode($_POST['items_json'] ?? '[]', true);

        $templateModel = new FormTemplate();
        $fieldModel = new FormField();

        // 1. Create New Template
        $templateModel->create([
            'campaign_id' => $campaignId,
            'title' => $title,
            'active' => 1
        ]);

        $templateId = $templateModel->getLastInsertId();

        // 2. Create Fields
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

        $title = $_POST['title'];
        $items = json_decode($_POST['items_json'] ?? '[]', true);

        $templateModel = new FormTemplate();
        $fieldModel = new FormField();

        $templateModel->updateTitle($templateId, $title);

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
        Auth::requireAnyRole(['admin', 'qa']);

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

    public function duplicate()
    {
        Auth::requireAnyRole(['admin', 'qa']);

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

        $templateModel->create([
            'campaign_id' => $template['campaign_id'],
            'title' => $template['title'] . ' (Copia)',
            'description' => $template['description'] ?? '',
            'active' => 0
        ]);

        $newTemplateId = $templateModel->getLastInsertId();
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
