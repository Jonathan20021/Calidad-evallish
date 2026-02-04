<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Call;
use App\Models\CallAnalytics;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CorporateClient;
use App\Models\AiEvaluationCriteria;
use App\Services\GeminiService;

class CallController
{
    private function formatDuration($seconds)
    {
        if ($seconds === null || $seconds === '') {
            return '00:00';
        }
        $seconds = (int) $seconds;
        $minutes = floor($seconds / 60);
        $remaining = $seconds % 60;
        return str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        Auth::requireAuth();

        $callModel = new Call();
        $calls = $callModel->getAll(100);

        foreach ($calls as &$call) {
            $call['agent'] = $call['agent_name'];
            $call['project'] = $call['project_name'] ?? null;
            $call['campaign'] = $call['campaign_name'];
            $call['date'] = date('d/m/Y H:i', strtotime($call['call_datetime']));
            $call['duration'] = $this->formatDuration($call['duration_seconds']);
            $call['status'] = $call['evaluation_id'] ? 'evaluated' : 'pending';
        }
        unset($call);

        require __DIR__ . '/../Views/calls/index.php';
    }

    public function create()
    {
        Auth::requireAuth();

        $campaignModel = new Campaign();
        $userModel = new User();
        $clientModel = new CorporateClient();

        $campaigns = $campaignModel->getActive();
        $agents = $userModel->getByRole('agent');
        $projects = $clientModel->getAll();
        $errors = [];
        $old = [
            'agent_id' => $_GET['agent_id'] ?? '',
            'project_id' => $_GET['project_id'] ?? '',
            'campaign_id' => $_GET['campaign_id'] ?? '',
            'call_type' => $_GET['call_type'] ?? '',
            'call_datetime' => $_GET['call_datetime'] ?? '',
            'duration_seconds' => $_GET['duration_seconds'] ?? '',
            'customer_phone' => $_GET['customer_phone'] ?? '',
            'lead' => $_GET['lead'] ?? '',
            'notes' => $_GET['notes'] ?? ''
        ];
        if ($old['call_datetime'] === '') {
            $old['call_datetime'] = date('Y-m-d\TH:i');
        }

        require __DIR__ . '/../Views/calls/create.php';
    }

    public function store()
    {
        Auth::requireAuth();

        $campaignModel = new Campaign();
        $userModel = new User();
        $clientModel = new CorporateClient();
        $campaigns = $campaignModel->getActive();
        $agents = $userModel->getByRole('agent');
        $projects = $clientModel->getAll();

        $errors = [];
        $old = [
            'agent_id' => $_POST['agent_id'] ?? '',
            'project_id' => $_POST['project_id'] ?? '',
            'campaign_id' => $_POST['campaign_id'] ?? '',
            'call_type' => $_POST['call_type'] ?? '',
            'call_datetime' => $_POST['call_datetime'] ?? '',
            'duration_seconds' => $_POST['duration_seconds'] ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'lead' => $_POST['lead'] ?? '',
            'notes' => $_POST['notes'] ?? ''
        ];

        if (empty($old['agent_id'])) {
            $errors[] = 'Seleccione un agente.';
        }
        if (empty($old['campaign_id'])) {
            $errors[] = 'Seleccione una campaña.';
        }
        if (empty($old['call_datetime'])) {
            $errors[] = 'Indique la fecha y hora de la llamada.';
        }

        $recordingPath = null;
        if (!isset($_FILES['recording'])) {
            $errors[] = 'La grabación es obligatoria.';
        } elseif ($_FILES['recording']['error'] !== UPLOAD_ERR_OK) {
            $uploadError = $_FILES['recording']['error'];
            $errorMap = [
                UPLOAD_ERR_INI_SIZE => 'La grabación excede el límite del servidor.',
                UPLOAD_ERR_FORM_SIZE => 'La grabación excede el tamaño permitido.',
                UPLOAD_ERR_PARTIAL => 'La grabación se subió parcialmente.',
                UPLOAD_ERR_NO_FILE => 'La grabación es obligatoria.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor.',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el servidor.',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.'
            ];
            $errors[] = $errorMap[$uploadError] ?? 'Error al subir la grabación.';
        } else {
            $file = $_FILES['recording'];
            $maxBytes = 50 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $errors[] = 'La grabación supera el tamaño permitido (50MB).';
            } else {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($extension === '') {
                    $extension = 'audio';
                }
                $uploadDir = __DIR__ . '/../../public/uploads/calls';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                try {
                    $random = bin2hex(random_bytes(6));
                } catch (\Exception $e) {
                    $random = uniqid();
                }
                $filename = 'call_' . date('Ymd_His') . '_' . $random . '.' . $extension;
                $targetPath = $uploadDir . '/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = 'No se pudo guardar la grabación.';
                } else {
                    $recordingPath = 'uploads/calls/' . $filename;
                }
            }
        }

        if (!empty($errors)) {
            require __DIR__ . '/../Views/calls/create.php';
            return;
        }

        $callDateTime = date('Y-m-d H:i:s', strtotime($old['call_datetime']));
        $durationSeconds = $old['duration_seconds'] !== '' ? (int) $old['duration_seconds'] : null;

        $callModel = new Call();
        $callModel->create([
            'agent_id' => $old['agent_id'],
            'project_id' => $old['project_id'] !== '' ? (int) $old['project_id'] : null,
            'campaign_id' => $old['campaign_id'],
            'call_type' => $old['call_type'] !== '' ? $old['call_type'] : null,
            'call_datetime' => $callDateTime,
            'duration_seconds' => $durationSeconds,
            'customer_phone' => $old['customer_phone'],
            'lead' => $old['lead'] !== '' ? $old['lead'] : null,
            'notes' => $old['notes'],
            'recording_path' => $recordingPath
        ]);

        header('Location: ' . \App\Config\Config::BASE_URL . 'calls');
    }

    public function show()
    {
        Auth::requireAuth();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'calls');
            exit;
        }

        $callModel = new Call();
        $call = $callModel->findById($id);

        if (!$call) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'calls');
            exit;
        }

        $call['agent'] = $call['agent_name'];
        $call['project'] = $call['project_name'] ?? null;
        $call['campaign'] = $call['campaign_name'];
        $call['date'] = date('d/m/Y H:i', strtotime($call['call_datetime']));
        $call['duration'] = $this->formatDuration($call['duration_seconds']);
        $call['status'] = $call['evaluation_id'] ? 'evaluated' : 'pending';
        $call['recording_url'] = $call['recording_path'] ? (\App\Config\Config::BASE_URL . $call['recording_path']) : null;

        $forceAnalyze = isset($_GET['analyze']) && $_GET['analyze'] === '1';
        [$aiAnalytics, $aiAnalyticsError] = $this->resolveAiAnalytics($call, $forceAnalyze);

        require __DIR__ . '/../Views/calls/show.php';
    }

    public function destroy()
    {
        Auth::requireAuth();

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'calls');
            exit;
        }

        $callModel = new Call();
        $call = $callModel->findById($id);
        if (!$call) {
            header('Location: ' . \App\Config\Config::BASE_URL . 'calls');
            exit;
        }

        $callModel->deleteById($id);

        if (!empty($call['recording_path'])) {
            $recordingFile = __DIR__ . '/../../public/' . $call['recording_path'];
            if (is_file($recordingFile)) {
                @unlink($recordingFile);
            }
        }

        header('Location: ' . \App\Config\Config::BASE_URL . 'calls');
    }

    public function analyze()
    {
        Auth::requireAuth();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'error' => 'ID requerido.'], 400);
            return;
        }

        $callModel = new Call();
        $call = $callModel->findById($id);
        if (!$call) {
            $this->jsonResponse(['success' => false, 'error' => 'Llamada no encontrada.'], 404);
            return;
        }

        $call['agent'] = $call['agent_name'];
        $call['project'] = $call['project_name'] ?? null;
        $call['campaign'] = $call['campaign_name'];
        $call['date'] = date('d/m/Y H:i', strtotime($call['call_datetime']));
        $call['duration'] = $this->formatDuration($call['duration_seconds']);

        [$aiAnalytics, $aiAnalyticsError] = $this->resolveAiAnalytics($call, true);

        if ($aiAnalyticsError) {
            $this->jsonResponse(['success' => false, 'error' => $aiAnalyticsError], 500);
            return;
        }

        $this->jsonResponse(['success' => true, 'data' => $aiAnalytics]);
    }

    private function resolveAiAnalytics(array $call, bool $forceAnalyze): array
    {
        $aiAnalytics = null;
        $aiAnalyticsError = null;
        if (!empty($call['recording_path'])) {
            $analyticsModel = new CallAnalytics();
            $existing = $analyticsModel->findByCallId($call['id'], \App\Config\Config::$GEMINI_MODEL);
            if ($existing && !$forceAnalyze) {
                if (!empty($existing['metrics_json'])) {
                    $decoded = json_decode($existing['metrics_json'], true);
                    if (is_array($decoded)) {
                        $aiAnalytics = $decoded;
                    }
                }
                if (!$aiAnalytics && ($existing['score'] !== null || !empty($existing['summary']))) {
                    $aiAnalytics = [
                        'overall_score' => $existing['score'],
                        'summary' => $existing['summary'] ?? null
                    ];
                }
            } else {
                try {
                    $audioPath = __DIR__ . '/../../public/' . ltrim($call['recording_path'], '/');
                    $service = new GeminiService();
                    $criteriaModel = new AiEvaluationCriteria();
                    $criteria = $this->formatCriteria($criteriaModel->getMatching(
                        $call['project_id'] ?? null,
                        $call['campaign_id'] ?? null,
                        $call['call_type'] ?? null
                    ));
                    $result = $service->analyzeCallAudio($audioPath, [
                        'agent' => $call['agent'] ?? '',
                        'project' => $call['project'] ?? '',
                        'campaign' => $call['campaign'] ?? '',
                        'call_type' => $call['call_type'] ?? '',
                        'duration' => $call['duration'] ?? '',
                        'date' => $call['date'] ?? '',
                        'notes' => $call['notes'] ?? '',
                        'criteria' => $criteria
                    ]);

                    $metricsJson = json_encode($result['data'], JSON_UNESCAPED_UNICODE);
                    $rawJson = json_encode($result['raw'], JSON_UNESCAPED_UNICODE);
                    if ($existing) {
                        $analyticsModel->update($existing['id'], [
                            'score' => $result['score'],
                            'summary' => $result['summary'],
                            'metrics_json' => $metricsJson,
                            'raw_response_json' => $rawJson
                        ]);
                    } else {
                        $analyticsModel->create([
                            'call_id' => $call['id'],
                            'model' => \App\Config\Config::$GEMINI_MODEL,
                            'score' => $result['score'],
                            'summary' => $result['summary'],
                            'metrics_json' => $metricsJson,
                            'raw_response_json' => $rawJson
                        ]);
                    }

                    $aiAnalytics = $result['data'];
                } catch (\Throwable $e) {
                    $aiAnalyticsError = $e->getMessage();
                }
            }
        }

        return [$aiAnalytics, $aiAnalyticsError];
    }

    private function formatCriteria(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $lines = [];
        foreach ($rows as $row) {
            $scope = [];
            if (!empty($row['project_name'])) {
                $scope[] = 'Proyecto: ' . $row['project_name'];
            }
            if (!empty($row['campaign_name'])) {
                $scope[] = 'Campaña: ' . $row['campaign_name'];
            }
            if (!empty($row['call_type'])) {
                $scope[] = 'Tipo: ' . $row['call_type'];
            }
            $prefix = !empty($scope) ? ('[' . implode(' | ', $scope) . '] ') : '';
            $lines[] = $prefix . trim($row['criteria_text']);
        }

        return implode("\n", $lines);
    }

    private function jsonResponse(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
