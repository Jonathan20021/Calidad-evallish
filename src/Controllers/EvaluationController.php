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
    private function formatDuration($seconds): ?string
    {
        if ($seconds === null || $seconds === '') {
            return null;
        }
        $seconds = (int) $seconds;
        $minutes = floor($seconds / 60);
        $remaining = $seconds % 60;
        return str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT);
    }

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
        if ($evaluation) {
            $evaluation['call_duration_formatted'] = $this->formatDuration($evaluation['call_duration'] ?? null);
            if (!empty($evaluation['feedback_evidence_path'])) {
                $evaluation['feedback_evidence_url'] = \App\Config\Config::BASE_URL . ltrim($evaluation['feedback_evidence_path'], '/');
            }
        }

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
        if ($evaluation) {
            $evaluation['call_duration_formatted'] = $this->formatDuration($evaluation['call_duration'] ?? null);
            if (!empty($evaluation['feedback_evidence_path'])) {
                $evaluation['feedback_evidence_url'] = \App\Config\Config::BASE_URL . ltrim($evaluation['feedback_evidence_path'], '/');
            }
        }
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
        $recordingUrl = null;
        $templates = [];
        $formFields = [];
        $template = null;

        if ($callId) {
            $callModel = new Call();
            $lockedCall = $callModel->findById($callId);
            if ($lockedCall) {
                $selectedCampaignId = $lockedCall['campaign_id'];
                $selectedAgentId = $lockedCall['agent_id'];
                if (!empty($lockedCall['recording_path'])) {
                    $recordingUrl = \App\Config\Config::BASE_URL . ltrim($lockedCall['recording_path'], '/');
                }
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
        $actionType = $_POST['action_type'] ?? null;
        $improvementAreas = $_POST['improvement_areas'] ?? '';
        $improvementPlan = $_POST['improvement_plan'] ?? '';
        $tasksCommitments = $_POST['tasks_commitments'] ?? '';
        $feedbackConfirmed = isset($_POST['feedback_confirmed']) ? 1 : 0;
        $feedbackEvidenceNote = $_POST['feedback_evidence_note'] ?? '';
        $feedbackEvidencePath = null;
        $feedbackEvidenceName = null;

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
                $maxScore = isset($field['max_score']) ? (float) $field['max_score'] : 100.0;
                if ($maxScore <= 0) {
                    $maxScore = 100.0;
                }
                $scoreValue = (float) $score;
                if ($scoreValue < 0) {
                    $scoreValue = 0.0;
                } elseif ($scoreValue > $maxScore) {
                    $scoreValue = $maxScore;
                }
                $totalScore += ($scoreValue * $weight);
                $maxPossibleScore += ($maxScore * $weight);
            }
        }

        // Percentage
        $percentage = ($maxPossibleScore > 0) ? ($totalScore / $maxPossibleScore) * 100 : 0;

        if (isset($_FILES['feedback_evidence']) && $_FILES['feedback_evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['feedback_evidence']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'No se pudo subir la evidencia de feedback.';
            } else {
                $file = $_FILES['feedback_evidence'];
                $maxBytes = 50 * 1024 * 1024;
                if ($file['size'] > $maxBytes) {
                    $errors[] = 'La evidencia supera el tamaño permitido (50MB).';
                } else {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if ($extension === '') {
                        $extension = 'bin';
                    }
                    $uploadDir = __DIR__ . '/../../public/uploads/feedback';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    try {
                        $random = bin2hex(random_bytes(6));
                    } catch (\Exception $e) {
                        $random = uniqid();
                    }
                    $filename = 'feedback_' . date('Ymd_His') . '_' . $random . '.' . $extension;
                    $targetPath = $uploadDir . '/' . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $errors[] = 'No se pudo guardar la evidencia.';
                    } else {
                        $feedbackEvidencePath = 'uploads/feedback/' . $filename;
                        $feedbackEvidenceName = $file['name'];
                    }
                }
            }
        }

        if (!empty($errors)) {
            require __DIR__ . '/../Views/evaluations/create.php';
            return;
        }

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
            'general_comments' => $generalComments,
            'action_type' => $actionType,
            'improvement_areas' => $improvementAreas,
            'improvement_plan' => $improvementPlan,
            'tasks_commitments' => $tasksCommitments,
            'feedback_confirmed' => $feedbackConfirmed,
            'feedback_confirmed_at' => $feedbackConfirmed ? date('Y-m-d H:i:s') : null,
            'feedback_evidence_path' => $feedbackEvidencePath,
            'feedback_evidence_name' => $feedbackEvidenceName,
            'feedback_evidence_note' => $feedbackEvidenceNote
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

