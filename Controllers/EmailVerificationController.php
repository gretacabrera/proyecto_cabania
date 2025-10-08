<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

/**
 * Controlador para verificación de emails
 */
class EmailVerificationController extends Controller
{
    protected $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Verificar token de verificación de email
     */
    public function verify()
    {
        $token = $this->get('token');
        
        if (!$token) {
            $data = [
                'title' => 'Error de Verificación',
                'message' => 'Token de verificación no válido',
                'type' => 'error'
            ];
            
            return $this->render('public/auth/verification_result', $data);
        }
        
        $usuario = $this->usuarioModel->verifyToken($token);
        
        if ($usuario) {
            $nombreCompleto = trim($usuario['persona_nombre'] . ' ' . $usuario['persona_apellido']);
            
            $data = [
                'title' => '¡Email Verificado!',
                'message' => "¡Email verificado exitosamente! Bienvenido/a $nombreCompleto",
                'type' => 'success',
                'usuario' => $usuario
            ];
        } else {
            $data = [
                'title' => 'Error de Verificación',
                'message' => 'Token de verificación inválido o expirado',
                'type' => 'error'
            ];
        }
        
        return $this->render('public/auth/verification_result', $data);
    }

    /**
     * Mostrar página de estado de verificación
     */
    public function status($userId = null)
    {
        // Si no se proporciona ID, usar el usuario actual
        if (!$userId) {
            $userId = $_SESSION['usuario_id'] ?? null;
        }
        
        if (!$userId) {
            $this->redirect('/login', 'Debe iniciar sesión', 'error');
            return;
        }
        
        $usuario = $this->usuarioModel->findWithRelations($userId);
        
        if (!$usuario) {
            return $this->view->error(404);
        }
        
        $isVerified = $this->usuarioModel->isEmailVerified($userId);
        
        $data = [
            'title' => 'Estado de Verificación de Email',
            'usuario' => $usuario,
            'is_verified' => $isVerified,
            'can_resend' => !$isVerified
        ];
        
        return $this->render('public/auth/verification_status', $data);
    }

    /**
     * Reenviar email de verificación
     */
    public function resend()
    {
        if (!$this->isPost()) {
            return $this->view->error(405);
        }
        
        $userId = $this->post('user_id');
        
        if (!$userId) {
            $userId = $_SESSION['usuario_id'] ?? null;
        }
        
        if (!$userId) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no identificado'
            ]);
            return;
        }
        
        // Verificar si el email ya está verificado
        if ($this->usuarioModel->isEmailVerified($userId)) {
            echo json_encode([
                'success' => false,
                'message' => 'El email ya está verificado'
            ]);
            return;
        }
        
        // Obtener datos del usuario
        $userData = $this->usuarioModel->getUserForEmail($userId);
        
        if (!$userData || !$userData['persona_email']) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo obtener la información del usuario'
            ]);
            return;
        }
        
        // Generar nuevo token
        $verificationToken = $this->usuarioModel->generateVerificationToken($userId);
        
        if (!$verificationToken) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar token de verificación'
            ]);
            return;
        }
        
        // Enviar email
        try {
            $emailService = new \App\Core\EmailService();
            $result = $emailService->sendUserVerificationEmail(
                $userData['persona_email'],
                trim($userData['persona_nombre'] . ' ' . $userData['persona_apellido']),
                $userData['usuario_nombre'],
                $verificationToken
            );
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Limpiar tokens expirados (método administrativo)
     */
    public function cleanup()
    {
        if (!$this->hasPermission('usuarios')) {
            return $this->view->error(403);
        }
        
        if ($this->usuarioModel->cleanupExpiredTokens()) {
            $this->redirect('/usuarios', 'Tokens expirados limpiados exitosamente', 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al limpiar tokens expirados', 'error');
        }
    }
}