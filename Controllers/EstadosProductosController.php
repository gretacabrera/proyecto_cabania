<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoProducto;

/**
 * Controlador para Estados de Productos
 */
class EstadosProductosController extends Controller
{
    private $estadoProductoModel;

    public function __construct()
    {
        parent::__construct();
        $this->estadoProductoModel = new EstadoProducto();
    }

    /**
     * Listar estados de productos
     */
    public function index()
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $perPage = $this->get('per_page', 10);
        
        $filters = [
            'search' => $this->get('search', ''),
            'estado' => $this->get('estado', '')
        ];

        if (!empty($filters['search']) || $filters['estado'] !== '') {
            $result = $this->estadoProductoModel->search($filters, $page, $perPage);
            $totalPages = $this->estadoProductoModel->getTotalPages($filters, $perPage);
        } else {
            $result = $this->estadoProductoModel->paginate($page, $perPage, "1=1", "estadoproducto_descripcion");
            $totalPages = $this->estadoProductoModel->getTotalPages([], $perPage);
        }

        $data = [
            'title' => 'Estados de Productos',
            'estados' => $result['data'] ?? [],
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'filters' => $filters,
            'total_records' => $result['total'] ?? 0
        ];

        return $this->render('admin/configuracion/estados_productos/listado', $data);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'estadoproducto_descripcion' => $this->post('estadoproducto_descripcion'),
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('estadoproducto_descripcion', 'La descripción del estado es requerida')
                     ->minLength('estadoproducto_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('estadoproducto_descripcion', 100, 'La descripción no puede exceder 100 caracteres');
            
            if ($validator->fails()) {
                $this->redirect('/estados-productos/create', $validator->firstError(), 'error');
                return;
            }
            
            // Agregar estado por defecto
            $data['estadoproducto_estado'] = 1;

            if ($this->estadoProductoModel->create($data)) {
                $this->redirect('/estados-productos', 'Estado de producto creado exitosamente', 'exito');
            } else {
                $this->redirect('/estados-productos/create', 'Error al crear el estado de producto', 'error');
            }
        }

        $data = ['title' => 'Nuevo Estado de Producto'];
        return $this->render('admin/configuracion/estados_productos/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        $estadoProducto = $this->estadoProductoModel->findById($id);
        if (!$estadoProducto) {
            $this->redirect('/estados-productos', 'Estado de producto no encontrado', 'error');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'estadoproducto_descripcion' => $this->post('estadoproducto_descripcion')
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('estadoproducto_descripcion', 'La descripción del estado es requerida')
                     ->minLength('estadoproducto_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('estadoproducto_descripcion', 100, 'La descripción no puede exceder 100 caracteres');
            
            if ($validator->fails()) {
                $this->redirect("/estados-productos/{$id}/edit", $validator->firstError(), 'error');
                return;
            }

            if ($this->estadoProductoModel->update($id, $data)) {
                $this->redirect('/estados-productos', 'Estado de producto actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/estados-productos/{$id}/edit", 'Error al actualizar el estado de producto', 'error');
            }
        }

        $data = [
            'title' => 'Editar Estado de Producto',
            'estado_producto' => $estadoProducto
        ];

        return $this->render('admin/configuracion/estados_productos/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        // Verificar si está en uso
        if ($this->estadoProductoModel->isInUse($id)) {
            $this->redirect('/estados-productos', 'No se puede eliminar: el estado está siendo utilizado por productos', 'error');
            return;
        }

        if ($this->estadoProductoModel->update($id, ['estadoproducto_estado' => 0])) {
            $this->redirect('/estados-productos', 'Estado de producto eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/estados-productos', 'Error al eliminar el estado de producto', 'error');
        }
    }

    /**
     * Restaurar (quitar baja lógica)
     */
    public function restore($id)
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        if ($this->estadoProductoModel->update($id, ['estadoproducto_estado' => 1])) {
            $this->redirect('/estados-productos', 'Estado de producto restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/estados-productos', 'Error al restaurar el estado de producto', 'error');
        }
    }

    /**
     * Cambiar estado (toggle)
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        $estadoProducto = $this->estadoProductoModel->findById($id);
        if (!$estadoProducto) {
            $this->redirect('/estados-productos', 'Estado de producto no encontrado', 'error');
            return;
        }

        // Si está activo y se intenta desactivar, verificar uso
        if ($estadoProducto['estadoproducto_estado'] == 1 && $this->estadoProductoModel->isInUse($id)) {
            $this->redirect('/estados-productos', 'No se puede desactivar: el estado está siendo utilizado por productos', 'error');
            return;
        }

        if ($this->estadoProductoModel->toggleStatus($id)) {
            $action = $estadoProducto['estadoproducto_estado'] == 1 ? 'desactivado' : 'activado';
            $this->redirect('/estados-productos', "Estado de producto {$action} exitosamente", 'exito');
        } else {
            $this->redirect('/estados-productos', 'Error al cambiar el estado', 'error');
        }
    }

    /**
     * Búsqueda de estados
     */
    public function search()
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        $query = $this->get('q', '');
        $page = $this->get('page', 1);
        $perPage = $this->get('per_page', 10);

        if (empty($query)) {
            $this->redirect('/estados-productos');
            return;
        }

        $filters = ['search' => $query];
        $result = $this->estadoProductoModel->search($filters, $page, $perPage);
        $totalPages = $this->estadoProductoModel->getTotalPages($filters, $perPage);

        $data = [
            'title' => 'Búsqueda de Estados de Productos',
            'estados' => $result['data'] ?? [],
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'filters' => $filters,
            'search_query' => $query,
            'total_records' => $result['total'] ?? 0
        ];

        return $this->render('admin/configuracion/estados_productos/listado', $data);
    }

    /**
     * Ver estadísticas
     */
    public function stats()
    {
        if (!$this->hasPermission('estados_productos')) {
            return $this->view->error(403);
        }

        $estadosConConteo = $this->estadoProductoModel->getWithProductCount();

        $data = [
            'title' => 'Estadísticas de Estados de Productos',
            'estados_stats' => $estadosConConteo
        ];

        return $this->render('admin/configuracion/estados_productos/stats', $data);
    }
}
