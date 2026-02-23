<?php

namespace App\Controllers;

use App\Config\Config;
use App\Helpers\Auth;
use App\Models\Call;
use App\Models\Campaign;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\TrainingExam;
use App\Models\TrainingExamAnswer;
use App\Models\TrainingExamQuestion;
use App\Models\TrainingRoleplay;
use App\Models\TrainingRoleplayFeedback;
use App\Models\TrainingRoleplayCoachNote;
use App\Models\TrainingRoleplayMessage;
use App\Models\TrainingRubric;
use App\Models\TrainingRubricItem;
use App\Models\TrainingNotification;
use App\Models\TrainingScript;
use App\Models\User;
use App\Models\QaRetraining;
use App\Models\QaRetrainingModule;
use App\Models\QaRetrainingProgress;
use App\Services\EmailService;
use App\Services\GeminiService;

class TrainingController
{
    public function index()
    {
        Auth::requirePermission('training.view');

        $role = Auth::user()['role'] ?? '';
        $scriptModel = new TrainingScript();
        $scripts = $scriptModel->getActive(50);

        if ($role === 'agent') {
            $roleplayModel = new TrainingRoleplay();
            $examModel = new TrainingExam();
            $agentRoleplays = $roleplayModel->getByAgentId(Auth::user()['id'], 50);
            $agentExams = $examModel->getByAgentId(Auth::user()['id'], 50);

            require __DIR__ . '/../Views/training/index.php';
            return;
        }

        $evaluationModel = new Evaluation();
        $topCalls = $evaluationModel->getTopEvaluatedCalls(10);
        $roleplayModel = new TrainingRoleplay();
        $examModel = new TrainingExam();
        $userModel = new User();
        $campaignModel = new Campaign();
        $rubricModel = new TrainingRubric();

        $agents = $userModel->getByRole('agent');
        $campaigns = $campaignModel->getActive();
        $recentRoleplays = $roleplayModel->getRecent(10);
        $recentExams = $examModel->getRecent(10);
        $roleplayStats = $roleplayModel->getStats();
        $examStats = $examModel->getStats();
        $rubrics = $rubricModel->getAllActive();

        require __DIR__ . '/../Views/training/index.php';
    }

    public function uploadScript()
    {
        Auth::requirePermission('training.manage');

        $title = trim($_POST['title'] ?? '');
        $campaignId = $_POST['campaign_id'] ?? null;
        $scriptText = trim($_POST['script_text'] ?? '');
        $scenarioText = trim($_POST['scenario_text'] ?? '');

        if ($title === '') {
            $this->redirectWithMessage('training', 'error', 'Titulo requerido.');
            return;
        }

        $filePath = null;
        $originalFilename = null;
        if (!empty($_FILES['script_file']['name'])) {
            $file = $_FILES['script_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->redirectWithMessage('training', 'error', 'No se pudo subir el archivo.');
                return;
            }
            if ($file['size'] > 1024 * 1024) {
                $this->redirectWithMessage('training', 'error', 'El archivo supera el limite de 1MB.');
                return;
            }
            $originalFilename = $file['name'];
            $uploadDir = __DIR__ . '/../../public/uploads/training_scripts';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($extension === '') {
                $extension = 'txt';
            }
            $allowed = ['txt', 'md', 'csv'];
            if (!in_array($extension, $allowed, true)) {
                $this->redirectWithMessage('training', 'error', 'Formato no permitido. Usa txt, md o csv.');
                return;
            }
            try {
                $random = bin2hex(random_bytes(4));
            } catch (\Exception $e) {
                $random = uniqid();
            }
            $filename = 'script_' . date('Ymd_His') . '_' . $random . '.' . $extension;
            $targetPath = $uploadDir . '/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $this->redirectWithMessage('training', 'error', 'No se pudo guardar el archivo.');
                return;
            }
            $filePath = 'uploads/training_scripts/' . $filename;
            if ($scriptText === '') {
                $fileContents = file_get_contents($targetPath);
                if ($fileContents !== false) {
                    $scriptText = trim($fileContents);
                }
            }
        }

        if ($scriptText === '') {
            $this->redirectWithMessage('training', 'error', 'Ingresa un guion o adjunta un archivo.');
            return;
        }

        $scriptModel = new TrainingScript();
        $scriptModel->create([
            'title' => $title,
            'script_text' => $scriptText,
            'scenario_text' => $scenarioText !== '' ? $scenarioText : null,
            'source_type' => $filePath ? 'upload' : 'manual',
            'campaign_id' => $campaignId ?: null,
            'created_by' => Auth::user()['id'],
            'file_path' => $filePath,
            'original_filename' => $originalFilename
        ]);

        $this->redirectWithMessage('training', 'success', 'Guion cargado.');
    }

    public function createScriptFromBestCall()
    {
        Auth::requirePermission('training.manage');

        $callId = $_POST['call_id'] ?? null;
        if (!$callId) {
            $this->redirectWithMessage('training', 'error', 'Selecciona una llamada.');
            return;
        }

        $callModel = new Call();
        $call = $callModel->findById($callId);
        if (!$call || empty($call['recording_path'])) {
            $this->redirectWithMessage('training', 'error', 'La llamada no tiene grabacion.');
            return;
        }

        $evaluationModel = new Evaluation();
        $evaluation = $evaluationModel->findByCallId($callId);

        $audioPath = __DIR__ . '/../../public/' . ltrim($call['recording_path'], '/');
        $service = new GeminiService();

        try {
            $data = $service->generateTrainingScriptFromAudio($audioPath, [
                'agent' => $call['agent_name'] ?? '',
                'campaign' => $call['campaign_name'] ?? '',
                'score' => $evaluation['percentage'] ?? '',
                'notes' => $call['notes'] ?? ''
            ]);
        } catch (\Throwable $e) {
            $this->redirectWithMessage('training', 'error', 'Error IA: ' . $e->getMessage());
            return;
        }

        $title = $data['title'] ?? ('Guion QA ' . date('Y-m-d H:i'));
        $scriptText = $data['script'] ?? '';
        if ($scriptText === '') {
            $this->redirectWithMessage('training', 'error', 'La IA no devolvio un guion.');
            return;
        }

        $persona = $data['customer_profile'] ?? null;
        $personaJson = is_array($persona) ? json_encode($persona, JSON_UNESCAPED_UNICODE) : (is_string($persona) ? $persona : null);

        $scriptModel = new TrainingScript();
        $scriptModel->create([
            'title' => $title,
            'script_text' => $scriptText,
            'scenario_text' => $data['scenario'] ?? null,
            'persona_json' => $personaJson,
            'source_type' => 'best_call',
            'call_id' => $call['id'],
            'campaign_id' => $call['campaign_id'] ?? null,
            'created_by' => Auth::user()['id']
        ]);

        $this->redirectWithMessage('training', 'success', 'Guion creado con IA.');
    }

    public function startRoleplay()
    {
        Auth::requirePermission('training.view');

        $scriptId = $_GET['script_id'] ?? null;
        if (!$scriptId) {
            $this->redirectWithMessage('training', 'error', 'Selecciona un guion.');
            return;
        }

        $agentId = $_GET['agent_id'] ?? Auth::user()['id'];
        $objectivesText = trim($_GET['objectives_text'] ?? '');
        $toneText = trim($_GET['tone_text'] ?? '');
        $obstaclesText = trim($_GET['obstacles_text'] ?? '');
        $rubricId = $_GET['rubric_id'] ?? null;
        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $agentId !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $scriptModel = new TrainingScript();
        $script = $scriptModel->findById($scriptId);
        if (!$script) {
            $this->redirectWithMessage('training', 'error', 'Guion no encontrado.');
            return;
        }

        $roleplayModel = new TrainingRoleplay();
        $roleplayModel->create([
            'script_id' => $script['id'],
            'agent_id' => $agentId,
            'qa_id' => (Auth::user()['role'] ?? '') === 'agent' ? null : Auth::user()['id'],
            'campaign_id' => $script['campaign_id'] ?? null,
            'status' => 'active',
            'objectives_text' => $objectivesText !== '' ? $objectivesText : null,
            'tone_text' => $toneText !== '' ? $toneText : null,
            'obstacles_text' => $obstaclesText !== '' ? $obstaclesText : null,
            'rubric_id' => $rubricId ? (int) $rubricId : null
        ]);

        $roleplayId = $roleplayModel->getLastInsertId();
        header('Location: ' . Config::BASE_URL . 'training/roleplay?session_id=' . $roleplayId);
    }

    public function showRoleplay()
    {
        Auth::requirePermission('training.view');

        $roleplayId = $_GET['session_id'] ?? null;
        if (!$roleplayId) {
            $this->redirectWithMessage('training', 'error', 'Sesion no encontrada.');
            return;
        }

        $roleplayModel = new TrainingRoleplay();
        $roleplay = $roleplayModel->findById($roleplayId);
        if (!$roleplay) {
            $this->redirectWithMessage('training', 'error', 'Sesion no encontrada.');
            return;
        }

        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $roleplay['agent_id'] !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $messageModel = new TrainingRoleplayMessage();
        $messages = $messageModel->getByRoleplayId($roleplayId, 200);
        $feedbackModel = new TrainingRoleplayFeedback();
        $feedbackItems = $feedbackModel->getByRoleplayId($roleplayId, 200);
        $feedbackAverage = $feedbackModel->getAverageScoreByRoleplayId($roleplayId);
        $coachNoteModel = new TrainingRoleplayCoachNote();
        $coachNotes = $coachNoteModel->getByRoleplayId($roleplayId, 50);
        $rubricItems = [];
        if (!empty($roleplay['rubric_id'])) {
            $rubricItemModel = new TrainingRubricItem();
            $rubricItems = $rubricItemModel->getByRubricId($roleplay['rubric_id']);
        }
        $roleplayModel = new TrainingRoleplay();
        $recentAgentRoleplays = $roleplayModel->getRecentByAgent($roleplay['agent_id'], 5);
        $evaluationModel = new Evaluation();
        $recentEvaluations = $evaluationModel->getRecentByAgent($roleplay['agent_id'], 5);

        require __DIR__ . '/../Views/training/roleplay.php';
    }

    public function sendRoleplayMessage()
    {
        Auth::requirePermission('training.view');

        $roleplayId = $_POST['session_id'] ?? null;
        $message = trim($_POST['message'] ?? '');
        if (strlen($message) > 1200) {
            $message = substr($message, 0, 1200);
        }

        if (!$roleplayId || $message === '') {
            $this->jsonResponse(['success' => false, 'error' => 'Mensaje invalido.'], 400);
            return;
        }

        $roleplayModel = new TrainingRoleplay();
        $roleplay = $roleplayModel->findById($roleplayId);
        if (!$roleplay) {
            $this->jsonResponse(['success' => false, 'error' => 'Sesion no encontrada.'], 404);
            return;
        }

        $role = Auth::user()['role'] ?? '';
        if ($role === 'agent' && (int) $roleplay['agent_id'] !== (int) Auth::user()['id']) {
            $this->jsonResponse(['success' => false, 'error' => 'Acceso denegado.'], 403);
            return;
        }

        $messageModel = new TrainingRoleplayMessage();
        $messageModel->create([
            'roleplay_id' => $roleplayId,
            'sender' => $role === 'qa' || $role === 'admin' ? 'qa' : 'agent',
            'message_text' => $message
        ]);
        $agentMessageId = $messageModel->getLastInsertId();

        $history = $this->buildRoleplayHistory($messageModel->getByRoleplayId($roleplayId, 20));

        $service = new GeminiService();
        $feedbackPayload = null;
        try {
            $reply = $service->generateRoleplayReply([
                'scenario' => $roleplay['scenario_text'] ?? '',
                'tone' => $roleplay['tone_text'] ?? '',
                'obstacles' => $roleplay['obstacles_text'] ?? '',
                'persona' => $roleplay['persona_json'] ?? '',
                'script' => $roleplay['script_text'] ?? '',
                'history' => $history,
                'agent_message' => $message
            ]);

            $objectives = $this->resolveRoleplayObjectives($roleplay);
            $feedbackPayload = $service->generateRoleplayFeedback([
                'objectives' => $objectives,
                'scenario' => $roleplay['scenario_text'] ?? '',
                'history' => $history,
                'agent_message' => $message
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            return;
        }

        $messageModel->create([
            'roleplay_id' => $roleplayId,
            'sender' => 'ai',
            'message_text' => $reply
        ]);

        if ($feedbackPayload && $agentMessageId) {
            $feedbackModel = new TrainingRoleplayFeedback();
            $feedbackModel->create([
                'roleplay_id' => $roleplayId,
                'message_id' => $agentMessageId,
                'score' => $feedbackPayload['score'] ?? null,
                'feedback' => $feedbackPayload['feedback'] ?? null,
                'checklist_json' => isset($feedbackPayload['checklist']) ? json_encode($feedbackPayload['checklist'], JSON_UNESCAPED_UNICODE) : null
            ]);
        }

        $this->jsonResponse([
            'success' => true,
            'data' => [
                'reply' => $reply,
                'feedback' => $feedbackPayload
            ]
        ]);
    }

    public function endRoleplay()
    {
        Auth::requirePermission('training.view');

        $roleplayId = $_POST['session_id'] ?? null;
        if (!$roleplayId) {
            $this->redirectWithMessage('training', 'error', 'Sesion no encontrada.');
            return;
        }

        $roleplayModel = new TrainingRoleplay();
        $roleplay = $roleplayModel->findById($roleplayId);
        if (!$roleplay) {
            $this->redirectWithMessage('training', 'error', 'Sesion no encontrada.');
            return;
        }

        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $roleplay['agent_id'] !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $messageModel = new TrainingRoleplayMessage();
        $messages = $messageModel->getByRoleplayId($roleplayId, 200);
        $transcript = $this->buildRoleplayHistory($messages);

        $service = new GeminiService();
        $summaryPayload = null;
        try {
            $summaryPayload = $service->generateRoleplaySummary([
                'objectives' => $this->resolveRoleplayObjectives($roleplay),
                'scenario' => $roleplay['scenario_text'] ?? '',
                'transcript' => $transcript
            ]);
        } catch (\Throwable $e) {
            $summaryPayload = null;
        }

        $score = $summaryPayload['score'] ?? null;
        $summary = $summaryPayload['summary'] ?? null;
        $actionsJson = null;
        if (!empty($summaryPayload['actions']) && is_array($summaryPayload['actions'])) {
            $actionsJson = json_encode($summaryPayload['actions'], JSON_UNESCAPED_UNICODE);
        }
        $roleplayModel->updateStatus($roleplayId, [
            'status' => 'completed',
            'score' => $score,
            'ai_summary' => $summary,
            'ai_actions_json' => $actionsJson,
            'ended_at' => date('Y-m-d H:i:s')
        ]);

        $notification = new TrainingNotification();
        $notification->create([
            'type' => 'roleplay_completed',
            'agent_id' => $roleplay['agent_id'],
            'qa_id' => $roleplay['qa_id'],
            'payload_json' => json_encode([
                'roleplay_id' => $roleplayId,
                'score' => $score
            ], JSON_UNESCAPED_UNICODE)
        ]);

        header('Location: ' . Config::BASE_URL . 'training/roleplay?session_id=' . $roleplayId);
    }

    public function addCoachNote()
    {
        Auth::requirePermission('training.manage');

        $roleplayId = $_POST['session_id'] ?? null;
        $note = trim($_POST['note_text'] ?? '');
        if (!$roleplayId || $note === '') {
            $this->redirectWithMessage('training', 'error', 'Nota invalida.');
            return;
        }

        $coachNoteModel = new TrainingRoleplayCoachNote();
        $coachNoteModel->create([
            'roleplay_id' => $roleplayId,
            'qa_id' => Auth::user()['id'],
            'note_text' => $note
        ]);

        header('Location: ' . Config::BASE_URL . 'training/roleplay?session_id=' . $roleplayId);
    }

    public function updateFeedback()
    {
        Auth::requirePermission('training.manage');

        $feedbackId = $_POST['feedback_id'] ?? null;
        $roleplayId = $_POST['session_id'] ?? null;
        if (!$feedbackId || !$roleplayId) {
            $this->redirectWithMessage('training', 'error', 'Feedback invalido.');
            return;
        }

        $qaScore = $_POST['qa_score'] ?? null;
        $qaFeedback = trim($_POST['qa_feedback'] ?? '');
        $qaChecklist = $_POST['qa_checklist'] ?? [];
        if (!is_array($qaChecklist)) {
            $qaChecklist = [];
        }

        $feedbackModel = new TrainingRoleplayFeedback();
        $feedbackModel->updateQaReview($feedbackId, [
            'qa_score' => $qaScore !== '' ? (float) $qaScore : null,
            'qa_feedback' => $qaFeedback !== '' ? $qaFeedback : null,
            'qa_checklist_json' => !empty($qaChecklist) ? json_encode($qaChecklist, JSON_UNESCAPED_UNICODE) : null,
            'approved_by' => Auth::user()['id'],
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        header('Location: ' . Config::BASE_URL . 'training/roleplay?session_id=' . $roleplayId);
    }

    public function savePlan()
    {
        Auth::requirePermission('training.manage');

        $roleplayId = $_POST['session_id'] ?? null;
        $planText = trim($_POST['qa_plan_text'] ?? '');
        if (!$roleplayId) {
            $this->redirectWithMessage('training', 'error', 'Sesion no encontrada.');
            return;
        }

        $roleplayModel = new TrainingRoleplay();
        $roleplayModel->updateStatus($roleplayId, [
            'qa_plan_text' => $planText !== '' ? $planText : null
        ]);

        header('Location: ' . Config::BASE_URL . 'training/roleplay?session_id=' . $roleplayId);
    }

    public function createRubric()
    {
        Auth::requirePermission('training.manage');

        $title = trim($_POST['title'] ?? '');
        $campaignId = $_POST['campaign_id'] ?? null;
        $itemsRaw = trim($_POST['items'] ?? '');
        if ($title === '' || $itemsRaw === '') {
            $this->redirectWithMessage('training', 'error', 'Completa titulo e items.');
            return;
        }

        $rubricModel = new TrainingRubric();
        $rubricModel->create([
            'title' => $title,
            'campaign_id' => $campaignId ?: null,
            'created_by' => Auth::user()['id']
        ]);
        $rubricId = $rubricModel->getLastInsertId();

        $itemModel = new TrainingRubricItem();
        $lines = preg_split('/\r\n|\r|\n/', $itemsRaw);
        foreach ($lines as $line) {
            $label = trim($line);
            if ($label === '') {
                continue;
            }
            $itemModel->create([
                'rubric_id' => $rubricId,
                'label' => $label,
                'weight' => 1.0
            ]);
        }

        $this->redirectWithMessage('training', 'success', 'Rubrica creada.');
    }

    public function generateExam()
    {
        Auth::requirePermission('training.manage');

        $agentId = $_POST['agent_id'] ?? null;
        $campaignId = $_POST['campaign_id'] ?? null;
        $numQuestions = (int) ($_POST['num_questions'] ?? 8);
        $difficulty = trim($_POST['difficulty'] ?? 'media');

        if ($numQuestions < 4) {
            $numQuestions = 4;
        } elseif ($numQuestions > 20) {
            $numQuestions = 20;
        }
        $allowedDifficulty = ['baja', 'media', 'alta'];
        if (!in_array($difficulty, $allowedDifficulty, true)) {
            $difficulty = 'media';
        }

        if (!$agentId) {
            $this->redirectWithMessage('training', 'error', 'Selecciona un agente.');
            return;
        }

        $userModel = new User();
        $agent = $userModel->findById($agentId);
        if (!$agent) {
            $this->redirectWithMessage('training', 'error', 'Agente no encontrado.');
            return;
        }

        $campaign = null;
        if ($campaignId) {
            $campaignModel = new Campaign();
            $campaign = $campaignModel->findById($campaignId);
        }

        $answerModel = new EvaluationAnswer();
        $weakAreas = $answerModel->getWeakAreasByAgent($agentId, 5);
        $weakAreaText = '';
        if (!empty($weakAreas)) {
            $parts = [];
            foreach ($weakAreas as $area) {
                $parts[] = $area['field_label'] . ' (' . number_format((float) $area['avg_score'], 1) . ')';
            }
            $weakAreaText = implode(', ', $parts);
        }

        $service = new GeminiService();
        try {
            $examData = $service->generateTrainingExam([
                'agent' => $agent['full_name'] ?? $agent['username'],
                'campaign' => $campaign['name'] ?? '',
                'weak_areas' => $weakAreaText,
                'num_questions' => $numQuestions,
                'difficulty' => $difficulty
            ]);
        } catch (\Throwable $e) {
            $this->redirectWithMessage('training', 'error', 'Error IA: ' . $e->getMessage());
            return;
        }

        $title = $examData['title'] ?? ('Examen IA ' . date('Y-m-d H:i'));
        $questions = $examData['questions'] ?? [];
        if (empty($questions)) {
            $this->redirectWithMessage('training', 'error', 'La IA no genero preguntas.');
            return;
        }

        $examModel = new TrainingExam();
        $examModel->create([
            'agent_id' => $agentId,
            'qa_id' => Auth::user()['id'],
            'campaign_id' => $campaignId ?: null,
            'title' => $title,
            'status' => 'assigned',
            'prompt_context' => $weakAreaText,
            'public_token' => $this->generateToken(),
            'public_enabled' => 1
        ]);
        $examId = $examModel->getLastInsertId();

        $questionModel = new TrainingExamQuestion();
        foreach ($questions as $question) {
            if (empty($question['question'])) {
                continue;
            }
            $options = $question['options'] ?? null;
            $questionModel->create([
                'exam_id' => $examId,
                'question_text' => $question['question'],
                'question_type' => $question['type'] ?? 'open',
                'options_json' => is_array($options) ? json_encode($options, JSON_UNESCAPED_UNICODE) : null,
                'correct_answer' => $question['correct_answer'] ?? null,
                'weight' => $question['weight'] ?? 1.0
            ]);
        }

        header('Location: ' . Config::BASE_URL . 'training/exams/view?exam_id=' . $examId);
    }

    public function enablePublicExam()
    {
        Auth::requirePermission('training.manage');

        $examId = $_POST['exam_id'] ?? null;
        if (!$examId) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findById($examId);
        if (!$exam) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $token = $exam['public_token'] ?: $this->generateToken();
        $examModel->updateStatus($examId, [
            'public_token' => $token,
            'public_enabled' => 1
        ]);

        header('Location: ' . Config::BASE_URL . 'training/exams/view?exam_id=' . $examId);
    }

    public function disablePublicExam()
    {
        Auth::requirePermission('training.manage');

        $examId = $_POST['exam_id'] ?? null;
        if (!$examId) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findById($examId);
        if (!$exam) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $examModel->updateStatus($examId, [
            'public_enabled' => 0
        ]);

        header('Location: ' . Config::BASE_URL . 'training/exams/view?exam_id=' . $examId);
    }

    public function viewExam()
    {
        Auth::requirePermission('training.view');

        $examId = $_GET['exam_id'] ?? null;
        if (!$examId) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findById($examId);
        if (!$exam) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $exam['agent_id'] !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $questionModel = new TrainingExamQuestion();
        $questions = $questionModel->getByExamId($examId);

        require __DIR__ . '/../Views/training/exam_view.php';
    }

    public function takeExam()
    {
        Auth::requirePermission('training.view');

        $examId = $_GET['exam_id'] ?? null;
        if (!$examId) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findById($examId);
        if (!$exam) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $exam['agent_id'] !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        if ($exam['status'] === 'assigned') {
            $examModel->updateStatus($examId, ['status' => 'in_progress']);
            $exam['status'] = 'in_progress';
        }

        $questionModel = new TrainingExamQuestion();
        $questions = $questionModel->getByExamId($examId);

        require __DIR__ . '/../Views/training/exam_take.php';
    }

    public function publicExam()
    {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            http_response_code(404);
            echo 'Examen no encontrado.';
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findByPublicToken($token);
        if (!$exam || !(int) $exam['public_enabled']) {
            http_response_code(404);
            echo 'Examen no disponible.';
            return;
        }

        if ($exam['status'] === 'assigned') {
            $examModel->updateStatus($exam['id'], ['status' => 'in_progress']);
            $exam['status'] = 'in_progress';
        }

        $questionModel = new TrainingExamQuestion();
        $questions = $questionModel->getByExamId($exam['id']);

        require __DIR__ . '/../Views/training/exam_public.php';
    }

    public function submitPublicExam()
    {
        $token = $_POST['token'] ?? null;
        if (!$token) {
            http_response_code(404);
            echo 'Examen no encontrado.';
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findByPublicToken($token);
        if (!$exam || !(int) $exam['public_enabled']) {
            http_response_code(404);
            echo 'Examen no disponible.';
            return;
        }

        $questionModel = new TrainingExamQuestion();
        $questions = $questionModel->getByExamId($exam['id']);

        $answerModel = new TrainingExamAnswer();
        $totalScore = 0.0;
        $maxScore = 0.0;
        $openQuestions = [];

        foreach ($questions as $index => $question) {
            $answerKey = 'answer_' . $question['id'];
            $answerText = trim($_POST[$answerKey] ?? '');
            $weight = (float) ($question['weight'] ?? 1.0);
            $maxScore += $weight;

            if ($question['question_type'] === 'mcq') {
                $correct = trim((string) ($question['correct_answer'] ?? ''));
                $score = 0.0;
                if ($correct !== '' && strcasecmp($correct, $answerText) === 0) {
                    $score = $weight;
                }
                $totalScore += $score;
                $answerModel->create([
                    'question_id' => $question['id'],
                    'answer_text' => $answerText,
                    'score' => $score,
                    'feedback' => $score > 0 ? 'Correcto.' : 'Respuesta incorrecta.'
                ]);
            } else {
                $openQuestions[] = [
                    'index' => count($openQuestions) + 1,
                    'question_id' => $question['id'],
                    'question_text' => $question['question_text'],
                    'weight' => $weight,
                    'answer_text' => $answerText
                ];
            }
        }

        $summary = null;
        if (!empty($openQuestions)) {
            $questionsPayload = [];
            $answersPayload = [];
            foreach ($openQuestions as $item) {
                $questionsPayload[] = [
                    'index' => $item['index'],
                    'question' => $item['question_text'],
                    'weight' => $item['weight']
                ];
                $answersPayload[] = [
                    'index' => $item['index'],
                    'answer' => $item['answer_text']
                ];
            }

            $service = new GeminiService();
            try {
                $grading = $service->gradeTrainingExam([
                    'questions' => json_encode($questionsPayload, JSON_UNESCAPED_UNICODE),
                    'answers' => json_encode($answersPayload, JSON_UNESCAPED_UNICODE)
                ]);
                $summary = $grading['summary'] ?? null;
                $answers = $grading['answers'] ?? [];
            } catch (\Throwable $e) {
                $answers = [];
            }

            foreach ($openQuestions as $item) {
                $score = 0.0;
                $feedback = null;
                foreach ($answers as $graded) {
                    if ((int) ($graded['question_index'] ?? 0) === $item['index']) {
                        $score = (float) ($graded['score'] ?? 0.0);
                        $feedback = $graded['feedback'] ?? null;
                        break;
                    }
                }
                if ($score > $item['weight']) {
                    $score = $item['weight'];
                }
                $totalScore += $score;
                $answerModel->create([
                    'question_id' => $item['question_id'],
                    'answer_text' => $item['answer_text'],
                    'score' => $score,
                    'feedback' => $feedback
                ]);
            }
        }

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $examModel->updateStatus($exam['id'], [
            'status' => 'completed',
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'ai_summary' => $summary,
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        require __DIR__ . '/../Views/training/exam_public_thanks.php';
    }

    public function submitExam()
    {
        Auth::requireAnyRole(['admin', 'qa', 'agent']);

        $examId = $_POST['exam_id'] ?? null;
        if (!$examId) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        $examModel = new TrainingExam();
        $exam = $examModel->findById($examId);
        if (!$exam) {
            $this->redirectWithMessage('training', 'error', 'Examen no encontrado.');
            return;
        }

        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $exam['agent_id'] !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $questionModel = new TrainingExamQuestion();
        $questions = $questionModel->getByExamId($examId);

        $answerModel = new TrainingExamAnswer();
        $totalScore = 0.0;
        $maxScore = 0.0;
        $openQuestions = [];

        foreach ($questions as $index => $question) {
            $answerKey = 'answer_' . $question['id'];
            $answerText = trim($_POST[$answerKey] ?? '');
            $weight = (float) ($question['weight'] ?? 1.0);
            $maxScore += $weight;

            if ($question['question_type'] === 'mcq') {
                $correct = trim((string) ($question['correct_answer'] ?? ''));
                $score = 0.0;
                if ($correct !== '' && strcasecmp($correct, $answerText) === 0) {
                    $score = $weight;
                }
                $totalScore += $score;
                $answerModel->create([
                    'question_id' => $question['id'],
                    'answer_text' => $answerText,
                    'score' => $score,
                    'feedback' => $score > 0 ? 'Correcto.' : 'Respuesta incorrecta.'
                ]);
            } else {
                $openQuestions[] = [
                    'index' => count($openQuestions) + 1,
                    'question_id' => $question['id'],
                    'question_text' => $question['question_text'],
                    'weight' => $weight,
                    'answer_text' => $answerText
                ];
            }
        }

        $summary = null;
        if (!empty($openQuestions)) {
            $questionsPayload = [];
            $answersPayload = [];
            foreach ($openQuestions as $item) {
                $questionsPayload[] = [
                    'index' => $item['index'],
                    'question' => $item['question_text'],
                    'weight' => $item['weight']
                ];
                $answersPayload[] = [
                    'index' => $item['index'],
                    'answer' => $item['answer_text']
                ];
            }

            $service = new GeminiService();
            try {
                $grading = $service->gradeTrainingExam([
                    'questions' => json_encode($questionsPayload, JSON_UNESCAPED_UNICODE),
                    'answers' => json_encode($answersPayload, JSON_UNESCAPED_UNICODE)
                ]);
                $summary = $grading['summary'] ?? null;
                $answers = $grading['answers'] ?? [];
            } catch (\Throwable $e) {
                $answers = [];
            }

            foreach ($openQuestions as $item) {
                $score = 0.0;
                $feedback = null;
                foreach ($answers as $graded) {
                    if ((int) ($graded['question_index'] ?? 0) === $item['index']) {
                        $score = (float) ($graded['score'] ?? 0.0);
                        $feedback = $graded['feedback'] ?? null;
                        break;
                    }
                }
                if ($score > $item['weight']) {
                    $score = $item['weight'];
                }
                $totalScore += $score;
                $answerModel->create([
                    'question_id' => $item['question_id'],
                    'answer_text' => $item['answer_text'],
                    'score' => $score,
                    'feedback' => $feedback
                ]);
            }
        }

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
        $examModel->updateStatus($examId, [
            'status' => 'completed',
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'ai_summary' => $summary,
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        header('Location: ' . Config::BASE_URL . 'training/exams/view?exam_id=' . $examId);
    }

    public function retrainingIndex()
    {
        Auth::requirePermission('training.view');

        $role = Auth::user()['role'] ?? '';
        $userId = (int) (Auth::user()['id'] ?? 0);

        $retrainingModel = new QaRetraining();
        $moduleModel = new QaRetrainingModule();
        $progressModel = new QaRetrainingProgress();

        if ($role === 'agent') {
            $retrainings = $retrainingModel->getByAgentId($userId, 30);
            foreach ($retrainings as &$retraining) {
                $modules = $moduleModel->getByRetrainingId((int) $retraining['id']);
                $progress = $progressModel->getByRetrainingAndAgent((int) $retraining['id'], $userId);
                $retraining['modules'] = $modules;
                $retraining['progress_map'] = $progress;
            }
            unset($retraining);

            require __DIR__ . '/../Views/training/retraining.php';
            return;
        }

        $campaignModel = new Campaign();
        $userModel = new User();
        $evaluationModel = new Evaluation();

        $retrainings = $retrainingModel->getRecent(60);
        foreach ($retrainings as &$retraining) {
            $modules = $moduleModel->getByRetrainingId((int) $retraining['id']);
            $progress = $progressModel->getByRetrainingAndAgent((int) $retraining['id'], (int) $retraining['agent_id']);
            $retraining['modules'] = $modules;
            $retraining['progress_map'] = $progress;
        }
        unset($retraining);

        $agents = $userModel->getByRole('agent');
        $supervisors = $userModel->getByRole('qa');
        $campaigns = $campaignModel->getActive();
        $pendingReminders = $retrainingModel->getPendingReminders(date('Y-m-d'));
        $recentEvaluations = $evaluationModel->getAll(20);

        require __DIR__ . '/../Views/training/retraining.php';
    }

    public function createRetraining()
    {
        Auth::requirePermission('training.manage');

        if (!$this->isSupervisorOrAdmin()) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $campaignId = (int) ($_POST['campaign_id'] ?? 0);
        $agentId = (int) ($_POST['agent_id'] ?? 0);
        $evaluationId = !empty($_POST['evaluation_id']) ? (int) $_POST['evaluation_id'] : null;
        $supervisorId = !empty($_POST['supervisor_id']) ? (int) $_POST['supervisor_id'] : null;
        $dueDate = trim($_POST['due_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $errorsRaw = trim($_POST['detected_errors'] ?? '');

        if ($campaignId <= 0 || $agentId <= 0 || $errorsRaw === '') {
            $this->redirectWithMessage('training/retraining', 'error', 'Campana, agente y errores detectados son obligatorios.');
            return;
        }

        $retrainingModel = new QaRetraining();
        $existing = $retrainingModel->findActiveByCampaignAndAgent($campaignId, $agentId);
        if ($existing) {
            $this->redirectWithMessage('training/retraining', 'error', 'Ya existe un reentrenamiento activo para esa campana y agente.');
            return;
        }

        $errors = preg_split('/\r\n|\r|\n/', $errorsRaw);
        $errors = array_values(array_filter(array_map('trim', $errors), static function ($line) {
            return $line !== '';
        }));

        if (empty($errors)) {
            $this->redirectWithMessage('training/retraining', 'error', 'Debes indicar al menos un error detectado.');
            return;
        }

        $retrainingModel->create([
            'campaign_id' => $campaignId,
            'agent_id' => $agentId,
            'evaluation_id' => $evaluationId,
            'created_by' => (int) Auth::user()['id'],
            'supervisor_id' => $supervisorId ?: (int) Auth::user()['id'],
            'status' => 'assigned',
            'due_date' => $dueDate !== '' ? $dueDate : null,
            'notes' => $notes !== '' ? $notes : null
        ]);

        $retrainingId = $retrainingModel->getLastInsertId();
        $moduleModel = new QaRetrainingModule();
        $order = 1;
        foreach ($errors as $error) {
            $moduleModel->create([
                'retraining_id' => $retrainingId,
                'title' => 'Modulo ' . $order . ': ' . $error,
                'lesson_text' => "Error detectado: {$error}\n\nObjetivo: corregir esta desviacion en la campana.",
                'detected_error' => $error,
                'sequence_order' => $order,
                'pass_score' => 80,
                'quiz_question' => 'Explica como corregirias este error en una llamada real.',
                'quiz_type' => 'text',
                'correct_answer' => $error
            ]);
            $order++;
        }

        $this->createTrainingNotification('retraining_assigned', $agentId, $supervisorId ?: (int) Auth::user()['id'], [
            'retraining_id' => $retrainingId
        ]);

        $this->redirectWithMessage('training/retraining', 'success', 'Reentrenamiento creado con modulos por error detectado.');
    }

    public function startRetraining()
    {
        Auth::requirePermission('training.view');

        $retrainingId = (int) ($_POST['retraining_id'] ?? 0);
        if ($retrainingId <= 0) {
            $this->redirectWithMessage('training/retraining', 'error', 'Reentrenamiento no encontrado.');
            return;
        }

        $retrainingModel = new QaRetraining();
        $retraining = $retrainingModel->findById($retrainingId);
        if (!$retraining) {
            $this->redirectWithMessage('training/retraining', 'error', 'Reentrenamiento no encontrado.');
            return;
        }

        if ((Auth::user()['role'] ?? '') === 'agent' && (int) $retraining['agent_id'] !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        if ($retraining['status'] === 'assigned') {
            $retrainingModel->update($retrainingId, ['status' => 'in_progress']);
        }

        header('Location: ' . Config::BASE_URL . 'training/retraining');
    }

    public function submitRetrainingModule()
    {
        Auth::requirePermission('training.view');

        $retrainingId = (int) ($_POST['retraining_id'] ?? 0);
        $moduleId = (int) ($_POST['module_id'] ?? 0);
        $answerText = trim($_POST['answer_text'] ?? '');

        if ($retrainingId <= 0 || $moduleId <= 0) {
            $this->redirectWithMessage('training/retraining', 'error', 'Modulo no valido.');
            return;
        }

        $retrainingModel = new QaRetraining();
        $moduleModel = new QaRetrainingModule();
        $progressModel = new QaRetrainingProgress();

        $retraining = $retrainingModel->findById($retrainingId);
        $module = $moduleModel->findById($moduleId);

        if (!$retraining || !$module || (int) $module['retraining_id'] !== $retrainingId) {
            $this->redirectWithMessage('training/retraining', 'error', 'Reentrenamiento o modulo no encontrado.');
            return;
        }

        $role = Auth::user()['role'] ?? '';
        $agentId = (int) $retraining['agent_id'];
        if ($role === 'agent' && $agentId !== (int) Auth::user()['id']) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        if ($retraining['status'] === 'failed' || $retraining['status'] === 'active_in_production') {
            $this->redirectWithMessage('training/retraining', 'error', 'Este reentrenamiento ya no admite respuestas.');
            return;
        }

        $allModules = $moduleModel->getByRetrainingId($retrainingId);
        $progressMap = $progressModel->getByRetrainingAndAgent($retrainingId, $agentId);

        foreach ($allModules as $item) {
            if ((int) $item['sequence_order'] >= (int) $module['sequence_order']) {
                break;
            }
            if ((int) $item['is_required'] === 1) {
                $prevProgress = $progressMap[(int) $item['id']] ?? null;
                if (!$prevProgress || $prevProgress['status'] !== 'completed') {
                    $this->redirectWithMessage('training/retraining', 'error', 'Debes completar las lecciones previas antes de avanzar.');
                    return;
                }
            }
        }

        $existingProgress = $progressMap[$moduleId] ?? null;
        $attempts = (int) ($existingProgress['attempts'] ?? 0) + 1;
        $score = $this->gradeRetrainingAnswer($module, $answerText);
        $passed = $score >= (float) ($module['pass_score'] ?? 80);

        $progressModel->upsert([
            'module_id' => $moduleId,
            'retraining_id' => $retrainingId,
            'agent_id' => $agentId,
            'status' => $passed ? 'completed' : 'failed',
            'score' => $score,
            'answer_text' => $answerText !== '' ? $answerText : null,
            'attempts' => $attempts,
            'completed_at' => $passed ? date('Y-m-d H:i:s') : null
        ]);

        if (!$passed) {
            $this->handleRetrainingFailure($retraining, $module);
            $this->redirectWithMessage('training/retraining', 'error', 'Modulo reprobado. Se asigno refuerzo obligatorio.');
            return;
        }

        $requiredCount = $moduleModel->getRequiredCountByRetrainingId($retrainingId);
        $completedCount = $progressModel->getCompletedRequiredCount($retrainingId, $agentId);
        $progressPercent = $requiredCount > 0 ? round(($completedCount / $requiredCount) * 100, 2) : 0.0;

        $retrainingModel->update($retrainingId, [
            'status' => 'in_progress',
            'progress_percent' => $progressPercent
        ]);

        if ($requiredCount > 0 && $completedCount >= $requiredCount) {
            $retrainingModel->update($retrainingId, [
                'status' => 'approved',
                'progress_percent' => 100
            ]);

            $this->createTrainingNotification('retraining_completed_for_approval', (int) $retraining['agent_id'], (int) $retraining['supervisor_id'], [
                'retraining_id' => $retrainingId
            ]);
        }

        $this->redirectWithMessage('training/retraining', 'success', 'Modulo completado.');
    }

    public function approveRetraining()
    {
        Auth::requirePermission('training.manage');

        if (!$this->isSupervisorOrAdmin()) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $retrainingId = (int) ($_POST['retraining_id'] ?? 0);
        if ($retrainingId <= 0) {
            $this->redirectWithMessage('training/retraining', 'error', 'Reentrenamiento no encontrado.');
            return;
        }

        $retrainingModel = new QaRetraining();
        $retraining = $retrainingModel->findById($retrainingId);
        if (!$retraining) {
            $this->redirectWithMessage('training/retraining', 'error', 'Reentrenamiento no encontrado.');
            return;
        }

        if (!in_array($retraining['status'], ['approved', 'in_progress'], true)) {
            $this->redirectWithMessage('training/retraining', 'error', 'El reentrenamiento no esta en estado aprobable.');
            return;
        }

        $retrainingModel->update($retrainingId, [
            'status' => 'active_in_production',
            'approved_by' => (int) Auth::user()['id'],
            'approved_at' => date('Y-m-d H:i:s'),
            'activation_at' => date('Y-m-d H:i:s'),
            'progress_percent' => 100
        ]);

        $this->createTrainingNotification('retraining_activated_in_production', (int) $retraining['agent_id'], (int) Auth::user()['id'], [
            'retraining_id' => $retrainingId
        ]);

        $this->redirectWithMessage('training/retraining', 'success', 'Reentrenamiento aprobado y activado a produccion.');
    }

    public function sendRetrainingReminders()
    {
        Auth::requirePermission('training.manage');

        if (!$this->isSupervisorOrAdmin()) {
            http_response_code(403);
            echo 'Access denied';
            return;
        }

        $retrainingModel = new QaRetraining();
        $pending = $retrainingModel->getPendingReminders(date('Y-m-d'));

        if (empty($pending)) {
            $this->redirectWithMessage('training/retraining', 'success', 'No hay reentrenamientos pendientes para recordar.');
            return;
        }

        $userModel = new User();
        $emailService = new EmailService();
        $sentCount = 0;

        foreach ($pending as $retraining) {
            $agent = $userModel->findById((int) $retraining['agent_id']);
            $supervisor = !empty($retraining['supervisor_id']) ? $userModel->findById((int) $retraining['supervisor_id']) : null;

            $subject = 'Recordatorio: Reentrenamiento QA pendiente';
            $html = '<p>Tienes un reentrenamiento pendiente de completar.</p>'
                . '<p><strong>Campana:</strong> ' . htmlspecialchars((string) ($retraining['campaign_name'] ?? 'N/A')) . '</p>'
                . '<p><strong>Estado:</strong> ' . htmlspecialchars((string) $retraining['status']) . '</p>';

            if ($agent && filter_var($agent['username'], FILTER_VALIDATE_EMAIL)) {
                if ($emailService->send($agent['username'], $subject, $html)) {
                    $sentCount++;
                }
            }
            if ($supervisor && filter_var($supervisor['username'], FILTER_VALIDATE_EMAIL)) {
                $emailService->send($supervisor['username'], 'Alerta supervisor: agente con reentrenamiento pendiente', $html);
            }

            $this->createTrainingNotification('retraining_reminder', (int) $retraining['agent_id'], (int) ($retraining['supervisor_id'] ?? 0), [
                'retraining_id' => (int) $retraining['id']
            ]);
            $retrainingModel->incrementReminderCount((int) $retraining['id']);
        }

        $this->redirectWithMessage('training/retraining', 'success', 'Recordatorios procesados: ' . $sentCount);
    }

    private function handleRetrainingFailure(array $retraining, array $failedModule): void
    {
        $retrainingModel = new QaRetraining();
        $moduleModel = new QaRetrainingModule();

        $currentFailCount = (int) ($retraining['fail_count'] ?? 0);
        $retrainingModel->update((int) $retraining['id'], [
            'status' => 'failed',
            'reinforcement_required' => 1,
            'fail_count' => $currentFailCount + 1
        ]);

        $retrainingModel->create([
            'campaign_id' => (int) $retraining['campaign_id'],
            'agent_id' => (int) $retraining['agent_id'],
            'evaluation_id' => !empty($retraining['evaluation_id']) ? (int) $retraining['evaluation_id'] : null,
            'created_by' => (int) Auth::user()['id'],
            'supervisor_id' => !empty($retraining['supervisor_id']) ? (int) $retraining['supervisor_id'] : null,
            'status' => 'assigned',
            'due_date' => !empty($retraining['due_date']) ? $retraining['due_date'] : null,
            'reinforcement_required' => 1,
            'notes' => 'Refuerzo obligatorio por reprobacion del modulo: ' . ($failedModule['title'] ?? 'N/A')
        ]);
        $newRetrainingId = $retrainingModel->getLastInsertId();

        $moduleModel->create([
            'retraining_id' => $newRetrainingId,
            'title' => 'Refuerzo obligatorio: ' . ($failedModule['title'] ?? 'Modulo'),
            'lesson_text' => "Se detecto reprobacion previa.\n\nRepasa el error y describe como evitarlo en produccion.",
            'detected_error' => $failedModule['detected_error'] ?? $failedModule['title'],
            'sequence_order' => 1,
            'pass_score' => (float) ($failedModule['pass_score'] ?? 80),
            'quiz_question' => $failedModule['quiz_question'] ?? 'Describe la correccion esperada.',
            'quiz_type' => $failedModule['quiz_type'] ?? 'text',
            'options_json' => $failedModule['options_json'] ?? null,
            'correct_answer' => $failedModule['correct_answer'] ?? null,
            'is_required' => 1
        ]);

        $this->createTrainingNotification('retraining_failed', (int) $retraining['agent_id'], (int) ($retraining['supervisor_id'] ?? 0), [
            'retraining_id' => (int) $retraining['id'],
            'new_retraining_id' => $newRetrainingId,
            'failed_module_id' => (int) ($failedModule['id'] ?? 0)
        ]);
    }

    private function gradeRetrainingAnswer(array $module, string $answerText): float
    {
        $quizType = $module['quiz_type'] ?? 'text';
        $correctAnswer = trim((string) ($module['correct_answer'] ?? ''));

        if ($quizType === 'mcq') {
            if ($correctAnswer === '') {
                return 100.0;
            }
            return strcasecmp($correctAnswer, trim($answerText)) === 0 ? 100.0 : 0.0;
        }

        if ($correctAnswer === '') {
            return $answerText !== '' ? 100.0 : 0.0;
        }

        $normalizedAnswer = strtolower(trim($answerText));
        $normalizedExpected = strtolower($correctAnswer);

        if ($normalizedAnswer === '') {
            return 0.0;
        }
        if (strpos($normalizedAnswer, $normalizedExpected) !== false) {
            return 100.0;
        }

        return 50.0;
    }

    private function createTrainingNotification(string $type, int $agentId, int $qaId, array $payload = []): void
    {
        $notification = new TrainingNotification();
        $notification->create([
            'type' => $type,
            'agent_id' => $agentId > 0 ? $agentId : null,
            'qa_id' => $qaId > 0 ? $qaId : null,
            'payload_json' => !empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null
        ]);
    }

    private function isSupervisorOrAdmin(): bool
    {
        $role = Auth::user()['role'] ?? '';
        return in_array($role, ['qa', 'admin'], true);
    }

    private function buildRoleplayHistory(array $messages): string
    {
        $lines = [];
        foreach ($messages as $message) {
            $prefix = $message['sender'] === 'ai' ? 'Cliente' : ($message['sender'] === 'qa' ? 'QA' : 'Agente');
            $lines[] = $prefix . ': ' . $message['message_text'];
        }
        return implode("\n", $lines);
    }

    private function redirectWithMessage(string $path, string $type, string $message): void
    {
        $query = http_build_query([$type => $message]);
        header('Location: ' . Config::BASE_URL . $path . '?' . $query);
    }

    private function generateToken(): string
    {
        try {
            return bin2hex(random_bytes(20));
        } catch (\Exception $e) {
            return sha1(uniqid((string) mt_rand(), true));
        }
    }

    private function resolveRoleplayObjectives(array $roleplay): string
    {
        $objectives = trim((string) ($roleplay['objectives_text'] ?? ''));
        if ($objectives !== '') {
            return $objectives;
        }

        if (!empty($roleplay['rubric_id'])) {
            $itemModel = new TrainingRubricItem();
            $items = $itemModel->getByRubricId($roleplay['rubric_id']);
            if (!empty($items)) {
                $labels = array_map(static function ($item) {
                    return $item['label'];
                }, $items);
                return implode(', ', $labels);
            }
        }

        return 'Saludo profesional, identificacion, verificacion de datos, empatia, resolucion clara, cierre efectivo';
    }

    private function jsonResponse(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
