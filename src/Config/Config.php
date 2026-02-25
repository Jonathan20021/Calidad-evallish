<?php

namespace App\Config;

class Config
{
    // Configuración de la aplicación
    public static string $APP_NAME;
    public static string $BASE_URL;
    public static string $TIMEZONE;
    
    // Configuración de Gemini AI
    public static string $GEMINI_API_KEY;
    public static string $GEMINI_MODEL;
    public static int $GEMINI_CONNECT_TIMEOUT;
    public static int $GEMINI_TIMEOUT;

    // Constantes para retrocompatibilidad
    const APP_NAME = 'Evallish BPO';
    const BASE_URL = '/';
    const TIMEZONE = 'America/Bogota';
    const GEMINI_API_KEY = '';
    const GEMINI_MODEL = 'gemini-3-flash-preview';
    const GEMINI_CONNECT_TIMEOUT = 10;
    const GEMINI_TIMEOUT = 60;

    public static function init()
    {
        self::loadEnv();
        
        // Cargar valores desde .env o usar valores por defecto
        self::$APP_NAME = self::getEnv('APP_NAME', 'Evallish BPO');
        self::$BASE_URL = self::getEnv('BASE_URL', '/');
        self::$TIMEZONE = self::getEnv('TIMEZONE', 'America/Bogota');
        self::$GEMINI_API_KEY = self::getEnv('GEMINI_API_KEY', '');
        self::$GEMINI_MODEL = self::getEnv('GEMINI_MODEL', 'gemini-3-flash-preview');
        self::$GEMINI_CONNECT_TIMEOUT = (int)self::getEnv('GEMINI_CONNECT_TIMEOUT', '10');
        self::$GEMINI_TIMEOUT = (int)self::getEnv('GEMINI_TIMEOUT', '60');

        date_default_timezone_set(self::$TIMEZONE);
        self::configureSession();
        session_start();
    }

    private static function configureSession(): void
    {
        // 10 years by default to avoid automatic expiration in long-running usage.
        $lifetime = (int) self::getEnv('SESSION_LIFETIME_SECONDS', '315360000');
        if ($lifetime < 0) {
            $lifetime = 315360000;
        }

        ini_set('session.gc_maxlifetime', (string) $lifetime);
        ini_set('session.cookie_lifetime', (string) $lifetime);
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function loadEnv()
    {
        // Intentar múltiples rutas posibles para el archivo .env
        $possiblePaths = [
            dirname(__DIR__, 2) . '/.env',                    // Desde src/Config
            dirname($_SERVER['DOCUMENT_ROOT']) . '/.env',      // Un nivel arriba de public
            $_SERVER['DOCUMENT_ROOT'] . '/../.env',            // Alternativa
            __DIR__ . '/../../.env',                           // Relativo a este archivo
        ];
        
        $envFile = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $envFile = $path;
                break;
            }
        }
        
        if (!$envFile) {
            // Log para debug (opcional)
            error_log("Warning: .env file not found in any of the expected locations");
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                $value = trim($value, '"\'');
                
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    private static function getEnv(string $key, string $default = ''): string
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
}
