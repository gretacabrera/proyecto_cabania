<?php

namespace App\Controllers;

use App\Core\Controller;
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
                'usuario_clave' => password_hash($this->post('usuario_clave'), PASSWORD_DEFAULT),
                'rela_perfil' => $this->post('rela_perfil'),
                'rela_persona' => $this->post('rela_persona'),
                'usuario_estado' => 1
            ];

            // Verificar que el usuario no exista
            if ($this->usuarioModel->findByUsername($data['usuario_nombre'])) {
                $this->redirect('/admin/seguridad/usuarios/create', 'El nombre de usuario ya existe', 'error');
            }

            if ($this->usuarioModel->create($data)) {
                $this->redirect('/usuarios', 'Usuario creado exitosamente', 'exito');
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
                'rela_perfil' => $this->post('rela_perfil'),
                'rela_persona' => $this->post('rela_persona')
            ];

            // Solo actualizar clave si se proporciona
            if ($this->post('usuario_clave')) {
                $data['usuario_clave'] = password_hash($this->post('usuario_clave'), PASSWORD_DEFAULT);
            }

            // Verificar que el usuario no exista (excepto el actual)
            $existingUser = $this->usuarioModel->findByUsername($data['usuario_nombre']);
            if ($existingUser && $existingUser['id_usuario'] != $id) {
                $this->redirect("/admin/seguridad/usuarios/{$id}/edit", 'El nombre de usuario ya existe', 'error');
            }

            if ($this->usuarioModel->update($id, $data)) {
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
}
