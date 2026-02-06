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
            error_log("Email Service: Failed to send email to $to. Error: " . $e->getMessage());
            error_log("Email Service: PHPMailer ErrorInfo: {$mail->ErrorInfo}");
            return false;
        } catch (\Exception $e) {
            error_log("Email Service: General exception: " . $e->getMessage());
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
        $contactName = $clientData['contact_name'] ?? '';
        
        // Si el contact_name está vacío o parece un teléfono, usar el nombre del cliente
        if (empty($contactName) || preg_match('/^\d+$/', $contactName)) {
            $contactName = $clientName;
        }
        
        $username = $userData['username'] ?? '';
        $password = $userData['password'] ?? '';
        $fullName = $userData['full_name'] ?? '';
        
        // URL de login del sistema (los clientes inician sesión aquí y son redirigidos a su portal)
        $portalUrl = rtrim(Config::$BASE_URL, '/');

        $subject = "Bienvenido a " . Config::$APP_NAME . " - Portal de Cliente";

        $htmlBody = $this->getWelcomeEmailTemplate(
            $contactName,
            $clientName,
            $username,
            $password,
            $fullName,
            $portalUrl
        );

        $result = $this->send($to, $subject, $htmlBody);
        
        if (!$result) {
            error_log("Email Service: Failed to send welcome email to client: " . $clientName . " (" . $to . ")");
        }
        
        return $result;
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
        
        // URL del logo
        $logoUrl = rtrim(Config::$BASE_URL, '/') . '/logo.png';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a $appName</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f0f4f8;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f4f8; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header con Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%); padding: 50px 40px 40px; text-align: center;">
                            <img src="$logoUrl" alt="$appName" style="max-width: 200px; height: auto; margin-bottom: 25px; display: block; margin-left: auto; margin-right: auto;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                ¡Bienvenido!
                            </h1>
                            <p style="margin: 12px 0 0; color: #dbeafe; font-size: 18px; font-weight: 500;">
                                Portal de Cliente Corporativo
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 45px 40px;">
                            <p style="margin: 0 0 25px; font-size: 17px; color: #1f2937; line-height: 1.7;">
                                Hola <strong style="color: #1e40af;">$contactName</strong>,
                            </p>
                            
                            <p style="margin: 0 0 25px; font-size: 16px; color: #374151; line-height: 1.7;">
                                Nos complace informarte que tu cuenta de acceso al <strong>Portal de Clientes de $appName</strong> ha sido creada exitosamente para <strong style="color: #1e40af;">$clientName</strong>.
                            </p>
                            
                            <p style="margin: 0 0 35px; font-size: 16px; color: #374151; line-height: 1.7;">
                                A través de este portal podrás acceder en tiempo real a:
                            </p>
                            
                            <!-- Features -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 35px;">
                                <tr>
                                    <td style="padding: 0 0 15px 0;">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right: 12px; vertical-align: top;">
                                                    <div style="width: 24px; height: 24px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span style="color: #1e40af; font-size: 16px; font-weight: bold;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="font-size: 15px; color: #374151; line-height: 1.6;">
                                                    Métricas y estadísticas de calidad de tus campañas
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 0 15px 0;">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right: 12px; vertical-align: top;">
                                                    <div style="width: 24px; height: 24px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span style="color: #1e40af; font-size: 16px; font-weight: bold;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="font-size: 15px; color: #374151; line-height: 1.6;">
                                                    Reportes detallados de evaluaciones y desempeño
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 0;">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right: 12px; vertical-align: top;">
                                                    <div style="width: 24px; height: 24px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                        <span style="color: #1e40af; font-size: 16px; font-weight: bold;">✓</span>
                                                    </div>
                                                </td>
                                                <td style="font-size: 15px; color: #374151; line-height: 1.6;">
                                                    Dashboards interactivos con información actualizada
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Credentials Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #3b82f6; border-radius: 12px; margin-bottom: 35px;">
                                <tr>
                                    <td style="padding: 35px 30px;">
                                        <h2 style="margin: 0 0 25px; color: #1e40af; font-size: 20px; font-weight: 700; text-align: center;">
                                            🔐 Tus Credenciales de Acceso
                                        </h2>
                                        
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                            <tr>
                                                <td style="padding: 12px 0; font-size: 15px; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">
                                                    Nombre:
                                                </td>
                                                <td style="padding: 12px 0; font-size: 15px; color: #1f2937; font-weight: 700; text-align: right; border-bottom: 1px solid #e5e7eb;">
                                                    $fullName
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; font-size: 15px; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb;">
                                                    Usuario:
                                                </td>
                                                <td style="padding: 12px 0; font-size: 15px; color: #1e40af; font-weight: 700; text-align: right; font-family: 'Courier New', monospace; border-bottom: 1px solid #e5e7eb;">
                                                    $username
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; font-size: 15px; color: #6b7280; font-weight: 600;">
                                                    Contraseña:
                                                </td>
                                                <td style="padding: 12px 0; font-size: 15px; color: #1e40af; font-weight: 700; text-align: right; font-family: 'Courier New', monospace;">
                                                    $password
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Instrucciones -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-left: 4px solid #3b82f6; border-radius: 6px; margin-bottom: 35px;">
                                <tr>
                                    <td style="padding: 20px 25px;">
                                        <p style="margin: 0 0 12px; font-size: 15px; color: #1f2937; font-weight: 600;">
                                            📝 Cómo acceder:
                                        </p>
                                        <ol style="margin: 0; padding-left: 20px; color: #374151; font-size: 14px; line-height: 1.8;">
                                            <li>Haz clic en el botón "Iniciar Sesión" abajo</li>
                                            <li>Ingresa tu usuario y contraseña</li>
                                            <li>Serás redirigido automáticamente a tu portal personalizado</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="$portalUrl" style="display: inline-block; padding: 16px 50px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 17px; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4); transition: all 0.3s;">
                                            🔗 Iniciar Sesión Ahora
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 10px; font-size: 14px; color: #6b7280; text-align: center; line-height: 1.6;">
                                O copia y pega este enlace en tu navegador:
                            </p>
                            <p style="margin: 0 0 30px; font-size: 13px; text-align: center;">
                                <a href="$portalUrl" style="color: #3b82f6; text-decoration: none; word-break: break-all; font-weight: 500;">$portalUrl</a>
                            </p>
                            
                            <!-- Security Notice -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 6px; margin-top: 35px;">
                                <tr>
                                    <td style="padding: 18px 22px;">
                                        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
                                            <strong>⚠️ Importante:</strong> Por tu seguridad, te recomendamos cambiar tu contraseña después del primer inicio de sesión. Mantén tus credenciales seguras y no las compartas con nadie.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 35px 0 0; font-size: 15px; color: #6b7280; line-height: 1.7; text-align: center;">
                                Si tienes alguna pregunta o necesitas asistencia,<br>
                                no dudes en contactarnos.
                            </p>
                            
                            <p style="margin: 25px 0 0; font-size: 15px; color: #1f2937; line-height: 1.6; text-align: center;">
                                Saludos cordiales,<br>
                                <strong style="color: #1e40af;">El equipo de $appName</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 30px 40px; text-align: center;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #dbeafe; line-height: 1.5;">
                                Este es un correo automático, por favor no responder directamente.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #93c5fd; line-height: 1.5;">
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

