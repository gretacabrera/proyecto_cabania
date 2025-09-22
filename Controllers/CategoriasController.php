<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;

/**
 * Controlador para la gestión de categorías
 */
class CategoriasController extends Controller
{
    protected $categoriaModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoriaModel = new Categoria();
    }

    /**
     * Listar todas las categorías
     */
    public function index()
    {
        if (!$this->hasPermission('categorias')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');

        if ($search) {
            $categorias = $this->categoriaModel->search($search, $page);
            $totalPages = $this->categoriaModel->getTotalPages($search);
        } else {
            $categorias = $this->categoriaModel->paginate($page);
            $totalPages = $this->categoriaModel->getTotalPages();
        }

        $data = [
            'title' => 'Gestión de Categorías',
            'categorias' => $categorias,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ];

        return $this->render('admin/configuracion/categorias/listado', $data);
    }

    /**
     * Mostrar formulario de nueva categoría
     */
    public function create()
    {
        if (!$this->hasPermission('categorias')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'categoria_descripcion' => $this->post('categoria_descripcion'),
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('categoria_descripcion', 'La descripción de la categoría es requerida')
                     ->minLength('categoria_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('categoria_descripcion', 100, 'La descripción no puede exceder 100 caracteres');
            
            if ($validator->fails()) {
                $this->redirect('/admin/configuracion/categorias/create', $validator->firstError(), 'error');
                return;
            }
            
            // Agregar estado por defecto
            $data['categoria_estado'] = 1;

            if ($this->categoriaModel->create($data)) {
                $this->redirect('/categorias', 'Categoría creada exitosamente', 'exito');
            } else {
                $this->redirect('/admin/configuracion/categorias/create', 'Error al crear la categoría', 'error');
            }
        }

        $data = ['title' => 'Nueva Categoría'];
        return $this->render('admin/configuracion/categorias/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('categorias')) {
            return $this->view->error(403);
        }

        $categoria = $this->categoriaModel->findById($id);
        if (!$categoria) {
            $this->redirect('/categorias', 'Categoría no encontrada', 'error');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'categoria_descripcion' => $this->post('categoria_descripcion')
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('categoria_descripcion', 'La descripción de la categoría es requerida')
                     ->minLength('categoria_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('categoria_descripcion', 100, 'La descripción no puede exceder 100 caracteres');
            
            if ($validator->fails()) {
                $this->redirect("/admin/configuracion/categorias/{$id}/edit", $validator->firstError(), 'error');
                return;
            }

            if ($this->categoriaModel->update($id, $data)) {
                $this->redirect('/categorias', 'Categoría actualizada exitosamente', 'exito');
            } else {
                $this->redirect("/admin/configuracion/categorias/{$id}/edit", 'Error al actualizar la categoría', 'error');
            }
        }

        $data = [
            'title' => 'Editar Categoría',
            'categoria' => $categoria
        ];

        return $this->render('admin/configuracion/categorias/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('categorias')) {
            return $this->view->error(403);
        }

        if ($this->categoriaModel->softDelete($id)) {
            $this->redirect('/categorias', 'Categoría eliminada exitosamente', 'exito');
        } else {
            $this->redirect('/categorias', 'Error al eliminar la categoría', 'error');
        }
    }

    /**
     * Restaurar categoría
     */
    public function restore($id)
    {
        if (!$this->hasPermission('categorias')) {
            return $this->view->error(403);
        }

        if ($this->categoriaModel->restore($id)) {
            $this->redirect('/categorias', 'Categoría restaurada exitosamente', 'exito');
        } else {
            $this->redirect('/categorias', 'Error al restaurar la categoría', 'error');
        }
    }

    /**
     * Búsqueda de categorías
     */
    public function search()
    {
        if (!$this->hasPermission('categorias')) {
            return $this->view->error(403);
        }

        $search = $this->get('q', '');
        $page = $this->get('page', 1);

        if (empty($search)) {
            $this->redirect('/categorias');
        }

        $categorias = $this->categoriaModel->search($search, $page);
        $totalPages = $this->categoriaModel->getTotalPages($search);

        $data = [
            'title' => 'Búsqueda de Categorías',
            'categorias' => $categorias,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ];

        return $this->render('admin/configuracion/categorias/listado', $data);
    }
}
