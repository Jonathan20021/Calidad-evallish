<?php

namespace App\Services;

use App\Config\Config;

class GeminiService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = Config::$GEMINI_API_KEY;
        $this->model = Config::$GEMINI_MODEL;
        if (trim($this->apiKey) === '') {
            throw new \RuntimeException('GEMINI_API_KEY no configurada.');
        }
    }

    public function analyzeCallAudio(string $filePath, array $context = []): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('No se puede leer el archivo de audio.');
        }

        $prompt = $this->buildPrompt($context);
        $contents = $this->buildAudioContents($filePath, $prompt);

        $response = $this->generateContent($contents);
        $text = $this->extractText($response);
        $data = $this->parseJsonPayload($text);

        if (!is_array($data)) {
            $data = [
                'overall_score' => null,
                'summary' => $text,
                'sentiment' => 'desconocido',
                'punto_de_vista' => 'desconocido',
                'analisis' => 'desconocido',
                'agent_strengths' => [],
                'agent_opportunities' => [],
                'compliance' => [],
                'critical_issues' => [],
                'coaching_tips' => [],
                'call_tags' => [],
                'next_best_actions' => []
            ];
        }

        return [
            'summary' => $data['summary'] ?? null,
            'score' => $data['overall_score'] ?? null,
            'data' => $data,
            'raw' => $response
        ];
    }

    public function generateTrainingScriptFromAudio(string $filePath, array $context = []): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('No se puede leer el archivo de audio.');
        }

        $prompt = $this->buildTrainingScriptPrompt($context);
        $contents = $this->buildAudioContents($filePath, $prompt);

        $response = $this->generateContent($contents);
        $text = $this->extractText($response);
        $data = $this->parseJsonPayload($text);

        if (!is_array($data)) {
            throw new \RuntimeException('No se pudo generar un guion de entrenamiento.');
        }

        return $data;
    }

    public function generateRoleplayReply(array $context): string
    {
        $prompt = $this->buildRoleplayPrompt($context);
        return $this->generateTextFromPrompt($prompt);
    }

    public function generateRoleplayFeedback(array $context): array
    {
        $prompt = $this->buildRoleplayFeedbackPrompt($context);
        $text = $this->generateTextFromPrompt($prompt);
        $data = $this->parseJsonPayload($text);
        if (!is_array($data)) {
            throw new \RuntimeException('No se pudo generar feedback del roleplay.');
        }
        return $data;
    }

    public function generateRoleplaySummary(array $context): array
    {
        $prompt = $this->buildRoleplaySummaryPrompt($context);
        $text = $this->generateTextFromPrompt($prompt);
        $data = $this->parseJsonPayload($text);
        if (!is_array($data)) {
            throw new \RuntimeException('No se pudo generar el resumen del roleplay.');
        }
        return $data;
    }

    public function generateTrainingExam(array $context): array
    {
        $prompt = $this->buildExamPrompt($context);
        $text = $this->generateTextFromPrompt($prompt);
        $data = $this->parseJsonPayload($text);
        if (!is_array($data)) {
            throw new \RuntimeException('No se pudo generar el examen.');
        }
        return $data;
    }

    public function gradeTrainingExam(array $context): array
    {
        $prompt = $this->buildExamGradingPrompt($context);
        $text = $this->generateTextFromPrompt($prompt);
        $data = $this->parseJsonPayload($text);
        if (!is_array($data)) {
            throw new \RuntimeException('No se pudo calificar el examen.');
        }
        return $data;
    }

    private function buildPrompt(array $context): string
    {
        $lines = [
            'Analiza la llamada para control de calidad.',
            'Devuelve SOLO JSON valido con las claves:',
            'overall_score (0-100), summary, sentiment (positivo|neutral|negativo),',
            'punto_de_vista, analisis,',
            'agent_strengths (array), agent_opportunities (array),',
            'compliance (obj con saludo, identificacion, verificacion, escucha_activa, empatia, cierre, politica),',
            'critical_issues (array), coaching_tips (array), call_tags (array), next_best_actions (array).',
            'Si falta info, usa "desconocido" o lista vacia. Responde en espanol sin markdown.'
        ];

        $meta = [];
        if (!empty($context['agent'])) {
            $meta[] = 'Agente: ' . $context['agent'];
        }
        if (!empty($context['campaign'])) {
            $meta[] = 'Campana: ' . $context['campaign'];
        }
        if (!empty($context['project'])) {
            $meta[] = 'Proyecto: ' . $context['project'];
        }
        if (!empty($context['call_type'])) {
            $meta[] = 'Tipo de llamada: ' . $context['call_type'];
        }
        if (!empty($context['duration'])) {
            $meta[] = 'Duracion: ' . $context['duration'];
        }
        if (!empty($context['date'])) {
            $meta[] = 'Fecha: ' . $context['date'];
        }
        if (!empty($context['notes'])) {
            $meta[] = 'Notas: ' . $context['notes'];
        }

        if (!empty($meta)) {
            $lines[] = 'Contexto: ' . implode(' | ', $meta);
        }
        if (!empty($context['criteria'])) {
            $lines[] = 'Criterios de evaluacion aplicables:';
            $lines[] = $context['criteria'];
        }

        return implode("\n", $lines);
    }

    private function buildTrainingScriptPrompt(array $context): string
    {
        $lines = [
            'A partir de esta llamada, genera un guion de entrenamiento para roleplay.',
            'Devuelve SOLO JSON valido con las claves:',
            'title, scenario, customer_profile, customer_goal, objections (array),',
            'key_moments (array), ideal_agent_behaviors (array), script.',
            'Responde en espanol sin markdown.'
        ];

        $meta = [];
        if (!empty($context['agent'])) {
            $meta[] = 'Agente: ' . $context['agent'];
        }
        if (!empty($context['campaign'])) {
            $meta[] = 'Campana: ' . $context['campaign'];
        }
        if (!empty($context['score'])) {
            $meta[] = 'Puntaje QA: ' . $context['score'];
        }
        if (!empty($context['notes'])) {
            $meta[] = 'Notas: ' . $context['notes'];
        }
        if (!empty($meta)) {
            $lines[] = 'Contexto: ' . implode(' | ', $meta);
        }

        return implode("\n", $lines);
    }

    private function buildRoleplayPrompt(array $context): string
    {
        $lines = [
            'Actua como cliente en un roleplay de contact center.',
            'Reglas:',
            '- Responde solo como cliente.',
            '- 1 a 3 frases por turno.',
            '- Usa un tono realista.',
            '- Si el agente cierra correctamente, acepta y despide.',
            'Contexto del roleplay:'
        ];

        if (!empty($context['scenario'])) {
            $lines[] = 'Escenario: ' . $context['scenario'];
        }
        if (!empty($context['tone'])) {
            $lines[] = 'Tono del cliente: ' . $context['tone'];
        }
        if (!empty($context['obstacles'])) {
            $lines[] = 'Obstaculos del cliente: ' . $context['obstacles'];
        }
        if (!empty($context['persona'])) {
            $lines[] = 'Perfil del cliente: ' . $context['persona'];
        }
        if (!empty($context['script'])) {
            $lines[] = 'Guion base: ' . $context['script'];
        }
        if (!empty($context['history'])) {
            $lines[] = 'Historial reciente: ' . $context['history'];
        }
        if (!empty($context['agent_message'])) {
            $lines[] = 'Mensaje del agente: ' . $context['agent_message'];
        }

        $lines[] = 'Responde solo con el texto del cliente, sin etiquetas ni markdown.';

        return implode("\n", $lines);
    }

    private function buildRoleplayFeedbackPrompt(array $context): string
    {
        $lines = [
            'Eres un QA coach y vas a calificar el ultimo turno del agente.',
            'Devuelve SOLO JSON valido con las claves: score (0-100), feedback, checklist (array).',
            'checklist debe incluir items como: saludo, identificacion, verificacion, empatia, resolucion, cierre.',
            'Responde en espanol sin markdown.'
        ];

        if (!empty($context['objectives'])) {
            $lines[] = 'Objetivos: ' . $context['objectives'];
        }
        if (!empty($context['scenario'])) {
            $lines[] = 'Escenario: ' . $context['scenario'];
        }
        if (!empty($context['history'])) {
            $lines[] = 'Historial: ' . $context['history'];
        }
        if (!empty($context['agent_message'])) {
            $lines[] = 'Ultimo mensaje del agente: ' . $context['agent_message'];
        }

        return implode("\n", $lines);
    }

    private function buildRoleplaySummaryPrompt(array $context): string
    {
        $lines = [
            'Genera un resumen final del roleplay y una calificacion global.',
            'Devuelve SOLO JSON valido con las claves: score (0-100), summary, strengths (array), improvements (array), actions (array de 3 pasos).',
            'Responde en espanol sin markdown.'
        ];

        if (!empty($context['objectives'])) {
            $lines[] = 'Objetivos: ' . $context['objectives'];
        }
        if (!empty($context['scenario'])) {
            $lines[] = 'Escenario: ' . $context['scenario'];
        }
        if (!empty($context['transcript'])) {
            $lines[] = 'Transcripcion: ' . $context['transcript'];
        }

        return implode("\n", $lines);
    }

    private function buildExamPrompt(array $context): string
    {
        $lines = [
            'Genera un examen personalizado para entrenamiento de agentes.',
            'Devuelve SOLO JSON valido con las claves: title, questions.',
            'questions es un array de objetos con: question, type (mcq|open), options (array, solo mcq), correct_answer, weight.',
            'Escribe en espanol, sin markdown.'
        ];

        if (!empty($context['agent'])) {
            $lines[] = 'Agente: ' . $context['agent'];
        }
        if (!empty($context['campaign'])) {
            $lines[] = 'Campana: ' . $context['campaign'];
        }
        if (!empty($context['weak_areas'])) {
            $lines[] = 'Debilidades detectadas: ' . $context['weak_areas'];
        }
        if (!empty($context['num_questions'])) {
            $lines[] = 'Cantidad de preguntas: ' . (int) $context['num_questions'];
        }
        if (!empty($context['difficulty'])) {
            $lines[] = 'Dificultad: ' . $context['difficulty'];
        }

        return implode("\n", $lines);
    }

    private function buildExamGradingPrompt(array $context): string
    {
        $lines = [
            'Eres un QA que califica un examen de entrenamiento.',
            'Devuelve SOLO JSON valido con las claves: summary, answers.',
            'answers es un array con objetos: question_index, score, feedback.',
            'Respeta el peso maximo por pregunta.',
            'Responde en espanol, sin markdown.'
        ];

        if (!empty($context['questions'])) {
            $lines[] = 'Preguntas: ' . $context['questions'];
        }
        if (!empty($context['answers'])) {
            $lines[] = 'Respuestas del agente: ' . $context['answers'];
        }

        return implode("\n", $lines);
    }

    private function detectMimeType(string $filePath): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $filePath);
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        return 'audio/mpeg';
    }

    private function buildAudioContents(string $filePath, string $prompt): array
    {
        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            throw new \RuntimeException('No se pudo obtener el tamano del audio.');
        }

        $mimeType = $this->detectMimeType($filePath);

        if ($fileSize <= 18 * 1024 * 1024) {
            $audioBytes = file_get_contents($filePath);
            if ($audioBytes === false) {
                throw new \RuntimeException('No se pudo leer el audio.');
            }

            return [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => base64_encode($audioBytes)
                            ]
                        ]
                    ]
                ]
            ];
        }

        $fileInfo = $this->uploadFileResumable($filePath, $mimeType);
        $fileUri = $fileInfo['uri'] ?? ($fileInfo['file']['uri'] ?? null);
        if (!$fileUri) {
            throw new \RuntimeException('No se obtuvo file_uri del upload.');
        }

        return [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt],
                    [
                        'file_data' => [
                            'mime_type' => $mimeType,
                            'file_uri' => $fileUri
                        ]
                    ]
                ]
            ]
        ];
    }

    private function generateContent(array $contents, ?string $responseMimeType = 'application/json'): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 4096
            ]
        ];
        if ($responseMimeType) {
            $payload['generationConfig']['response_mime_type'] = $responseMimeType;
        }

        $response = $this->postJsonWithRetry($url, $payload, [
            'x-goog-api-key: ' . $this->apiKey
        ]);

        if ($response['status'] >= 300 && $responseMimeType) {
            unset($payload['generationConfig']['response_mime_type']);
            $retry = $this->postJsonWithRetry($url, $payload, [
                'x-goog-api-key: ' . $this->apiKey
            ]);
            if ($retry['status'] < 300) {
                $response = $retry;
            } else {
                $response = $retry;
            }
        }

        if ($response['status'] >= 300) {
            if ($response['status'] === 503) {
                throw new \RuntimeException('Gemini saturado (503). Intenta en unos minutos.');
            }
            throw new \RuntimeException('Gemini error (' . $response['status'] . '): ' . $response['body']);
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            throw new \RuntimeException('Respuesta invalida de Gemini.');
        }

        return $data;
    }

    private function generateTextFromPrompt(string $prompt): string
    {
        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ];

        $response = $this->generateContent($contents, null);
        $text = $this->extractText($response);
        if ($text === '') {
            throw new \RuntimeException('Respuesta vacia del modelo.');
        }
        return $text;
    }

    private function extractText(array $response): string
    {
        $text = '';
        if (!empty($response['candidates'][0]['content']['parts'])) {
            foreach ($response['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $text .= $part['text'];
                }
            }
        }
        return trim($text);
    }

    private function parseJsonPayload(string $text): ?array
    {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $clean = trim($text);
        if (stripos($clean, '```') !== false) {
            $clean = preg_replace('/```(?:json)?/i', '', $clean);
            $clean = trim($clean);
        }

        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($clean, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function uploadFileResumable(string $filePath, string $mimeType): array
    {
        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            throw new \RuntimeException('No se pudo obtener el tamano del audio.');
        }

        $displayName = basename($filePath);
        $startUrl = 'https://generativelanguage.googleapis.com/upload/v1beta/files';
        $startPayload = json_encode([
            'file' => [
                'display_name' => $displayName
            ]
        ]);

        $startResponse = $this->postRaw($startUrl, $startPayload, [
            'x-goog-api-key: ' . $this->apiKey,
            'X-Goog-Upload-Protocol: resumable',
            'X-Goog-Upload-Command: start',
            'X-Goog-Upload-Header-Content-Length: ' . $fileSize,
            'X-Goog-Upload-Header-Content-Type: ' . $mimeType,
            'Content-Type: application/json'
        ]);

        if ($startResponse['status'] >= 300) {
            throw new \RuntimeException('Gemini upload start error (' . $startResponse['status'] . '): ' . $startResponse['body']);
        }

        $uploadUrl = $startResponse['headers']['x-goog-upload-url'] ?? null;
        if (!$uploadUrl) {
            throw new \RuntimeException('No se obtuvo URL de subida.');
        }

        $audioBytes = file_get_contents($filePath);
        if ($audioBytes === false) {
            throw new \RuntimeException('No se pudo leer el audio.');
        }

        $uploadResponse = $this->postRaw($uploadUrl, $audioBytes, [
            'X-Goog-Upload-Command: upload, finalize',
            'X-Goog-Upload-Offset: 0',
            'Content-Length: ' . $fileSize,
            'Content-Type: ' . $mimeType
        ]);

        if ($uploadResponse['status'] >= 300) {
            throw new \RuntimeException('Gemini upload error (' . $uploadResponse['status'] . '): ' . $uploadResponse['body']);
        }

        $fileInfo = json_decode($uploadResponse['body'], true);
        if (!is_array($fileInfo)) {
            throw new \RuntimeException('Respuesta invalida del upload.');
        }

        if (!empty($fileInfo['file']['name'])) {
            $fileInfo = $this->waitForFileActive($fileInfo['file']['name']);
        }

        return $fileInfo;
    }

    private function waitForFileActive(string $fileName): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/' . $fileName;
        $maxAttempts = 8;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->getJson($url, [
                'x-goog-api-key: ' . $this->apiKey
            ]);

            if ($response['status'] >= 300) {
                throw new \RuntimeException('Gemini file status error (' . $response['status'] . '): ' . $response['body']);
            }

            $data = json_decode($response['body'], true);
            if (is_array($data)) {
                $state = $data['state'] ?? ($data['file']['state'] ?? null);
                if ($state === 'ACTIVE' || $state === null) {
                    return $data;
                }
            }

            usleep(500000);
        }

        throw new \RuntimeException('El archivo no entro en estado ACTIVE.');
    }

    private function postJson(string $url, array $payload, array $headers = []): array
    {
        return $this->postRaw($url, json_encode($payload), array_merge($headers, [
            'Content-Type: application/json'
        ]));
    }

    private function postJsonWithRetry(string $url, array $payload, array $headers = []): array
    {
        $attempts = 3;
        $delayMs = 400;
        for ($i = 0; $i < $attempts; $i++) {
            $response = $this->postJson($url, $payload, $headers);
            if ($response['status'] !== 429 && $response['status'] !== 503) {
                return $response;
            }
            usleep($delayMs * 1000);
            $delayMs *= 2;
        }
        return $response;
    }

    private function getJson(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::$GEMINI_CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::$GEMINI_TIMEOUT);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException($this->formatCurlError($error));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerText = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        return [
            'status' => $status,
            'headers' => $this->parseHeaders($headerText),
            'body' => $body
        ];
    }

    private function postRaw(string $url, string $payload, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::$GEMINI_CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::$GEMINI_TIMEOUT);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException($this->formatCurlError($error));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerText = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        return [
            'status' => $status,
            'headers' => $this->parseHeaders($headerText),
            'body' => $body
        ];
    }

    private function parseHeaders(string $headerText): array
    {
        $headers = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($headerText));
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }
        return $headers;
    }

    private function formatCurlError(string $error): string
    {
        if (stripos($error, 'timed out') !== false) {
            return 'Tiempo de espera agotado al contactar Gemini. Intenta de nuevo en unos minutos.';
        }
        return 'Curl error: ' . $error;
    }
}
