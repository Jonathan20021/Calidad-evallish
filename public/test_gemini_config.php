<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Services\GeminiService;

Config::init();

echo "=== Test de Configuración de Gemini ===\n\n";

echo "✓ API Key configurada: " . substr(Config::$GEMINI_API_KEY, 0, 20) . "...\n";
echo "✓ Modelo: " . Config::$GEMINI_MODEL . "\n";
echo "✓ Timeout: " . Config::$GEMINI_TIMEOUT . " segundos\n\n";

try {
    $gemini = new GeminiService();
    echo "✓ GeminiService inicializado correctamente\n\n";
    echo "=== Configuración OK - Todo listo para usar! ===\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
