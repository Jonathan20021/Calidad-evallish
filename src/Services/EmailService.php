<?php

namespace App\Services;

use App\Config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService
{
    private string $fromEmail;
    private string $fromName;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private bool $smtpSecure;
    private bool $debug;
    
    public function __construct()
    {
        // Configuración del remitente
        $this->fromEmail = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@evallish.com';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: Config::$APP_NAME;
        
        // Configuración SMTP
        $this->smtpHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $this->smtpPort = (int)(getenv('MAIL_PORT') ?: 587);
        $this->smtpUsername = getenv('MAIL_USERNAME') ?: '';
        $this->smtpPassword = getenv('MAIL_PASSWORD') ?: '';
        $this->smtpSecure = getenv('MAIL_ENCRYPTION') === 'ssl' ? true : false; // ssl o tls
        $this->debug = getenv('MAIL_DEBUG') === 'true';
    }

    /**
     * Envía un correo electrónico usando PHPMailer
     * 
     * @param string $to Dirección de correo del destinatario
     * @param string $subject Asunto del correo
     * @param string $htmlBody Cuerpo del correo en HTML
     * @param string $textBody Cuerpo del correo en texto plano (opcional)
     * @return bool True si se envió correctamente
     */
    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        // Validar email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Email Service: Invalid email address: $to");
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = !empty($this->smtpUsername);
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = $this->smtpSecure ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            $mail->CharSet = 'UTF-8';
            
            // Debug (solo si está activado)
            if ($this->debug) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            // Remitente y destinatario
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = !empty($textBody) ? $textBody : strip_tags($htmlBody);
            
            // Enviar
            $success = $mail->send();
            
            if ($success) {
                error_log("Email Service: Email sent successfully to $to via PHPMailer");
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Email Service: Failed to send email to $to. Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Envía correo de bienvenida con credenciales al cliente corporativo
     * 
     * @param array $clientData Datos del cliente corporativo
     * @param array $userData Datos del usuario del portal (username, password)
     * @return bool True si se envió correctamente
     */
    public function sendClientWelcomeEmail(array $clientData, array $userData): bool
    {
        $to = $clientData['contact_email'] ?? '';
        
        if (empty($to)) {
            error_log("Email Service: No contact email provided for client: " . ($clientData['name'] ?? 'Unknown'));
            return false;
        }

        $clientName = $clientData['name'] ?? 'Cliente';
        $contactName = $clientData['contact_name'] ?? 'Estimado/a';
        $username = $userData['username'] ?? '';
        $password = $userData['password'] ?? '';
        $fullName = $userData['full_name'] ?? '';
        
        // URL del portal de clientes
        $portalUrl = rtrim(Config::$BASE_URL, '/') . '/client-portal';

        $subject = "Bienvenido a " . Config::$APP_NAME . " - Portal de Cliente";

        $htmlBody = $this->getWelcomeEmailTemplate(
            $contactName,
            $clientName,
            $username,
            $password,
            $fullName,
            $portalUrl
        );

        return $this->send($to, $subject, $htmlBody);
    }

    /**
     * Genera la plantilla HTML del correo de bienvenida
     */
    private function getWelcomeEmailTemplate(
        string $contactName,
        string $clientName,
        string $username,
        string $password,
        string $fullName,
        string $portalUrl
    ): string {
        $appName = htmlspecialchars(Config::$APP_NAME);
        $contactName = htmlspecialchars($contactName);
        $clientName = htmlspecialchars($clientName);
        $username = htmlspecialchars($username);
        $password = htmlspecialchars($password);
        $fullName = htmlspecialchars($fullName);
        $portalUrl = htmlspecialchars($portalUrl);

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a $appName</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 40px 30px; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-align: center;">
                                Bienvenido a $appName
                            </h1>
                            <p style="margin: 10px 0 0; color: #e0e7ff; font-size: 16px; text-align: center;">
                                Portal de Cliente Corporativo
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px; font-size: 16px; color: #374151; line-height: 1.6;">
                                Hola <strong>$contactName</strong>,
                            </p>
                            
                            <p style="margin: 0 0 20px; font-size: 16px; color: #374151; line-height: 1.6;">
                                Nos complace informarte que tu cuenta de acceso al portal de clientes de <strong>$appName</strong> ha sido creada exitosamente para <strong>$clientName</strong>.
                            </p>
                            
                            <p style="margin: 0 0 30px; font-size: 16px; color: #374151; line-height: 1.6;">
                                A través de este portal podrás acceder a métricas, reportes y estadísticas de calidad en tiempo real de tus campañas.
                            </p>
                            
                            <!-- Credentials Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 30px;">
                                        <h2 style="margin: 0 0 20px; color: #111827; font-size: 18px; font-weight: 600;">
                                            📋 Datos de Acceso
                                        </h2>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b7280; font-weight: 500;">
                                                    Nombre de usuario:
                                                </td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #111827; font-weight: 600; text-align: right;">
                                                    $fullName
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b7280; font-weight: 500;">
                                                    Usuario:
                                                </td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #111827; font-weight: 600; text-align: right; font-family: 'Courier New', monospace;">
                                                    $username
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b7280; font-weight: 500;">
                                                    Contraseña:
                                                </td>
                                                <td style="padding: 8px 0; font-size: 14px; color: #111827; font-weight: 600; text-align: right; font-family: 'Courier New', monospace;">
                                                    $password
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="$portalUrl" style="display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);">
                                            🔗 Acceder al Portal
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 15px; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                <strong>Enlace de acceso:</strong><br>
                                <a href="$portalUrl" style="color: #667eea; text-decoration: none; word-break: break-all;">$portalUrl</a>
                            </p>
                            
                            <!-- Security Notice -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; margin-top: 30px;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.5;">
                                            <strong>⚠️ Importante:</strong> Por razones de seguridad, te recomendamos cambiar tu contraseña después del primer acceso.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 30px 0 0; font-size: 14px; color: #6b7280; line-height: 1.6;">
                                Si tienes alguna pregunta o necesitas asistencia, no dudes en contactarnos.
                            </p>
                            
                            <p style="margin: 20px 0 0; font-size: 14px; color: #374151; line-height: 1.6;">
                                Saludos cordiales,<br>
                                <strong>El equipo de $appName</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px 40px; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center; line-height: 1.5;">
                                Este es un correo automático, por favor no responder a este mensaje.<br>
                                © 2026 $appName. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}

