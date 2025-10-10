<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Usuario;
use App\Models\Persona;

/**
 * Controlador para autenticación de usuarios
 */
class AuthController extends Controller
{
    protected $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Mostrar formulario de login
     */
    public function login()
    {
        // Si ya está autenticado, redirigir al inicio
        if (Auth::check()) {
            $this->redirect('/');
        }

        if ($this->isPost()) {
            return $this->processLogin();
        }

        $data = [
            'title' => 'Iniciar Sesión'
        ];

        return $this->render('public/auth/login', $data, 'auth');
    }

    /**
     * Procesar login
     */
    private function processLogin()
    {
        $username = $this->post('usuario_nombre');
        $password = $this->post('usuario_contrasenia');

        if (empty($username) || empty($password)) {
            $this->redirect('/auth/login', 'Por favor complete todos los campos', 'error');
            return;
        }

        try {
            $usuario = $this->usuarioModel->authenticate($username, $password);

            if ($usuario) {
                Auth::login($usuario['usuario_nombre'], $usuario['id_usuario']);
                
                // Verificar si hay una reserva pendiente después del login exitoso
                if (isset($_SESSION['pending_reservation'])) {
                    $pendingReservation = $_SESSION['pending_reservation'];
                    unset($_SESSION['pending_reservation']);
                    
                    $redirectUrl = '/reservas/confirmar?' . http_build_query([
                        'cabania_id' => $pendingReservation['cabania_id'],
                        'fecha_inicio' => $pendingReservation['fecha_inicio'],
                        'fecha_fin' => $pendingReservation['fecha_fin']
                    ]);
                    
                    $this->redirect($redirectUrl, 'Bienvenido ' . $usuario['usuario_nombre'] . '. Completa tu reserva.', 'exito');
                } else {
                    $this->redirect('/', 'Bienvenido ' . $usuario['usuario_nombre'], 'exito');
                }
            } else {
                $this->redirect('/auth/login', 'Credenciales incorrectas', 'error');
                return;
            }
        } catch (\Exception $e) {
            // Capturar mensaje de verificación pendiente
            $this->redirect('/auth/login', $e->getMessage(), 'aviso');
            return;
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        Auth::logout();
        $this->redirect('/auth/login', 'Sesión cerrada correctamente', 'info');
    }

    /**
     * Mostrar formulario de registro (solo para desarrollo)
     */
    public function register()
    {
        // DEBUG: Verificar si llegamos aquí
        error_log("AuthController::register() - Método: " . $_SERVER['REQUEST_METHOD']);
        
        if ($this->isPost()) {
            error_log("AuthController::register() - Procesando POST");
            return $this->processRegister();
        }

        error_log("AuthController::register() - Mostrando formulario");
        $data = [
            'title' => 'Registro de Usuario',
            'pageTitle' => 'Crear Nueva Cuenta'
        ];

        return $this->render('public/auth/register', $data, 'auth');
    }

    /**
     * Procesar registro
     */
    private function processRegister()
    {
        error_log("AuthController::processRegister() - Iniciando procesamiento");
        
        $data = $_POST;
        
        // Mapear datos al formato esperado por el modelo
        $mappedData = [
            'usuario_nombre' => $this->post('usuario_nombre'),
            'usuario_contrasenia' => $this->post('usuario_contrasenia'),
            'confirmar_contrasenia' => $this->post('confirmar_contrasenia'),
            'rela_perfil' => 3, // Perfil huésped por defecto
            'persona_nombres' => $this->post('nombre'),
            'persona_apellidos' => $this->post('apellido'),
            'persona_telefono' => $this->post('telefono', ''),
            'persona_email' => $this->post('email'),
            'persona_fecha_nacimiento' => $this->post('fecha_nacimiento'),
            'persona_direccion' => $this->post('direccion'),
            'persona_instagram' => $this->post('contacto_instagram'),
            'persona_facebook' => $this->post('contacto_facebook'),
            'acepta_terminos' => isset($_POST['accept_all'])  // Cambiar acepta_terminos -> accept_all
        ];

        error_log("AuthController::processRegister() - Datos mapeados: " . json_encode($mappedData, JSON_UNESCAPED_UNICODE));

        // Validar usando el modelo centralizado
        $errors = $this->usuarioModel->validateUserData($mappedData);

        // Validación de edad mínima
        if (!empty($mappedData['persona_fecha_nacimiento'])) {
            $fechaNac = new \DateTime($mappedData['persona_fecha_nacimiento']);
            $hoy = new \DateTime();
            $edad = $hoy->diff($fechaNac)->y;
            
            if ($edad < 18) {
                $errors[] = 'Debe ser mayor de 18 años para registrarse';
            }
        }

        // Verificar campos requeridos de persona
        if (empty($mappedData['persona_nombres'])) {
            $errors[] = 'Los nombres son obligatorios';
        }

        if (empty($mappedData['persona_apellidos'])) {
            $errors[] = 'Los apellidos son obligatorios';
        }

        if (empty($mappedData['persona_email'])) {
            $errors[] = 'El email es obligatorio';
        }

        if (empty($mappedData['persona_fecha_nacimiento'])) {
            $errors[] = 'La fecha de nacimiento es obligatoria';
        }

        if (empty($mappedData['persona_direccion'])) {
            $errors[] = 'La dirección es obligatoria';
        }

        if (!$mappedData['acepta_terminos']) {
            $errors[] = 'Debe aceptar los términos y condiciones';
        }

        if (!empty($errors)) {
            error_log("AuthController::processRegister() - Errores de validación: " . implode(', ', $errors));
            $this->redirect('/auth/register', implode('. ', $errors), 'error');
            return;
        }

        // Verificar email duplicado usando la tabla contacto
        $personaModel = new Persona();
        if ($personaModel->emailExists($mappedData['persona_email'])) {
            error_log("AuthController::processRegister() - Error: email existe");
            $this->redirect('/auth/register', 'El email ya está registrado', 'error');
            return;
        }

        try {
            // Usar el método del modelo para crear usuario completo
            $userId = $this->usuarioModel->createUsuarioCompleto($mappedData);

            if ($userId) {
                error_log("AuthController::processRegister() - Registro completo exitoso con ID: $userId");
                
                // Generar token de verificación
                $verificationToken = $this->usuarioModel->generateVerificationToken($userId);
                
                if ($verificationToken) {
                    // Obtener datos del usuario para el email
                    $userData = $this->usuarioModel->getUserForEmail($userId);
                    
                    error_log("AuthController::processRegister() - Datos de usuario: " . json_encode($userData, JSON_UNESCAPED_UNICODE));
                    
                    if ($userData && !empty($userData['persona_email'])) {
                        // Enviar email de verificación
                        try {
                            $emailService = new \App\Core\EmailService();
                            $nombreCompleto = trim($userData['persona_nombre'] . ' ' . $userData['persona_apellido']);
                            
                            $emailResult = $emailService->sendUserVerificationEmail(
                                $userData['persona_email'],
                                $nombreCompleto,
                                $userData['usuario_nombre'],
                                $verificationToken
                            );
                            
                            if ($emailResult['success']) {
                                error_log("AuthController::processRegister() - Email de verificación enviado exitosamente");
                                $this->redirect('/auth/login', 'Usuario registrado correctamente. Se ha enviado un email de verificación a su correo. Por favor verifique su email antes de iniciar sesión.', 'exito');
                            } else {
                                error_log("AuthController::processRegister() - Error al enviar email: " . $emailResult['message']);
                                $this->redirect('/auth/login', 'Usuario registrado correctamente, pero hubo un problema al enviar el email de verificación. Contacte al administrador.', 'aviso');
                            }
                        } catch (\Exception $e) {
                            error_log("AuthController::processRegister() - Excepción al enviar email: " . $e->getMessage());
                            $this->redirect('/auth/login', 'Usuario registrado correctamente, pero hubo un problema al enviar el email de verificación. Contacte al administrador.', 'aviso');
                        }
                    } else {
                        error_log("AuthController::processRegister() - No se pudo obtener email del usuario");
                        $this->redirect('/auth/login', 'Usuario registrado correctamente, pero no se pudo enviar el email de verificación. Contacte al administrador.', 'aviso');
                    }
                } else {
                    error_log("AuthController::processRegister() - Error al generar token de verificación");
                    $this->redirect('/auth/login', 'Usuario registrado correctamente, pero no se pudo generar el token de verificación. Contacte al administrador.', 'aviso');
                }
            } else {
                error_log("AuthController::processRegister() - Error: no se pudo completar el registro");
                $this->redirect('/auth/register', 'Error al crear el usuario', 'error');
            }
        } catch (\Exception $e) {
            error_log("AuthController::processRegister() - Excepción: " . $e->getMessage());
            $this->redirect('/auth/register', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword()
    {
        $this->requireAuth();

        if ($this->isPost()) {
            $currentPassword = $this->post('current_password');
            $newPassword = $this->post('new_password');
            $confirmPassword = $this->post('confirm_password');

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->redirect('/auth/change-password', 'Complete todos los campos', 'error');
            }

            if ($newPassword !== $confirmPassword) {
                $this->redirect('/auth/change-password', 'Las contraseñas nuevas no coinciden', 'error');
            }

            $userId = Auth::id();
            $usuario = $this->usuarioModel->find($userId);

            if (!$usuario) {
                $this->redirect('/auth/change-password', 'Usuario no encontrado', 'error');
                return;
            }

            if (!password_verify($currentPassword, $usuario['usuario_contrasenia'])) {
                $this->redirect('/auth/change-password', 'La contraseña actual es incorrecta', 'error');
                return;
            }

            $result = $this->usuarioModel->updatePassword($userId, $newPassword);

            if ($result) {
                error_log("AuthController::changePassword() - Contraseña actualizada exitosamente para usuario: " . $usuario['usuario_nombre']);
                
                // Por seguridad, cerrar sesión después de cambiar contraseña
                Auth::logout();
                
                // Redireccionar al login con mensaje de éxito
                $this->redirect('/auth/login', 'Contraseña cambiada exitosamente. Por seguridad, debe iniciar sesión nuevamente.', 'exito');
            } else {
                $this->redirect('/auth/change-password', 'Error al actualizar la contraseña', 'error');
            }
        }

        // Obtener datos del usuario actual
        $userId = Auth::id();
        $usuario = $this->usuarioModel->find($userId);

        $data = [
            'title' => 'Cambiar Contraseña - Casa de Palos',
            'pageTitle' => 'Cambiar Contraseña',
            'usuario' => $usuario
        ];

        return $this->render('public/auth/change-password', $data, 'auth');
    }

    /**
     * ============================
     * RECUPERACIÓN DE CONTRASEÑA
     * ============================
     */

    /**
     * Mostrar formulario para solicitar recuperación de contraseña
     */
    public function forgotPassword()
    {
        // Si ya está autenticado, redirigir al inicio
        if (Auth::check()) {
            $this->redirect('/');
        }

        if ($this->isPost()) {
            return $this->processForgotPassword();
        }

        $data = [
            'title' => 'Recuperar Contraseña - Casa de Palos',
            'pageTitle' => 'Recuperar Contraseña'
        ];

        return $this->render('public/auth/forgot_password', $data, 'auth');
    }

    /**
     * Procesar solicitud de recuperación de contraseña
     */
    private function processForgotPassword()
    {
        $email = trim($this->post('email'));

        if (empty($email)) {
            $this->redirect('/auth/forgot-password', 'Por favor ingrese su email', 'error');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/auth/forgot-password', 'Por favor ingrese un email válido', 'error');
            return;
        }

        try {
            // Buscar usuario por email
            $usuario = $this->usuarioModel->findUserByEmail($email);
            
            if (!$usuario) {
                // Por seguridad, no revelar si el email existe o no
                $this->redirect('/auth/login', 'Si el email está registrado en nuestro sistema, recibirá un mensaje con instrucciones para recuperar su contraseña.', 'info');
                return;
            }

            // Generar token de recuperación
            $tokenData = $this->usuarioModel->generatePasswordResetToken($email);
            
            if (!$tokenData) {
                error_log("AuthController::processForgotPassword() - Error al generar token para: $email");
                $this->redirect('/auth/forgot-password', 'Error interno. Por favor intente más tarde.', 'error');
                return;
            }

            // Enviar email de recuperación
            try {
                $emailService = new \App\Core\EmailService();
                $nombreCompleto = trim($usuario['persona_nombre'] . ' ' . $usuario['persona_apellido']);
                
                $emailResult = $emailService->sendPasswordResetEmail(
                    $email,
                    $nombreCompleto,
                    $usuario['usuario_nombre'],
                    $tokenData['token']
                );
                
                if ($emailResult['success']) {
                    error_log("AuthController::processForgotPassword() - Email de recuperación enviado exitosamente a: $email");
                    $this->redirect('/auth/login', 'Se ha enviado un email con instrucciones para recuperar su contraseña. Revise su bandeja de entrada.', 'exito');
                } else {
                    error_log("AuthController::processForgotPassword() - Error al enviar email: " . $emailResult['message']);
                    $this->redirect('/auth/forgot-password', 'Error al enviar email. Por favor intente más tarde.', 'error');
                }
            } catch (\Exception $e) {
                error_log("AuthController::processForgotPassword() - Excepción al enviar email: " . $e->getMessage());
                $this->redirect('/auth/forgot-password', 'Error al enviar email. Por favor intente más tarde.', 'error');
            }

        } catch (\Exception $e) {
            error_log("AuthController::processForgotPassword() - Excepción: " . $e->getMessage());
            $this->redirect('/auth/forgot-password', 'Error interno. Por favor intente más tarde.', 'error');
        }
    }

    /**
     * Mostrar formulario para restablecer contraseña
     */
    public function resetPassword()
    {
        $token = $this->get('token');
        
        if (!$token) {
            $this->redirect('/auth/login', 'Token de recuperación no válido', 'error');
            return;
        }

        // Verificar token
        $usuario = $this->usuarioModel->verifyPasswordResetToken($token);
        
        if (!$usuario) {
            $this->redirect('/auth/login', 'El enlace de recuperación ha expirado o no es válido', 'error');
            return;
        }

        if ($this->isPost()) {
            return $this->processResetPassword($token);
        }

        $data = [
            'title' => 'Restablecer Contraseña - Casa de Palos',
            'pageTitle' => 'Restablecer Contraseña',
            'token' => $token,
            'usuario_nombre' => $usuario['usuario_nombre']
        ];

        return $this->render('public/auth/reset_password', $data, 'auth');
    }

    /**
     * Procesar restablecimiento de contraseña
     */
    private function processResetPassword($token)
    {
        // Intentar obtener el token del POST si no viene del GET
        $tokenFromPost = $this->post('token');
        if (empty($token) && !empty($tokenFromPost)) {
            $token = $tokenFromPost;
        }
        
        if (empty($token)) {
            $this->redirect('/auth/login', 'Token de recuperación no válido', 'error');
            return;
        }
        
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        if (empty($newPassword) || empty($confirmPassword)) {
            $this->redirect("/auth/reset-password?token=$token", 'Complete todos los campos', 'error');
            return;
        }

        if (strlen($newPassword) < 6) {
            $this->redirect("/auth/reset-password?token=$token", 'La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirect("/auth/reset-password?token=$token", 'Las contraseñas no coinciden', 'error');
            return;
        }

        try {
            // Restablecer contraseña
            $usuario = $this->usuarioModel->resetPasswordWithToken($token, $newPassword);
            
            if ($usuario) {
                error_log("AuthController::processResetPassword() - Contraseña restablecida exitosamente para: " . $usuario['usuario_nombre']);
                $this->redirect('/auth/login', 'Contraseña restablecida exitosamente. Ya puede iniciar sesión con su nueva contraseña.', 'exito');
            } else {
                error_log("AuthController::processResetPassword() - Error al restablecer contraseña");
                $this->redirect("/auth/reset-password?token=$token", 'Error al restablecer contraseña. Intente nuevamente.', 'error');
            }
        } catch (\Exception $e) {
            error_log("AuthController::processResetPassword() - Excepción: " . $e->getMessage());
            $this->redirect("/auth/reset-password?token=$token", 'Error interno. Por favor intente más tarde.', 'error');
        }
    }

    /**
     * Crear un contacto en la tabla contacto (método legacy - mantener para compatibilidad)
     */
    private function createContacto($personaId, $tipoDescripcion, $valor)
    {
        if (empty($valor)) {
            return true; // No crear contacto si el valor está vacío
        }

        $db = \App\Core\Database::getInstance();
        
        // Obtener el ID del tipo de contacto
        $stmt = $db->prepare("SELECT id_tipocontacto FROM tipocontacto WHERE tipocontacto_descripcion = ?");
        $stmt->bind_param("s", $tipoDescripcion);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("AuthController::createContacto() - Tipo de contacto '$tipoDescripcion' no encontrado");
            return false;
        }
        
        $tipoContactoId = $result->fetch_assoc()['id_tipocontacto'];
        
        // Crear el contacto
        $stmt = $db->prepare("INSERT INTO contacto (contacto_descripcion, rela_persona, rela_tipocontacto, contacto_estado) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sii", $valor, $personaId, $tipoContactoId);
        
        if ($stmt->execute()) {
            error_log("AuthController::createContacto() - Contacto $tipoDescripcion creado: $valor");
            return true;
        } else {
            error_log("AuthController::createContacto() - Error al crear contacto $tipoDescripcion: " . $stmt->error);
            return false;
        }
    }


}