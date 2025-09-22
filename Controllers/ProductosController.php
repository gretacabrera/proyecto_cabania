<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;

/**
 * Controlador para la gestión de productos
 */
class ProductosController extends Controller
{
    protected $productoModel;
    protected $categoriaModel;
    protected $marcaModel;

    public function __construct()
    {
        parent::__construct();
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->marcaModel = new Marca();
    }

    /**
     * Listar todos los productos
     */
    public function index()
    {
        if (!$this->hasPermission('productos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');

        if ($search) {
            $productos = $this->productoModel->search($search, $page);
        } else {
            $productos = $this->productoModel->paginate($page);
        }

        $data = [
            'title' => 'Gestión de Productos',
            'productos' => $productos,
            'currentPage' => $page,
            'search' => $search
        ];

        return $this->render('admin/operaciones/productos/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo producto
     */
    public function create()
    {
        if (!$this->hasPermission('productos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'producto_descripcion' => $this->post('producto_descripcion'),
                'producto_precio' => $this->post('producto_precio'),
                'producto_costo' => $this->post('producto_costo'),
                'producto_stock' => $this->post('producto_stock'),
                'rela_categoria' => $this->post('rela_categoria'),
                'rela_marca' => $this->post('rela_marca'),
                'rela_estado_producto' => $this->post('rela_estado_producto', 1)
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('producto_descripcion', 'La descripción del producto es requerida')
                     ->minLength('producto_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('producto_descripcion', 255, 'La descripción no puede exceder 255 caracteres')
                     ->required('producto_precio', 'El precio es requerido')
                     ->positiveDecimal('producto_precio', 'El precio debe ser un número positivo')
                     ->positiveDecimal('producto_costo', 'El costo debe ser un número positivo o cero')
                     ->positiveInteger('producto_stock', 'El stock debe ser un número entero positivo o cero')
                     ->required('rela_categoria', 'Debe seleccionar una categoría')
                     ->positiveInteger('rela_categoria', 'Categoría inválida')
                     ->required('rela_marca', 'Debe seleccionar una marca')
                     ->positiveInteger('rela_marca', 'Marca inválida');
            
            if ($validator->fails()) {
                $this->redirect('/admin/operaciones/productos/create', $validator->firstError(), 'error');
                return;
            }

            if ($this->productoModel->create($data)) {
                $this->redirect('/productos', 'Producto creado exitosamente', 'exito');
            } else {
                $this->redirect('/admin/operaciones/productos/create', 'Error al crear el producto', 'error');
            }
        }

        $categorias = $this->categoriaModel->findAll();
        $marcas = $this->marcaModel->findAll();

        $data = [
            'title' => 'Nuevo Producto',
            'categorias' => $categorias,
            'marcas' => $marcas
        ];

        return $this->render('admin/operaciones/productos/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('productos')) {
            return $this->view->error(403);
        }

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        if ($this->isPost()) {
            $data = [
                'producto_descripcion' => $this->post('producto_descripcion'),
                'producto_precio' => $this->post('producto_precio'),
                'producto_costo' => $this->post('producto_costo'),
                'producto_stock' => $this->post('producto_stock'),
                'rela_categoria' => $this->post('rela_categoria'),
                'rela_marca' => $this->post('rela_marca')
            ];

            if ($this->productoModel->update($id, $data)) {
                $this->redirect('/productos', 'Producto actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/operaciones/productos/{$id}/edit", 'Error al actualizar el producto', 'error');
            }
        }

        $categorias = $this->categoriaModel->findAll();
        $marcas = $this->marcaModel->findAll();

        $data = [
            'title' => 'Editar Producto',
            'producto' => $producto,
            'categorias' => $categorias,
            'marcas' => $marcas
        ];

        return $this->render('admin/operaciones/productos/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('productos')) {
            return $this->view->error(403);
        }

        if ($this->productoModel->softDelete($id)) {
            $this->redirect('/productos', 'Producto eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/productos', 'Error al eliminar el producto', 'error');
        }
    }

    /**
     * Ver detalle de producto
     */
    public function show($id)
    {
        if (!$this->hasPermission('productos')) {
            return $this->view->error(403);
        }

        $producto = $this->productoModel->findWithRelations($id);
        if (!$producto) {
            $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        $data = [
            'title' => 'Detalle del Producto',
            'producto' => $producto
        ];

        return $this->render('admin/operaciones/productos/detalle', $data);
    }
}
