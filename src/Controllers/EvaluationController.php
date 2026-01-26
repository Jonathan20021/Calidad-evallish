<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\Campaign;
use App\Models\FormTemplate;
use App\Models\FormField;
use App\Models\User;
use App\Models\Call;

use Dompdf\Dompdf;
use Dompdf\Options;

class EvaluationController
{

    public function show()
    {
        Auth::requireAuth();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'evaluations');
            exit;
        }

        $evaluationModel = new Evaluation();
        $answerModel = new EvaluationAnswer();

        $evaluation = $evaluationModel->findById($id);

        // Fetch answers with field details
        $answers = $answerModel->getByEvaluationId($id);

        require __DIR__ . '/../Views/evaluations/show.php';
    }

    public function exportPdf()
    {
        Auth::requireAuth();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'evaluations');
            exit;
        }

        $evaluationModel = new Evaluation();
        $answerModel = new EvaluationAnswer();

        $evaluation = $evaluationModel->findById($id);
        $answers = $answerModel->getByEvaluationId($id);

        // buffer the output
        ob_start();
        require __DIR__ . '/../Views/evaluations/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Evaluacion-' . $evaluation['id'] . '-' . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    public function index()
    {
        Auth::requireAuth();

        $evaluationModel = new Evaluation();
        $evaluations = $evaluationModel->getAll();

        require __DIR__ . '/../Views/evaluations/index.php';
    }

    public function create()
    {
        Auth::requireAuth();

        $campaignModel = new Campaign();
        $userModel = new User();
        $templateModel = new FormTemplate();
        $fieldModel = new FormField();

        $campaigns = $campaignModel->getActive();
        $agents = $userModel->getByRole('agent');

        $selectedCampaignId = $_GET['campaign_id'] ?? null;
        $selectedAgentId = $_GET['agent_id'] ?? null;
        $selectedTemplateId = $_GET['form_template_id'] ?? null;
        $callId = $_GET['call_id'] ?? null;
        $lockedCall = null;
        $templates = [];
        $formFields = [];
        $template = null;

        if ($callId) {
            $callModel = new Call();
            $lockedCall = $callModel->findById($callId);
            if ($lockedCall) {
                $selectedCampaignId = $lockedCall['campaign_id'];
                $selectedAgentId = $lockedCall['agent_id'];
            }
        }

        if ($selectedCampaignId) {
            $templates = $templateModel->getByCampaign($selectedCampaignId);
            if (!empty($templates)) {
                if ($selectedTemplateId) {
                    foreach ($templates as $item) {
                        if ((int) $item['id'] === (int) $selectedTemplateId) {
                            $template = $item;
                            break;
                        }
                    }
                }
                if (!$template) {
                    $template = $templates[0];
                }
                $formFields = $fieldModel->getByTemplate($template['id']);
            }
        }

        require __DIR__ . '/../Views/evaluations/create.php';
    }

    public function store()
    {
        Auth::requireAuth();

        $callId = $_POST['call_id'] ?? null;
        $agentId = $_POST['agent_id'];
        $campaignId = $_POST['campaign_id'];
        $formTemplateId = $_POST['form_template_id'];
        $answers = $_POST['answers'] ?? [];
        $fieldComments = $_POST['field_comments'] ?? [];
        $generalComments = $_POST['general_comments'] ?? '';

        $callDate = null;
        $callDuration = null;
        if ($callId) {
            $callModel = new Call();
            $call = $callModel->findById($callId);
            if ($call) {
                $agentId = $call['agent_id'];
                $campaignId = $call['campaign_id'];
                $callDate = date('Y-m-d', strtotime($call['call_datetime']));
                $callDuration = $call['duration_seconds'];
            }
        }

        // Calculate Score
        $totalScore = 0;
        $maxPossibleScore = 0;

        // Fetch field details to get weights
        $fieldModel = new FormField();
        $fields = $fieldModel->getByTemplate($formTemplateId);
        $fieldsMap = [];
        foreach ($fields as $field) {
            $fieldsMap[$field['id']] = $field;
        }

        foreach ($answers as $fieldId => $score) {
            if (isset($fieldsMap[$fieldId])) {
                $field = $fieldsMap[$fieldId];

                // If it's a select field, we don't have a numeric score, so we skip it for score calc
                // Or we could assign scores to options? For now, we assume only 'score' and 'yes_no' types have numeric impact
                if ($field['field_type'] === 'select' || $field['field_type'] === 'text') {
                    continue;
                }
                // For simplicity: We sum up weighted scores.
                // Assuming Score is 0-100.
                // We normalize everything to percentage.

                // Let's assume Max Score for the form is Sum of Weights * 100?
                // Or simply: Calculate Total Percentage directly.
                // Formula: specific_score * (weight / total_weight)

                // But fieldsMap has 'weight'.

                $weight = (float) $field['weight'];
                $totalScore += ((float) $score * $weight);
                $maxPossibleScore += (100 * $weight);
            }
        }

        // Percentage
        $percentage = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;

        // Save Evaluation
        $evaluationModel = new Evaluation();
        $evaluationModel->create([
            'call_id' => $callId,
            'agent_id' => $agentId,
            'qa_id' => Auth::user()['id'],
            'campaign_id' => $campaignId,
            'form_template_id' => $formTemplateId,
            'call_date' => $callDate,
            'call_duration' => $callDuration,
            'total_score' => $totalScore,
            'max_possible_score' => $maxPossibleScore,
            'percentage' => $percentage,
            'general_comments' => $generalComments
        ]);

        $evaluationId = $evaluationModel->getLastInsertId();

        // Save Answers
        $answerModel = new EvaluationAnswer();
        foreach ($answers as $fieldId => $score) {
            $answerModel->create([
                'evaluation_id' => $evaluationId,
                'field_id' => $fieldId,
                'score_given' => $score,
                'comment' => $fieldComments[$fieldId] ?? ''
            ]);
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'evaluations');
    }
}
