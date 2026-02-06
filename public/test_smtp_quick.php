<?php
/**
 * Prueba rápida de conexión SMTP
 * Muestra mensajes de debug detallados para diagnosticar problemas
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

echo "<h2>🧪 Prueba Rápida de Conexión SMTP</h2>";
echo "<hr>";

// Configuración SMTP
$config = [
    'host' => 'mail.evallishbpo.com',
    'port' => 587,
    'username' => 'notificaciones@evallishbpo.com',
    'password' => 'Admin#2025#',
    'encryption' => 'tls', // 'tls' o 'ssl'
    'from_email' => 'notificaciones@evallishbpo.com',
    'from_name' => 'Evallish BPO - Test',
    'to_email' => 'notificaciones@evallishbpo.com' // Email de prueba
];

echo "<h3>📋 Configuración:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> {$config['host']}</li>";
echo "<li><strong>Puerto:</strong> {$config['port']}</li>";
echo "<li><strong>Usuario:</strong> {$config['username']}</li>";
echo "<li><strong>Encriptación:</strong> {$config['encryption']}</li>";
echo "<li><strong>Destinatario:</strong> {$config['to_email']}</li>";
echo "</ul>";
echo "<hr>";

$mail = new PHPMailer(true);

try {
    // Configuración del servidor
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Muestra todo el debug
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->Port = $config['port'];
    
    // Configurar encriptación
    if ($config['encryption'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    
    // Opciones adicionales para debugging
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    // Remitente y destinatario
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email']);
    
    // Contenido
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Prueba SMTP - ' . date('Y-m-d H:i:s');
    $mail->Body = '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2 style="color: #4F46E5;">✅ Prueba Exitosa de SMTP</h2>
            <p>Este correo confirma que la configuración SMTP está funcionando correctamente.</p>
            <p><strong>Servidor:</strong> ' . $config['host'] . ':' . $config['port'] . '</p>
            <p><strong>Encriptación:</strong> ' . strtoupper($config['encryption']) . '</p>
            <p><strong>Fecha/Hora:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </body>
        </html>
    ';
    $mail->AltBody = 'Prueba exitosa de SMTP desde ' . $config['host'];
    
    echo "<h3>📤 Intentando enviar correo...</h3>";
    echo "<pre style='background: #f3f4f6; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    
    $mail->send();
    
    echo "</pre>";
    echo "<h3 style='color: green;'>✅ ¡Correo enviado exitosamente!</h3>";
    echo "<p>Revisa la bandeja de entrada de: <strong>{$config['to_email']}</strong></p>";
    
} catch (Exception $e) {
    echo "</pre>";
    echo "<h3 style='color: red;'>❌ Error al enviar el correo</h3>";
    echo "<div style='background: #fee; padding: 15px; border-left: 4px solid #f00; margin: 10px 0;'>";
    echo "<p><strong>Error de PHPMailer:</strong></p>";
    echo "<pre>" . htmlspecialchars($mail->ErrorInfo) . "</pre>";
    echo "<p><strong>Excepción:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
    
    echo "<h3>🔍 Posibles soluciones:</h3>";
    echo "<ol>";
    echo "<li><strong>Verifica las credenciales:</strong> Asegúrate que el email y contraseña sean correctos en cPanel</li>";
    echo "<li><strong>Prueba otro puerto:</strong> Si 587 falla, prueba 465 con SSL</li>";
    echo "<li><strong>Revisa el firewall:</strong> El servidor debe permitir conexiones SMTP salientes</li>";
    echo "<li><strong>Contacta al hosting:</strong> Algunos hostings bloquean SMTP por defecto</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><small><a href='test_phpmailer.php'>← Volver a la prueba completa</a></small></p>";
