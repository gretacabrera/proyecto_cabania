<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Servicio para el manejo de emails
 */
class EmailService
{
    private $config;
    private $mailer;

    public function __construct()
    {
        // Cargar configuración
        $configFile = __DIR__ . '/config.php';
        
        if (!file_exists($configFile)) {
            throw new \Exception("Archivo de configuración no encontrado: $configFile");
        }
        
        $config = require $configFile;
        
        if (!is_array($config) || !isset($config['mail'])) {
            throw new \Exception("Configuración de mail no encontrada en config.php");
        }
        
        $this->config = $config['mail'];
        
        // Configurar PHPMailer
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configurar SMTP
     */
    private function configureSMTP()
    {
        try {
            // Configuración del servidor
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->Port = $this->config['port'];

            // Configurar encriptación
            if ($this->config['encryption'] === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Configuración adicional
            $this->mailer->setFrom($this->config['username'], $this->config['from_name']);
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log('Error al configurar SMTP: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de verificación de usuario
     */
    public function sendUserVerificationEmail($recipientEmail, $recipientName, $userName, $verificationToken = null)
    {
        try {
            // Limpiar destinatarios previos
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Configurar destinatario
            $this->mailer->addAddress($recipientEmail, $recipientName);
            
            // Configurar asunto
            $this->mailer->Subject = 'Bienvenido a Casa de Palos - Verificación de Usuario';
            
            // Generar token si no se proporciona
            if (!$verificationToken) {
                $verificationToken = $this->generateVerificationToken();
            }
            
            // Contenido del email
            $htmlContent = $this->buildVerificationEmailTemplate($recipientName, $userName, $verificationToken);
            $this->mailer->Body = $htmlContent;
            
            // Contenido alternativo en texto plano
            $textContent = $this->buildVerificationEmailText($recipientName, $userName, $verificationToken);
            $this->mailer->AltBody = $textContent;
            
            // Enviar email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Email de verificación enviado exitosamente a: $recipientEmail");
                return [
                    'success' => true,
                    'message' => 'Email de verificación enviado exitosamente',
                    'token' => $verificationToken
                ];
            }
            
        } catch (Exception $e) {
            error_log('Error al enviar email de verificación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage(),
                'token' => null
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error desconocido al enviar email',
            'token' => null
        ];
    }

    /**
     * Generar token de verificación
     */
    private function generateVerificationToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Construir template HTML del email de verificación
     */
    private function buildVerificationEmailTemplate($recipientName, $userName, $verificationToken)
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost';
        $verificationUrl = $appUrl . '/auth/verify?token=' . $verificationToken;
        
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verificación de Usuario - Casa de Palos</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #2c5530;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    border-radius: 8px 8px 0 0;
                }
                .content {
                    background-color: #f9f9f9;
                    padding: 30px;
                    border-radius: 0 0 8px 8px;
                }
                .welcome-message {
                    font-size: 18px;
                    margin-bottom: 20px;
                }
                .user-info {
                    background-color: #e8f5e8;
                    padding: 15px;
                    border-left: 4px solid #2c5530;
                    margin: 20px 0;
                }
                .verification-section {
                    background-color: white;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    text-align: center;
                }
                .verify-button {
                    display: inline-block;
                    background-color: #2c5530;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    margin: 10px 0;
                }
                .verify-button:hover {
                    background-color: #1e3a22;
                }
                .token-info {
                    font-size: 12px;
                    color: #666;
                    margin-top: 15px;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    font-size: 14px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Casa de Palos - Cabañas</h1>
                <p>Sistema de Reservas y Control de Acceso</p>
            </div>
            
            <div class='content'>
                <div class='welcome-message'>
                    <strong>¡Bienvenido/a, " . htmlspecialchars($recipientName) . "!</strong>
                </div>
                
                <p>Su cuenta de usuario ha sido creada exitosamente en nuestro sistema.</p>
                
                <div class='user-info'>
                    <h3>Información de su cuenta:</h3>
                    <p><strong>Nombre de usuario:</strong> " . htmlspecialchars($userName) . "</p>
                    <p><strong>Nombre real:</strong> " . htmlspecialchars($recipientName) . "</p>
                    <p><strong>Fecha de creación:</strong> " . date('d/m/Y H:i:s') . "</p>
                </div>
                
                <div class='verification-section'>
                    <h3>Verificación de cuenta</h3>
                    <p>Este email confirma que su usuario ha sido registrado correctamente en nuestro sistema.</p>
                    <p>Su cuenta está lista para usar. Ya puede acceder al sistema con sus credenciales.</p>
                    
                    <a href='" . htmlspecialchars($verificationUrl) . "' class='verify-button'>
                        Verificar Email
                    </a>
                    
                    <div class='token-info'>
                        <p>Si el botón no funciona, puede copiar y pegar el siguiente enlace en su navegador:</p>
                        <p style='word-break: break-all;'>" . htmlspecialchars($verificationUrl) . "</p>
                    </div>
                </div>
                
                <p><strong>Importante:</strong></p>
                <ul>
                    <li>Mantenga sus credenciales de acceso seguras</li>
                    <li>No comparta su nombre de usuario y contraseña</li>
                    <li>Si no solicitó esta cuenta, por favor contacte al administrador</li>
                </ul>
                
                <div class='footer'>
                    <p>Este es un email automático, por favor no responda a este mensaje.</p>
                    <p>&copy; " . date('Y') . " Casa de Palos - Cabañas. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Construir contenido en texto plano del email de verificación
     */
    private function buildVerificationEmailText($recipientName, $userName, $verificationToken)
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost';
        $verificationUrl = $appUrl . '/auth/verify?token=' . $verificationToken;
        
        return "
CASA DE PALOS - CABAÑAS
Sistema de Reservas y Control de Acceso

¡Bienvenido/a, $recipientName!

Su cuenta de usuario ha sido creada exitosamente en nuestro sistema.

INFORMACIÓN DE SU CUENTA:
- Nombre de usuario: $userName
- Nombre real: $recipientName
- Fecha de creación: " . date('d/m/Y H:i:s') . "

VERIFICACIÓN DE CUENTA:
Este email confirma que su usuario ha sido registrado correctamente en nuestro sistema.
Su cuenta está lista para usar. Ya puede acceder al sistema con sus credenciales.

Para verificar su email, visite el siguiente enlace:
$verificationUrl

IMPORTANTE:
- Mantenga sus credenciales de acceso seguras
- No comparta su nombre de usuario y contraseña  
- Si no solicitó esta cuenta, por favor contacte al administrador

Este es un email automático, por favor no responda a este mensaje.
© " . date('Y') . " Casa de Palos - Cabañas. Todos los derechos reservados.
        ";
    }

    /**
     * Enviar email genérico
     */
    public function sendEmail($to, $toName, $subject, $htmlBody, $textBody = '')
    {
        try {
            // Limpiar destinatarios previos
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Configurar destinatario
            $this->mailer->addAddress($to, $toName);
            
            // Configurar contenido
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            
            if ($textBody) {
                $this->mailer->AltBody = $textBody;
            }
            
            // Enviar email
            $result = $this->mailer->send();
            
            return [
                'success' => $result,
                'message' => $result ? 'Email enviado exitosamente' : 'Error al enviar email'
            ];
            
        } catch (Exception $e) {
            error_log('Error al enviar email: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar email de recuperación de contraseña
     */
    public function sendPasswordResetEmail($recipientEmail, $recipientName, $userName, $resetToken)
    {
        try {
            // Limpiar destinatarios previos
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Configurar destinatario
            $this->mailer->addAddress($recipientEmail, $recipientName);
            
            // Configurar asunto
            $this->mailer->Subject = 'Casa de Palos - Recuperación de Contraseña';
            
            // Contenido del email
            $htmlContent = $this->buildPasswordResetEmailTemplate($recipientName, $userName, $resetToken);
            $this->mailer->Body = $htmlContent;
            
            // Contenido alternativo en texto plano
            $textContent = $this->buildPasswordResetEmailText($recipientName, $userName, $resetToken);
            $this->mailer->AltBody = $textContent;
            
            // Enviar email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Email de recuperación de contraseña enviado exitosamente a: $recipientEmail");
                return [
                    'success' => true,
                    'message' => 'Email de recuperación enviado exitosamente'
                ];
            }
            
        } catch (Exception $e) {
            error_log('Error al enviar email de recuperación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error desconocido al enviar email'
        ];
    }

    /**
     * Construir template HTML del email de recuperación de contraseña
     */
    private function buildPasswordResetEmailTemplate($recipientName, $userName, $resetToken)
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost';
        $resetUrl = $appUrl . '/auth/reset-password?token=' . $resetToken;
        
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperación de Contraseña - Casa de Palos</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #2c5530;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    border-radius: 8px 8px 0 0;
                }
                .content {
                    background-color: #f9f9f9;
                    padding: 30px;
                    border-radius: 0 0 8px 8px;
                }
                .alert {
                    background-color: #fff3cd;
                    color: #856404;
                    padding: 15px;
                    border-left: 4px solid #ffc107;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .user-info {
                    background-color: #e8f5e8;
                    padding: 15px;
                    border-left: 4px solid #2c5530;
                    margin: 20px 0;
                }
                .reset-section {
                    background-color: white;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    text-align: center;
                }
                .reset-button {
                    display: inline-block;
                    background-color: #dc3545;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    margin: 10px 0;
                }
                .reset-button:hover {
                    background-color: #c82333;
                }
                .expiry-info {
                    font-size: 12px;
                    color: #666;
                    margin-top: 15px;
                }
                .security-notice {
                    background-color: #f8d7da;
                    color: #721c24;
                    padding: 15px;
                    border-left: 4px solid #dc3545;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    font-size: 14px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Casa de Palos - Cabañas</h1>
                <p>Recuperación de Contraseña</p>
            </div>
            
            <div class='content'>
                <div class='alert'>
                    <strong>⚠️ Solicitud de recuperación de contraseña</strong>
                </div>
                
                <p>Hola <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                
                <p>Hemos recibido una solicitud para restablecer la contraseña de su cuenta en nuestro sistema.</p>
                
                <div class='user-info'>
                    <h3>Datos de la cuenta:</h3>
                    <p><strong>Nombre de usuario:</strong> " . htmlspecialchars($userName) . "</p>
                    <p><strong>Nombre real:</strong> " . htmlspecialchars($recipientName) . "</p>
                    <p><strong>Fecha de solicitud:</strong> " . date('d/m/Y H:i:s') . "</p>
                </div>
                
                <div class='reset-section'>
                    <h3>Restablecer Contraseña</h3>
                    <p>Si usted solicitó este cambio, haga clic en el siguiente botón para crear una nueva contraseña:</p>
                    
                    <a href='" . htmlspecialchars($resetUrl) . "' class='reset-button'>
                        Restablecer Contraseña
                    </a>
                    
                    <div class='expiry-info'>
                        <p><strong>⏰ Este enlace expirará en 1 hora</strong></p>
                        <p>Si el botón no funciona, copie y pegue el siguiente enlace en su navegador:</p>
                        <p style='word-break: break-all; font-size: 11px;'>" . htmlspecialchars($resetUrl) . "</p>
                    </div>
                </div>
                
                <div class='security-notice'>
                    <h4>🔒 Aviso de Seguridad</h4>
                    <p><strong>Si no solicitó este cambio:</strong></p>
                    <ul>
                        <li>Ignore este email - su contraseña no se modificará</li>
                        <li>Considere cambiar su contraseña desde su cuenta</li>
                        <li>Contacte al administrador si sospecha actividad sospechosa</li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>Este es un email automático, por favor no responda a este mensaje.</p>
                    <p>Si tiene problemas, contacte al administrador del sistema.</p>
                    <p>&copy; " . date('Y') . " Casa de Palos - Cabañas. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Construir contenido en texto plano del email de recuperación
     */
    private function buildPasswordResetEmailText($recipientName, $userName, $resetToken)
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost';
        $resetUrl = $appUrl . '/auth/reset-password?token=' . $resetToken;
        
        return "
CASA DE PALOS - CABAÑAS
Recuperación de Contraseña

⚠️ SOLICITUD DE RECUPERACIÓN DE CONTRASEÑA

Hola $recipientName,

Hemos recibido una solicitud para restablecer la contraseña de su cuenta en nuestro sistema.

DATOS DE LA CUENTA:
- Nombre de usuario: $userName
- Nombre real: $recipientName
- Fecha de solicitud: " . date('d/m/Y H:i:s') . "

RESTABLECER CONTRASEÑA:
Si usted solicitó este cambio, visite el siguiente enlace para crear una nueva contraseña:

$resetUrl

⏰ IMPORTANTE: Este enlace expirará en 1 hora

🔒 AVISO DE SEGURIDAD:
Si no solicitó este cambio:
- Ignore este email - su contraseña no se modificará
- Considere cambiar su contraseña desde su cuenta
- Contacte al administrador si sospecha actividad sospechosa

Este es un email automático, por favor no responda a este mensaje.
Si tiene problemas, contacte al administrador del sistema.

© " . date('Y') . " Casa de Palos - Cabañas. Todos los derechos reservados.
        ";
    }

    /**
     * Verificar configuración de email
     */
    public function testEmailConfiguration()
    {
        try {
            // Verificar que las credenciales estén configuradas
            if (empty($this->config['username']) || empty($this->config['password'])) {
                return [
                    'success' => false,
                    'message' => 'Las credenciales de email no están configuradas'
                ];
            }
            
            // Probar conexión SMTP
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            
            return [
                'success' => true,
                'message' => 'Configuración de email válida'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en la configuración: ' . $e->getMessage()
            ];
        }
    }
}