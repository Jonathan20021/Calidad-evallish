<?php
/**
 * Script de prueba para verificar la configuración de PHPMailer
 * 
 * Uso:
 * 1. Configura tu archivo .env con tus credenciales SMTP
 * 2. Cambia la variable $emailDestino con tu email de prueba
 * 3. Accede desde el navegador: http://localhost/Calidad-evallish/public/test_phpmailer.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Services\EmailService;

// Inicializar configuración
Config::init();

// Email de prueba (CÁMBIALO por tu email)
$emailDestino = 'notificaciones@evallishbpo.com'; // Cambia esto por tu email de prueba

echo "<h2>🧪 Prueba de Configuración de PHPMailer</h2>";
echo "<hr>";

// Verificar que EmailService exista
if (!class_exists('App\Services\EmailService')) {
    echo "<p style='color: red;'>❌ EmailService no encontrado</p>";
    exit;
}

echo "<p>✅ EmailService cargado correctamente</p>";

// Mostrar configuración actual (sin mostrar la contraseña)
echo "<h3>📋 Configuración actual:</h3>";
echo "<ul>";
echo "<li><strong>MAIL_HOST:</strong> " . (getenv('MAIL_HOST') ?: 'No configurado') . "</li>";
echo "<li><strong>MAIL_PORT:</strong> " . (getenv('MAIL_PORT') ?: 'No configurado') . "</li>";
echo "<li><strong>MAIL_USERNAME:</strong> " . (getenv('MAIL_USERNAME') ?: 'No configurado') . "</li>";
echo "<li><strong>MAIL_PASSWORD:</strong> " . (getenv('MAIL_PASSWORD') ? '****** (Configurado)' : 'No configurado') . "</li>";
echo "<li><strong>MAIL_ENCRYPTION:</strong> " . (getenv('MAIL_ENCRYPTION') ?: 'No configurado') . "</li>";
echo "<li><strong>MAIL_FROM_ADDRESS:</strong> " . (getenv('MAIL_FROM_ADDRESS') ?: 'noreply@evallish.com (default)') . "</li>";
echo "<li><strong>MAIL_FROM_NAME:</strong> " . (getenv('MAIL_FROM_NAME') ?: Config::$APP_NAME . ' (default)') . "</li>";
echo "</ul>";

// Verificar configuración mínima
if (!getenv('MAIL_HOST') || !getenv('MAIL_USERNAME') || !getenv('MAIL_PASSWORD')) {
    echo "<p style='color: orange;'>⚠️ <strong>Advertencia:</strong> Faltan configuraciones SMTP en el archivo .env</p>";
    echo "<p>Por favor, configura las siguientes variables en tu archivo .env:</p>";
    echo "<pre>";
    echo "MAIL_HOST=smtp.gmail.com\n";
    echo "MAIL_PORT=587\n";
    echo "MAIL_USERNAME=tucorreo@gmail.com\n";
    echo "MAIL_PASSWORD=tu_password_o_app_password\n";
    echo "MAIL_ENCRYPTION=tls\n";
    echo "MAIL_FROM_ADDRESS=tucorreo@gmail.com\n";
    echo "MAIL_FROM_NAME=\"Evallish BPO\"\n";
    echo "</pre>";
    exit;
}

// Intentar enviar correo de prueba
echo "<hr>";
echo "<h3>📧 Enviando correo de prueba...</h3>";
echo "<p><strong>Destinatario:</strong> $emailDestino</p>";

if ($emailDestino === 'notificaciones@evallishbpo.com') {
    echo "<p style='color: orange;'>⚠️ <strong>Nota:</strong> Estás usando el email de notificaciones del sistema. Considera cambiarlo por tu email personal para la prueba.</p>";
}

try {
    $emailService = new EmailService();
    
    $subject = "🧪 Prueba de PHPMailer - " . Config::$APP_NAME;
    
    $logoUrl = rtrim(Config::$BASE_URL, '/') . '/logo.png';
    
    $htmlBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: white; padding: 40px 20px; text-align: center; }
            .logo { max-width: 180px; height: auto; margin-bottom: 20px; }
            .content { padding: 30px 20px; }
            .footer { background: #1e3a8a; color: #dbeafe; padding: 20px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <img src='" . $logoUrl . "' alt='Logo' class='logo'>
            <h1>✅ Prueba Exitosa de PHPMailer</h1>
        </div>
        <div class='content'>
            <h2>¡Felicitaciones!</h2>
            <p>Tu configuración de PHPMailer está funcionando correctamente.</p>
            <p><strong>Detalles de la prueba:</strong></p>
            <ul>
                <li>Servidor SMTP: " . getenv('MAIL_HOST') . "</li>
                <li>Puerto: " . getenv('MAIL_PORT') . "</li>
                <li>Encriptación: " . getenv('MAIL_ENCRYPTION') . "</li>
                <li>Usuario: " . getenv('MAIL_USERNAME') . "</li>
            </ul>
            <p>Ahora puedes usar el sistema de envío de credenciales a clientes corporativos.</p>
        </div>
        <div class='footer'>
            <p>Este es un correo de prueba automático de " . Config::$APP_NAME . "</p>
            <p>Fecha: " . date('Y-m-d H:i:s') . "</p>
        </div>
    </body>
    </html>
    ";
    
    $resultado = $emailService->send($emailDestino, $subject, $htmlBody);
    
    if ($resultado) {
        echo "<p style='color: green; font-size: 18px;'><strong>✅ ¡Correo enviado exitosamente!</strong></p>";
        echo "<p>Revisa tu bandeja de entrada (y spam) en: <strong>$emailDestino</strong></p>";
        echo "<p>Si el correo no llega:</p>";
        echo "<ol>";
        echo "<li>Revisa la carpeta de SPAM</li>";
        echo "<li>Verifica las credenciales en .env</li>";
        echo "<li>Para Gmail, asegúrate de usar una Contraseña de Aplicación</li>";
        echo "<li>Revisa los logs de PHP para más detalles</li>";
        echo "</ol>";
    } else {
        echo "<p style='color: red;'><strong>❌ Error al enviar el correo</strong></p>";
        echo "<p>Revisa los logs de PHP para más detalles del error.</p>";
        echo "<p><strong>Cosas a verificar:</strong></p>";
        echo "<ul>";
        echo "<li>Las credenciales en .env son correctas</li>";
        echo "<li>Para Gmail: usa una Contraseña de Aplicación (no tu contraseña normal)</li>";
        echo "<li>El puerto no está bloqueado por tu firewall</li>";
        echo "<li>Tu proveedor de internet permite conexiones SMTP salientes</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Excepción capturada:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><small>Para más información, revisa el archivo <strong>CONFIGURACION_CORREOS.md</strong></small></p>";

