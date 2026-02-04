<?php
/**
 * Script de verificación de configuración para producción
 * Ejecutar desde la línea de comandos o navegador
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;

echo "<pre>";
echo "=== Verificación de Configuración de Producción ===\n\n";

// 1. Verificar rutas posibles para .env
echo "1. Buscando archivo .env...\n";
$possiblePaths = [
    dirname(__DIR__) . '/.env',
    dirname($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) . '/.env',
    __DIR__ . '/../.env',
];

$envFound = false;
foreach ($possiblePaths as $path) {
    $realPath = realpath($path);
    if (file_exists($path)) {
        echo "   ✓ ENCONTRADO: $path\n";
        if ($realPath) echo "     Ruta real: $realPath\n";
        $envFound = true;
        
        // Verificar permisos
        if (is_readable($path)) {
            echo "     ✓ Archivo es legible\n";
        } else {
            echo "     ✗ ERROR: Archivo no es legible (permisos)\n";
        }
        break;
    } else {
        echo "   ✗ No existe: $path\n";
    }
}

if (!$envFound) {
    echo "\n   ⚠️  ADVERTENCIA: Archivo .env no encontrado\n";
    echo "   Necesitas crear el archivo .env en la raíz del proyecto\n\n";
}

echo "\n2. Inicializando configuración...\n";
try {
    Config::init();
    echo "   ✓ Config::init() ejecutado\n\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Verificar variables cargadas
echo "3. Variables de configuración:\n";
$configs = [
    'APP_NAME' => Config::$APP_NAME,
    'TIMEZONE' => Config::$TIMEZONE,
    'GEMINI_API_KEY' => Config::$GEMINI_API_KEY ? substr(Config::$GEMINI_API_KEY, 0, 20) . '...' : '❌ NO CONFIGURADA',
    'GEMINI_MODEL' => Config::$GEMINI_MODEL,
];

foreach ($configs as $key => $value) {
    $status = ($key === 'GEMINI_API_KEY' && strpos($value, '❌') !== false) ? '✗' : '✓';
    echo "   $status $key: $value\n";
}

// 4. Verificar timezone
echo "\n4. Verificando timezone:\n";
echo "   Configurado: " . date_default_timezone_get() . "\n";
echo "   Hora actual: " . date('Y-m-d H:i:s') . "\n";

// 5. Test de GeminiService
echo "\n5. Probando GeminiService:\n";
try {
    $gemini = new App\Services\GeminiService();
    echo "   ✓ GeminiService inicializado correctamente\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
    echo "\n   SOLUCIÓN: Verifica que GEMINI_API_KEY esté configurada en el archivo .env\n";
}

echo "\n=== Verificación Completada ===\n";
echo "\nSi ves errores, revisa el archivo CONFIGURACION_API_KEYS.md\n";
echo "</pre>";
