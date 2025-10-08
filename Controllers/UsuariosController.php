<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\EmailService;
use App\Models\Usuario;

/**
 * Controlador para la gestión de usuarios
 */
class UsuariosController extends Controller
{
    protected $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        if (!$this->hasPermission('usuarios')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');

        if ($search) {
            $usuarios = $this->usuarioModel->search($search, $page);
        } else {
            $usuarios = $this->usuarioModel->paginate($page);
        }

        $data = [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'currentPage' => $page,
            'search' => $search,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo usuario
     */
    public function create()
    {
        if (!$this->hasPermission('usuarios')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'usuario_nombre' => $this->post('usuario_nombre'),
                'usuario_contrasenia' => $this->post('usuario_contrasenia'),
                'confirmar_contrasenia' => $this->post('confirmar_contrasenia'),
                'rela_perfil' => $this->post('rela_perfil'),
                'rela_persona' => $this->post('rela_persona'),
                'usuario_estado' => 1
            ];

            // Validar usando el método centralizado
            $errors = $this->usuarioModel->validateUserData($data);

            if (!empty($errors)) {
                $this->redirect('/admin/seguridad/usuarios/create', implode('. ', $errors), 'error');
                return;
            }

            // Preparar datos para inserción
            // Estado 2 = Pendiente de verificación de email
            $insertData = [
                'usuario_nombre' => $data['usuario_nombre'],
                'usuario_contrasenia' => password_hash($data['usuario_contrasenia'], PASSWORD_DEFAULT),
                'rela_perfil' => $data['rela_perfil'],
                'rela_persona' => $data['rela_persona'],
                'usuario_estado' => 2
            ];

            $userId = $this->usuarioModel->create($insertData);
            
            if ($userId) {
                // Enviar email de verificación
                $this->sendVerificationEmail($userId);
                $this->redirect('/usuarios', 'Usuario creado exitosamente. Se ha enviado un email de verificación.', 'exito');
            } else {
                $this->redirect('/admin/seguridad/usuarios/create', 'Error al crear el usuario', 'error');
            }
        }

        // Obtener perfiles y personas para los selects
        $perfiles = $this->usuarioModel->getPerfiles();
        $personas = $this->usuarioModel->getPersonas();

        $data = [
            'title' => 'Nuevo Usuario',
            'perfiles' => $perfiles,
            'personas' => $personas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('usuarios')) {
            return $this->view->error(403);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            $this->redirect('/usuarios', 'Usuario no encontrado', 'error');
        }

        if ($this->isPost()) {
            $data = [
                'usuario_nombre' => $this->post('usuario_nombre'),
                'usuario_contrasenia' => $this->post('usuario_contrasenia'),
                'confirmar_contrasenia' => $this->post('confirmar_contrasenia'),
                'rela_perfil' => $this->post('rela_perfil'),
                'rela_persona' => $this->post('rela_persona')
            ];

            // Validar usando el método centralizado (para actualización)
            $errors = $this->usuarioModel->validateUserData($data, true, $id);

            if (!empty($errors)) {
                $this->redirect("/admin/seguridad/usuarios/{$id}/edit", implode('. ', $errors), 'error');
                return;
            }

            // Preparar datos para actualización
            $updateData = [
                'usuario_nombre' => $data['usuario_nombre'],
                'rela_perfil' => $data['rela_perfil'],
                'rela_persona' => $data['rela_persona']
            ];

            // Solo actualizar contrasenia si se proporciona
            if (!empty($data['usuario_contrasenia'])) {
                $updateData['usuario_contrasenia'] = password_hash($data['usuario_contrasenia'], PASSWORD_DEFAULT);
            }

            if ($this->usuarioModel->update($id, $updateData)) {
                $this->redirect('/usuarios', 'Usuario actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/seguridad/usuarios/{$id}/edit", 'Error al actualizar el usuario', 'error');
            }
        }

        $perfiles = $this->usuarioModel->getPerfiles();
        $personas = $this->usuarioModel->getPersonas();

        $data = [
            'title' => 'Editar Usuario',
            'usuario' => $usuario,
            'perfiles' => $perfiles,
            'personas' => $personas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('usuarios')) {
            return $this->view->error(403);
        }

        if ($this->usuarioModel->softDelete($id)) {
            $this->redirect('/usuarios', 'Usuario eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al eliminar el usuario', 'error');
        }
    }

    /**
     * Cambiar estado del usuario
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('usuarios')) {
            return $this->view->error(403);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            $this->redirect('/usuarios', 'Usuario no encontrado', 'error');
        }

        $newStatus = $usuario['usuario_estado'] == 1 ? 0 : 1;
        
        if ($this->usuarioModel->update($id, ['usuario_estado' => $newStatus])) {
            $message = $newStatus ? 'Usuario activado' : 'Usuario desactivado';
            $this->redirect('/usuarios', $message, 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al cambiar el estado', 'error');
        }
    }

    /**
     * Ver perfil de usuario
     */
    public function profile($id = null)
    {
        // Si no se proporciona ID, mostrar perfil del usuario actual
        if (!$id) {
            $id = $_SESSION['usuario_id'] ?? null;
        }

        if (!$id) {
            $this->redirect('/login', 'Debe iniciar sesión', 'error');
        }

        $usuario = $this->usuarioModel->findWithRelations($id);
        if (!$usuario) {
            $this->redirect('/usuarios', 'Usuario no encontrado', 'error');
        }

        $data = [
            'title' => 'Perfil de Usuario',
            'usuario' => $usuario,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/perfil', $data);
    }

    /**
     * Enviar email de verificación al usuario
     */
    private function sendVerificationEmail($userId)
    {
        try {
            // Obtener datos del usuario para el email
            $userData = $this->usuarioModel->getUserForEmail($userId);
            
            if (!$userData || !$userData['persona_email']) {
                error_log("No se pudo obtener el email del usuario ID: $userId");
                return false;
            }
            
            // Generar token de verificación
            $verificationToken = $this->usuarioModel->generateVerificationToken($userId);
            
            if (!$verificationToken) {
                error_log("Error al generar token de verificación para usuario ID: $userId");
                return false;
            }
            
            // Preparar datos para el email
            $recipientEmail = $userData['persona_email'];
            $recipientName = trim($userData['persona_nombre'] . ' ' . $userData['persona_apellido']);
            $userName = $userData['usuario_nombre'];
            
            // Enviar email usando EmailService
            $emailService = new EmailService();
            $result = $emailService->sendUserVerificationEmail(
                $recipientEmail,
                $recipientName,
                $userName,
                $verificationToken
            );
            
            if ($result['success']) {
                error_log("Email de verificación enviado exitosamente a: $recipientEmail (Usuario: $userName)");
                return true;
            } else {
                error_log("Error al enviar email de verificación: " . $result['message']);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("Excepción al enviar email de verificación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar token de verificación de email
     */
    public function verify()
    {
        $token = $this->get('token');
        
        if (!$token) {
            $this->redirect('/', 'Token de verificación no válido', 'error');
            return;
        }
        
        $usuario = $this->usuarioModel->verifyToken($token);
        
        if ($usuario) {
            $nombreCompleto = trim($usuario['persona_nombre'] . ' ' . $usuario['persona_apellido']);
            $this->redirect('/', "¡Email verificado exitosamente! Bienvenido/a $nombreCompleto", 'exito');
        } else {
            $this->redirect('/', 'Token de verificación inválido o expirado', 'error');
        }
    }

    /**
     * Reenviar email de verificación
     */
    public function resendVerification($id = null)
    {
        if (!$this->hasPermission('usuarios') && !$id) {
            return $this->view->error(403);
        }
        
        // Si no se proporciona ID, usar el usuario actual
        if (!$id) {
            $id = $_SESSION['usuario_id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/login', 'Debe iniciar sesión', 'error');
            return;
        }
        
        // Verificar si el email ya está verificado
        if ($this->usuarioModel->isEmailVerified($id)) {
            $this->redirect('/usuarios', 'El email ya está verificado', 'info');
            return;
        }
        
        // Reenviar email de verificación
        if ($this->sendVerificationEmail($id)) {
            $this->redirect('/usuarios', 'Email de verificación reenviado exitosamente', 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al reenviar el email de verificación', 'error');
        }
    }
}
