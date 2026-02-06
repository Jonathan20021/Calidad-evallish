# Configuración de Envío de Correos para Clientes Corporativos (PHPMailer)

## Descripción

El sistema ahora envía automáticamente un correo de bienvenida con las credenciales de acceso al portal cuando se crea un nuevo cliente corporativo. También incluye un botón para reenviar manualmente las credenciales desde la lista de clientes.

**📧 Usa PHPMailer**: Sistema profesional y robusto para envío de correos con soporte completo para SMTP.

## Instalación de Dependencias

Primero, instala PHPMailer usando Composer:

```bash
composer install
```

O si ya tienes vendor instalado:

```bash
composer update
```

Esto instalará PHPMailer 6.9+ automáticamente.

## Configuración del Servidor de Correo

El sistema usa **PHPMailer** con SMTP. Necesitas configurar las variables de entorno en tu archivo `.env`:

### Configuración Básica (`.env`)

Crea o edita el archivo `.env` en la raíz del proyecto y agrega:

```env
# Configuración de correo - SMTP (Servidor cPanel Evallish BPO)
MAIL_HOST=mail.evallishbpo.com
MAIL_PORT=465
MAIL_USERNAME=notificaciones@evallishbpo.com
MAIL_PASSWORD=Admin#2025#
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notificaciones@evallishbpo.com
MAIL_FROM_NAME="Evallish BPO Control - Sistema de QA"

# Debug de correo (opcional, solo para desarrollo)
MAIL_DEBUG=false
```

**Nota:** La aplicación está configurada para usar `https://qa.evallishbpo.com/` como URL base, por lo que el enlace del portal de clientes en el correo será: `https://qa.evallishbpo.com/client-portal`

### Opciones de Configuración SMTP

#### Opción 1: cPanel / Servidor Propio (Configuración Actual)

```env
MAIL_HOST=mail.evallishbpo.com
MAIL_PORT=465
MAIL_USERNAME=notificaciones@evallishbpo.com
MAIL_PASSWORD=Admin#2025#
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notificaciones@evallishbpo.com
MAIL_FROM_NAME="Evallish BPO Control - Sistema de QA"
```

**Ventajas:**
- ✅ Control total sobre el servidor de correo
- ✅ No hay límites de envío estrictos
- ✅ Dominio propio para mejor reputación
- ✅ Sin necesidad de configuraciones externas

**Notas importantes:**
- Usa puerto **465** con **SSL** (más seguro)
- Si tienes problemas, prueba puerto **587** con **TLS**
- Asegúrate que el firewall permita conexiones salientes al puerto 465

#### Opción 2: Gmail (Alternativa para pruebas)

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tucorreo@gmail.com
MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx  # Contraseña de aplicación
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tucorreo@gmail.com
MAIL_FROM_NAME="Evallish BPO"
```

**Nota importante para Gmail:**
1. Ve a tu cuenta de Google → Seguridad
2. Activa "Verificación en 2 pasos"
3. Genera una "Contraseña de aplicación" específica para este sistema
4. Usa esa contraseña de 16 caracteres en `MAIL_PASSWORD`
5. Gmail tiene límite de 500 correos por día

#### Opción 3: SendGrid (Para alto volumen)

```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu_api_key_de_sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@evallishbpo.com
MAIL_FROM_NAME="Evallish BPO Control - Sistema de QA"
```

#### Opción 4: Mailgun

```env
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@tudominio.mailgun.org
MAIL_PASSWORD=tu_password_mailgun
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@evallishbpo.com
MAIL_FROM_NAME="Evallish BPO Control - Sistema de QA"
```

#### Opción 5: Amazon SES

```env
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=tu_access_key
MAIL_PASSWORD=tu_secret_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@evallishbpo.com
MAIL_FROM_NAME="Evallish BPO Control - Sistema de QA"
```

### Parámetros de Configuración

- **MAIL_HOST**: Servidor SMTP
- **MAIL_PORT**: Puerto SMTP (587 para TLS, 465 para SSL)
- **MAIL_USERNAME**: Usuario del servidor SMTP
- **MAIL_PASSWORD**: Contraseña o API key
- **MAIL_ENCRYPTION**: `tls` (recomendado) o `ssl`
- **MAIL_FROM_ADDRESS**: Email del remitente
- **MAIL_FROM_NAME**: Nombre visible del remitente
- **MAIL_DEBUG**: `true` para ver debug completo (solo desarrollo)

## Funcionalidades Implementadas

### 1. Envío Automático al Crear Cliente

Cuando creas un nuevo cliente corporativo desde `/clients/create`, el sistema:
- ✅ Crea el cliente en la base de datos
- ✅ Crea el usuario del portal con sus credenciales
- ✅ Envía automáticamente un correo al `contact_email` del cliente con:
  - Enlace de acceso al portal (`/clients/portal`)
  - Usuario
  - Contraseña en texto plano
  - Nombre completo del usuario
  - Instrucciones de acceso

### 2. Botón de Envío Manual

En la lista de clientes (`/clients`), cada cliente con correo configurado tiene un botón:
- 📧 **Enviar credenciales**: Reenvía el correo con las credenciales
- El botón solo aparece si:
  - El cliente tiene un `contact_email` configurado
  - El cliente tiene un usuario de portal asignado

**Nota:** Para clientes existentes, la contraseña se muestra como `**********` porque está hasheada en la base de datos. Se recomienda resetear la contraseña si es necesario enviar credenciales nuevamente.

### 3. Mensajes de Confirmación

- ✅ **Éxito**: "Credenciales enviadas exitosamente a [email]"
- ❌ **Error**: Mensajes específicos si falta email, usuario o hay problemas con el servidor

## Plantilla del Correo

El correo incluye:
- 📨 Diseño profesional con gradiente morado
- 📋 Tabla clara con los datos de acceso
- 🔗 Botón para acceder directamente al portal
- ⚠️ Aviso de seguridad para cambiar la contraseña

## Pruebas

### Instalación de dependencias:

```bash
cd c:\xampp\htdocs\Calidad-evallish
composer install
```

### Para probar el envío de correo:

1. **Crear un nuevo cliente**:
   - Ve a `/clients/create`
   - Llena todos los campos, especialmente `Email de contacto`
   - Al guardar, se enviará el correo automáticamente

2. **Enviar manualmente**:
   - Ve a `/clients`
   - Haz clic en "📧 Enviar credenciales" en cualquier cliente
   - Confirma el envío
   - Verifica el mensaje de éxito/error

3. **Revisar logs**:
   - Los errores de envío se registran en el log de PHP
   - En XAMPP: `C:\xampp\php\logs\php_error_log`
   - En Linux: `/var/log/php_errors.log` o similar

## Solución de Problemas

### El correo no se envía

1. **Verifica que PHPMailer esté instalado**:
```bash
composer show phpmailer/phpmailer
```

2. **Activa el modo debug** en `.env`:
```env
MAIL_DEBUG=true
```

3. **Revisa los logs de PHP** para ver errores específicos de PHPMailer

4. **Prueba la conexión SMTP** creando un archivo de prueba `test_smtp.php`:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tucorreo@gmail.com';
    $mail->Password = 'tu_app_password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->SMTPDebug = 2; // Debug completo
    
    $mail->setFrom('tucorreo@gmail.com', 'Test');
    $mail->addAddress('destino@email.com');
    $mail->Subject = 'Prueba SMTP';
    $mail->Body = 'Test exitoso';
    
    $mail->send();
    echo 'Correo enviado exitosamente!';
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
```

### El correo llega a SPAM

- Configura registros SPF/DKIM en tu dominio
- Usa un servidor SMTP legítimo (Gmail, SendGrid, etc.)
- Agrega un encabezado `Reply-To` válido

### Errores comunes

#### "SMTP connect() failed"
- Verifica MAIL_HOST, MAIL_PORT y credenciales
- Asegúrate que tu firewall permita conexiones SMTP salientes
- Verifica que el puerto no esté bloqueado por tu ISP

#### "SMTP Error: Could not authenticate"
- Verifica MAIL_USERNAME y MAIL_PASSWORD
- Para Gmail: usa contraseña de aplicación, no tu contraseña normal
- Para SendGrid: usa "apikey" como username

#### "Invalid address"
- Verifica que el email del cliente sea válido
- Revisa que MAIL_FROM_ADDRESS esté bien configurado

#### Certificado SSL/TLS
Si tienes problemas con certificados SSL:
```php
// Solo para desarrollo, no usar en producción
$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
];
```

### Configuración en producción

Para producción, recomendamos:
1. **Usar un servicio dedicado**: SendGrid, Mailgun, Amazon SES
2. **No usar Gmail**: tiene límites de envío (500/día)
3. **Configurar SPF/DKIM**: en tu dominio para evitar spam
4. **Usar HTTPS**: para proteger las credenciales
5. **Mantener logs**: para auditoría de correos enviados

## Archivos Modificados/Creados

- ✨ **Nuevo**: `src/Services/EmailService.php` - Servicio de envío de correos con PHPMailer
- ✏️ **Modificado**: `composer.json` - Agregada dependencia phpmailer/phpmailer ^6.9
- ✏️ **Modificado**: `src/Controllers/CorporateClientController.php` - Agregado método `sendCredentials()` y envío automático en `store()`
- ✏️ **Modificado**: `src/Views/clients/index.php` - Agregado botón y mensajes de éxito/error
- ✏️ **Modificado**: `public/index.php` - Agregada ruta `/clients/send-credentials`

## Ventajas de PHPMailer

✅ **Autenticación SMTP robusta**: Soporte completo para SMTP con autenticación
✅ **Manejo de errores**: Excepciones detalladas para debug
✅ **Seguridad**: Soporte para TLS/SSL
✅ **Adjuntos**: Fácil envío de archivos adjuntos (para futuras mejoras)
✅ **HTML y texto**: Soporte automático para ambos formatos
✅ **Multiplataforma**: Funciona igual en Windows, Linux, Mac
✅ **Sin configuración del servidor**: No requiere configurar php.ini o sendmail

## Mejoras Futuras (Opcional)

Si deseas mejorar aún más el sistema de correos, considera:

1. **Cola de correos**: Para no bloquear la respuesta al usuario
   - Usar Redis o base de datos para encolar
   - Procesar correos en segundo plano

2. **Plantillas con motor de templates**: Blade, Twig, etc.
   - Plantillas más mantenibles
   - Reutilización de componentes

3. **Tracking de correos**: Saber si se abrió el correo
   - Pixel de seguimiento
   - Enlaces con tracking

4. **Múltiples destinatarios**: CC, BCC
5. **Adjuntos**: PDFs, documentos
6. **Notificaciones adicionales**: 
   - Reseteo de contraseña
   - Alertas de calidad
   - Reportes automáticos

## Ejemplo de uso avanzado

Para enviar correos con adjuntos (futuro):

```php
$emailService = new EmailService();
$mail = $emailService->createMailer(); // Método a agregar

$mail->addAttachment('/path/to/file.pdf', 'Reporte.pdf');
$mail->send($to, $subject, $htmlBody);
```

## Recursos

- **PHPMailer Docs**: https://github.com/PHPMailer/PHPMailer
- **Gmail App Passwords**: https://support.google.com/accounts/answer/185833
- **SendGrid**: https://sendgrid.com/
- **Mailgun**: https://www.mailgun.com/
- **Amazon SES**: https://aws.amazon.com/ses/

