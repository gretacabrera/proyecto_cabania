<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Modulo;

/**
 * Controlador para la gestión de módulos del sistema
 */
class ModulosController extends Controller
{
    protected $moduloModel;

    public function __construct()
    {
        parent::__construct();
        $this->moduloModel = new Modulo();
    }

    /**
     * Listar todos los módulos
     */
    public function index()
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');

        if ($search) {
            $modulos = $this->moduloModel->search($search, $page);
            $totalPages = $this->moduloModel->getTotalPages($search);
        } else {
            $modulos = $this->moduloModel->paginate($page);
            $totalPages = $this->moduloModel->getTotalPages();
        }

        $data = [
            'title' => 'Gestión de Módulos',
            'modulos' => $modulos,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ];

        return $this->render('admin/seguridad/modulos/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo módulo
     */
    public function create()
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'modulo_nombre' => $this->post('modulo_nombre'),
                'modulo_descripcion' => $this->post('modulo_descripcion'),
                'modulo_url' => $this->post('modulo_url'),
                'modulo_icono' => $this->post('modulo_icono'),
                'modulo_orden' => $this->post('modulo_orden'),
                'modulo_padre' => $this->post('modulo_padre') ?: null,
                'modulo_estado' => 1
            ];

            if ($this->moduloModel->create($data)) {
                $this->redirect('/modulos', 'Módulo creado exitosamente', 'exito');
            } else {
                $this->redirect('/admin/seguridad/modulos/create', 'Error al crear el módulo', 'error');
            }
        }

        // Obtener módulos padre para el select
        $modulosPadre = $this->moduloModel->getModulosPadre();

        $data = [
            'title' => 'Nuevo Módulo',
            'modulosPadre' => $modulosPadre
        ];

        return $this->render('admin/seguridad/modulos/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            $this->redirect('/modulos', 'Módulo no encontrado', 'error');
        }

        if ($this->isPost()) {
            $data = [
                'modulo_nombre' => $this->post('modulo_nombre'),
                'modulo_descripcion' => $this->post('modulo_descripcion'),
                'modulo_url' => $this->post('modulo_url'),
                'modulo_icono' => $this->post('modulo_icono'),
                'modulo_orden' => $this->post('modulo_orden'),
                'modulo_padre' => $this->post('modulo_padre') ?: null
            ];

            if ($this->moduloModel->update($id, $data)) {
                $this->redirect('/modulos', 'Módulo actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/seguridad/modulos/{$id}/edit", 'Error al actualizar el módulo', 'error');
            }
        }

        $modulosPadre = $this->moduloModel->getModulosPadre($id);

        $data = [
            'title' => 'Editar Módulo',
            'modulo' => $modulo,
            'modulosPadre' => $modulosPadre
        ];

        return $this->render('admin/seguridad/modulos/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        // Verificar que no tenga submódulos activos
        if ($this->moduloModel->hasActiveChildren($id)) {
            $this->redirect('/modulos', 'No se puede eliminar un módulo con submódulos activos', 'error');
        }

        // Verificar que no esté asignado a perfiles
        if ($this->moduloModel->hasProfileAssignments($id)) {
            $this->redirect('/modulos', 'No se puede eliminar un módulo asignado a perfiles', 'error');
        }

        if ($this->moduloModel->softDelete($id)) {
            $this->redirect('/modulos', 'Módulo eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/modulos', 'Error al eliminar el módulo', 'error');
        }
    }

    /**
     * Restaurar módulo
     */
    public function restore($id)
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        if ($this->moduloModel->restore($id)) {
            $this->redirect('/modulos', 'Módulo restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/modulos', 'Error al restaurar el módulo', 'error');
        }
    }

    /**
     * Buscar módulos
     */
    public function search()
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        $query = $this->get('q', '');
        $page = $this->get('page', 1);

        if (empty($query)) {
            $this->redirect('/modulos', 'Ingrese un término de búsqueda', 'warning');
        }

        $modulos = $this->moduloModel->search($query, $page);
        $totalPages = $this->moduloModel->getTotalPages($query);

        $data = [
            'title' => 'Búsqueda de Módulos',
            'modulos' => $modulos,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $query
        ];

        return $this->render('admin/seguridad/modulos/busqueda', $data);
    }

    /**
     * Ver estructura jerárquica de módulos
     */
    public function hierarchy()
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        $modulosTree = $this->moduloModel->getModulosTree();

        $data = [
            'title' => 'Estructura de Módulos',
            'modulosTree' => $modulosTree
        ];

        return $this->render('admin/seguridad/modulos/jerarquia', $data);
    }

    /**
     * Cambiar estado del módulo
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            $this->redirect('/modulos', 'Módulo no encontrado', 'error');
        }

        $newStatus = $modulo['modulo_estado'] == 1 ? 0 : 1;
        
        if ($this->moduloModel->update($id, ['modulo_estado' => $newStatus])) {
            $message = $newStatus ? 'Módulo activado' : 'Módulo desactivado';
            $this->redirect('/modulos', $message, 'exito');
        } else {
            $this->redirect('/modulos', 'Error al cambiar el estado', 'error');
        }
    }

    /**
     * Actualizar orden de módulos
     */
    public function updateOrder()
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        if (!$this->isPost()) {
            return $this->view->error(405);
        }

        $orders = $this->post('orders', []);
        
        foreach ($orders as $id => $order) {
            $this->moduloModel->update($id, ['modulo_orden' => $order]);
        }

        $this->redirect('/modulos', 'Orden actualizado exitosamente', 'exito');
    }

    /**
     * Ver permisos del módulo
     */
    public function permissions($id)
    {
        if (!$this->hasPermission('modulos')) {
            return $this->view->error(403);
        }

        $modulo = $this->moduloModel->findWithPermissions($id);
        if (!$modulo) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Permisos del Módulo',
            'modulo' => $modulo
        ];

        return $this->render('admin/seguridad/modulos/permisos', $data);
    }
}
