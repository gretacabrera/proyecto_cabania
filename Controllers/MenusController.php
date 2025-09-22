<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Modulo;

/**
 * Controlador para la gestión de menús
 */
class MenusController extends Controller
{
    protected $menuModel;
    protected $moduloModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuModel = new Menu();
        $this->moduloModel = new Modulo();
    }

    /**
     * Listar todos los menús
     */
    public function index()
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $page = (int) $this->get('page', 1);
        $search = trim($this->get('buscar', ''));
        $orderBy = $this->get('orderBy', 'menu_orden');
        $orderDir = $this->get('orderDir', 'ASC');

        $filters = [
            'search' => $search,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ];

        $result = $this->menuModel->getWithFilters($filters, $page);

        $data = [
            'title' => 'Gestión de Menús',
            'menus' => $result['data'],
            'currentPage' => $page,
            'totalPages' => $result['totalPages'],
            'totalRecords' => $result['totalRecords'],
            'search' => $search,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir,
            'stats' => $this->menuModel->getStats()
        ];

        return $this->render('admin/seguridad/menus/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo menú
     */
    public function create()
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'menu_nombre' => trim($this->post('menu_nombre')),
                'menu_orden' => (int) $this->post('menu_orden', 1),
                'menu_estado' => 1
            ];

            $validation = $this->menuModel->validate($data);
            if ($validation !== true) {
                $this->redirect('/admin/seguridad/menus/create', $validation, 'error');
                return;
            }

            if ($this->menuModel->create($data)) {
                $this->redirect('/menus', 'Menú creado exitosamente', 'exito');
            } else {
                $this->redirect('/admin/seguridad/menus/create', 'Error al crear el menú', 'error');
            }
            return;
        }

        // Obtener módulos disponibles para asociar (simplificado)
        $modulos = [];

        $data = [
            'title' => 'Nuevo Menú',
            'menu' => null,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/menus/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $menu = $this->menuModel->find($id);
        if (!$menu) {
            $this->redirect('/menus', 'Menú no encontrado', 'error');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'menu_nombre' => trim($this->post('menu_nombre')),
                'menu_orden' => (int) $this->post('menu_orden', 1)
            ];

            $validation = $this->menuModel->validate($data, $id);
            if ($validation !== true) {
                $this->redirect("/admin/seguridad/menus/{$id}/edit", $validation, 'error');
                return;
            }

            if ($this->menuModel->update($id, $data)) {
                $this->redirect('/menus', 'Menú actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/seguridad/menus/{$id}/edit", 'Error al actualizar el menú', 'error');
            }
            return;
        }

        // Obtener módulos disponibles para asociar (simplificado)
        $modulos = [];

        $data = [
            'title' => 'Editar Menú',
            'menu' => $menu,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/menus/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $menu = $this->menuModel->find($id);
        if (!$menu) {
            $this->redirect('/menus', 'Menú no encontrado', 'error');
            return;
        }

        if ($this->menuModel->update($id, ['menu_estado' => 0])) {
            $this->redirect('/menus', 'Menú eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/menus', 'Error al eliminar el menú', 'error');
        }
    }

    /**
     * Restaurar menú
     */
    public function restore($id)
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $menu = $this->menuModel->find($id);
        if (!$menu) {
            $this->redirect('/menus', 'Menú no encontrado', 'error');
            return;
        }

        if ($this->menuModel->update($id, ['menu_estado' => 1])) {
            $this->redirect('/menus', 'Menú restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/menus', 'Error al restaurar el menú', 'error');
        }
    }

    /**
     * Buscar menús
     */
    public function search()
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $query = trim($this->get('q', ''));
        $page = (int) $this->get('page', 1);

        if (empty($query)) {
            $this->redirect('/menus', 'Ingrese un término de búsqueda', 'warning');
            return;
        }

        $filters = [
            'search' => $query,
            'orderBy' => 'menu_orden',
            'orderDir' => 'ASC'
        ];

        $result = $this->menuModel->getWithFilters($filters, $page);

        $data = [
            'title' => 'Búsqueda de Menús',
            'menus' => $result['data'],
            'currentPage' => $page,
            'totalPages' => $result['totalPages'],
            'totalRecords' => $result['totalRecords'],
            'search' => $query
        ];

        return $this->render('admin/seguridad/menus/busqueda', $data);
    }

    /**
     * Ver detalles de un menú
     */
    public function show($id)
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $menu = $this->menuModel->find($id);
        if (!$menu) {
            $this->redirect('/menus', 'Menú no encontrado', 'error');
            return;
        }

        // Obtener módulos asociados a este menú
        $modulos = $this->menuModel->getModulos($id);

        $data = [
            'title' => 'Detalle del Menú: ' . $menu['menu_nombre'],
            'menu' => $menu,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/menus/detalle', $data);
    }

    /**
     * Mostrar estadísticas de menús
     */
    public function stats()
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        $stats = $this->menuModel->getStats();

        $data = [
            'title' => 'Estadísticas de Menús',
            'stats' => $stats
        ];

        return $this->render('admin/seguridad/menus/stats', $data);
    }

    /**
     * Reordenar menús
     */
    public function reorder()
    {
        if (!$this->hasPermission('menus')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $orders = $this->post('orders', []);
            
            if ($this->menuModel->reorder($orders)) {
                $this->redirect('/menus', 'Orden actualizado exitosamente', 'exito');
            } else {
                $this->redirect('/menus', 'Error al actualizar el orden', 'error');
            }
            return;
        }

        // Obtener menús activos ordenados
        $filters = [
            'estado' => 1,
            'orderBy' => 'menu_orden',
            'orderDir' => 'ASC'
        ];
        $result = $this->menuModel->getWithFilters($filters);
        $menus = $result['data'];

        $data = [
            'title' => 'Reordenar Menús',
            'menus' => $menus
        ];

        return $this->render('admin/seguridad/menus/reordenar', $data);
    }
}
