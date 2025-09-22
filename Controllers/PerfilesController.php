<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Perfil;

/**
 * Controlador para la gestión de perfiles de usuario
 */
class PerfilesController extends Controller
{
    protected $perfilModel;

    public function __construct()
    {
        parent::__construct();
        $this->perfilModel = new Perfil();
    }

    /**
     * Listar todos los perfiles
     */
    public function index()
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');

        if ($search) {
            $perfiles = $this->perfilModel->search($search, $page);
            $totalPages = $this->perfilModel->getTotalPages($search);
        } else {
            $perfiles = $this->perfilModel->paginate($page);
            $totalPages = $this->perfilModel->getTotalPages();
        }

        $data = [
            'title' => 'Gestión de Perfiles',
            'perfiles' => $perfiles,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ];

        return $this->render('admin/seguridad/perfiles/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo perfil
     */
    public function create()
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'perfil_nombre' => $this->post('perfil_nombre'),
                'perfil_descripcion' => $this->post('perfil_descripcion'),
                'perfil_estado' => 1
            ];

            $perfilId = $this->perfilModel->create($data);
            
            if ($perfilId) {
                // Asignar módulos al perfil
                $modulos = $this->post('modulos', []);
                if (!empty($modulos)) {
                    $this->perfilModel->assignModules($perfilId, $modulos);
                }
                
                $this->redirect('/perfiles', 'Perfil creado exitosamente', 'exito');
            } else {
                $this->redirect('/admin/seguridad/perfiles/create', 'Error al crear el perfil', 'error');
            }
        }

        // Obtener módulos disponibles
        $modulos = $this->perfilModel->getAvailableModules();

        $data = [
            'title' => 'Nuevo Perfil',
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/perfiles/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $perfil = $this->perfilModel->findWithModules($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
        }

        if ($this->isPost()) {
            $data = [
                'perfil_nombre' => $this->post('perfil_nombre'),
                'perfil_descripcion' => $this->post('perfil_descripcion')
            ];

            if ($this->perfilModel->update($id, $data)) {
                // Actualizar módulos asignados
                $modulos = $this->post('modulos', []);
                $this->perfilModel->updateModules($id, $modulos);
                
                $this->redirect('/perfiles', 'Perfil actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/seguridad/perfiles/{$id}/edit", 'Error al actualizar el perfil', 'error');
            }
        }

        $modulos = $this->perfilModel->getAvailableModules();

        $data = [
            'title' => 'Editar Perfil',
            'perfil' => $perfil,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/perfiles/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        // Verificar que no tenga usuarios asignados
        if ($this->perfilModel->hasActiveUsers($id)) {
            $this->redirect('/perfiles', 'No se puede eliminar un perfil con usuarios activos', 'error');
        }

        if ($this->perfilModel->softDelete($id)) {
            $this->redirect('/perfiles', 'Perfil eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/perfiles', 'Error al eliminar el perfil', 'error');
        }
    }

    /**
     * Restaurar perfil
     */
    public function restore($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        if ($this->perfilModel->restore($id)) {
            $this->redirect('/perfiles', 'Perfil restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/perfiles', 'Error al restaurar el perfil', 'error');
        }
    }

    /**
     * Buscar perfiles
     */
    public function search()
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $query = $this->get('q', '');
        $page = $this->get('page', 1);

        if (empty($query)) {
            $this->redirect('/perfiles', 'Ingrese un término de búsqueda', 'warning');
        }

        $perfiles = $this->perfilModel->search($query, $page);
        $totalPages = $this->perfilModel->getTotalPages($query);

        $data = [
            'title' => 'Búsqueda de Perfiles',
            'perfiles' => $perfiles,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $query
        ];

        return $this->render('admin/seguridad/perfiles/busqueda', $data);
    }

    /**
     * Ver módulos asignados al perfil
     */
    public function modules($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $perfil = $this->perfilModel->findWithModules($id);
        if (!$perfil) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Módulos del Perfil',
            'perfil' => $perfil
        ];

        return $this->render('admin/seguridad/perfiles/modulos', $data);
    }

    /**
     * Ver usuarios con este perfil
     */
    public function users($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            return $this->view->error(404);
        }

        $usuarios = $this->perfilModel->getUsers($id);

        $data = [
            'title' => 'Usuarios del Perfil',
            'perfil' => $perfil,
            'usuarios' => $usuarios
        ];

        return $this->render('admin/seguridad/perfiles/usuarios', $data);
    }

    /**
     * Cambiar estado del perfil
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $perfil = $this->perfilModel->find($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
        }

        $newStatus = $perfil['perfil_estado'] == 1 ? 0 : 1;
        
        if ($this->perfilModel->update($id, ['perfil_estado' => $newStatus])) {
            $message = $newStatus ? 'Perfil activado' : 'Perfil desactivado';
            $this->redirect('/perfiles', $message, 'exito');
        } else {
            $this->redirect('/perfiles', 'Error al cambiar el estado', 'error');
        }
    }

    /**
     * Clonar perfil
     */
    public function clone($id)
    {
        if (!$this->hasPermission('perfiles')) {
            return $this->view->error(403);
        }

        $perfil = $this->perfilModel->findWithModules($id);
        if (!$perfil) {
            $this->redirect('/perfiles', 'Perfil no encontrado', 'error');
        }

        if ($this->isPost()) {
            $newName = $this->post('perfil_nombre');
            
            if (empty($newName)) {
                $this->redirect("/admin/seguridad/perfiles/{$id}/clone", 'Debe especificar un nombre', 'error');
            }

            $newPerfilId = $this->perfilModel->clonePerfil($id, $newName);
            
            if ($newPerfilId) {
                $this->redirect('/perfiles', 'Perfil clonado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/seguridad/perfiles/{$id}/clone", 'Error al clonar el perfil', 'error');
            }
        }

        $data = [
            'title' => 'Clonar Perfil',
            'perfil' => $perfil
        ];

        return $this->render('admin/seguridad/perfiles/clonar', $data);
    }
}
