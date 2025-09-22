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
        $password = $this->post('usuario_clave');

        if (empty($username) || empty($password)) {
            $this->redirect('/auth/login', 'Por favor complete todos los campos', 'error');
            return;
        }

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
        if ($this->isPost()) {
            return $this->processRegister();
        }

        $data = [
            'title' => 'Registro de Usuario'
        ];

        return $this->render('public/auth/register', $data, 'auth');
    }

    /**
     * Procesar registro
     */
    private function processRegister()
    {
        $username = $this->post('usuario_nombre');
        $password = $this->post('usuario_clave');
        $confirmPassword = $this->post('confirmar_clave');
        $email = $this->post('email');
        $nombre = $this->post('nombre');
        $apellido = $this->post('apellido');

        // Validaciones básicas
        if (empty($username) || empty($password) || empty($email) || empty($nombre) || empty($apellido)) {
            $this->redirect('/auth/register', 'Por favor complete todos los campos', 'error');
        }

        if ($password !== $confirmPassword) {
            $this->redirect('/auth/register', 'Las contraseñas no coinciden', 'error');
        }

        if ($this->usuarioModel->userExists($username)) {
            $this->redirect('/auth/register', 'El nombre de usuario ya existe', 'error');
        }

        $personaModel = new Persona();
        if ($personaModel->emailExists($email)) {
            $this->redirect('/auth/register', 'El email ya está registrado', 'error');
        }

        try {
            // Crear persona primero
            $personaId = $personaModel->create([
                'persona_nombre' => $nombre,
                'persona_apellido' => $apellido,
                'persona_email' => $email,
                'persona_telefono' => $this->post('telefono', ''),
                'persona_documento' => $this->post('documento', ''),
                'rela_estadopersona' => 1, // Estado activo
                'persona_estado' => 1
            ]);

            // Crear usuario
            $userId = $this->usuarioModel->createUser([
                'usuario_nombre' => $username,
                'usuario_clave' => $password,
                'rela_persona' => $personaId,
                'rela_perfil' => 3, // Perfil huésped por defecto
                'usuario_estado' => 1
            ]);

            if ($userId) {
                $this->redirect('/auth/login', 'Usuario registrado correctamente. Ya puede iniciar sesión.', 'exito');
            } else {
                $this->redirect('/auth/register', 'Error al crear el usuario', 'error');
            }
        } catch (\Exception $e) {
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

            if (!password_verify($currentPassword, $usuario['usuario_contrasenia'])) {
                $this->redirect('/auth/change-password', 'La contraseña actual es incorrecta', 'error');
            }

            if ($this->usuarioModel->updatePassword($userId, $newPassword)) {
                $this->redirect('/', 'Contraseña actualizada correctamente', 'exito');
            } else {
                $this->redirect('/auth/change-password', 'Error al actualizar la contraseña', 'error');
            }
        }

        $data = [
            'title' => 'Cambiar Contraseña'
        ];

        return $this->render('public/auth/change-password', $data);
    }
}