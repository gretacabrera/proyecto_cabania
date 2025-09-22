<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Marca;

class MarcasController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new Marca();
    }

    /**
     * Mostrar listado de marcas
     */
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $limit = 10;

        // Obtener filtros de búsqueda
        $filters = [];
        if (!empty($_GET['marca_descripcion'])) {
            $filters['marca_descripcion'] = $_GET['marca_descripcion'];
        }
        if (isset($_GET['marca_estado']) && $_GET['marca_estado'] !== '') {
            $filters['marca_estado'] = $_GET['marca_estado'];
        }

        $result = $this->model->getWithFilters($filters, $page, $limit);

        return $this->render('admin/configuracion/marcas/listado', [
            'marcas' => $result['data'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'current_page' => $result['current_page'],
            'filters' => $filters,
            'title' => 'Marcas'
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return $this->render('admin/configuracion/marcas/formulario', [
            'marca' => null,
            'action' => '/admin/configuracion/marcas/store',
            'title' => 'Nueva Marca'
        ]);
    }

    /**
     * Guardar nueva marca
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/configuracion/marcas/create');
            exit;
        }

        $data = $this->model->sanitizeData($_POST);
        $errors = $this->model->validate($data);

        if (!empty($errors)) {
            return $this->render('admin/configuracion/marcas/formulario', [
                'marca' => (object) $data,
                'errors' => $errors,
                'action' => '/admin/configuracion/marcas/store',
                'title' => 'Nueva Marca'
            ]);
        }

        // Establecer estado por defecto si no se proporciona
        if (!isset($data['marca_estado'])) {
            $data['marca_estado'] = 1;
        }

        $id = $this->model->create($data);

        if ($id) {
            $_SESSION['success'] = 'Marca creada exitosamente.';
            header('Location: /marcas');
        } else {
            $_SESSION['error'] = 'Error al crear la marca.';
            header('Location: /admin/configuracion/marcas/create');
        }
        exit;
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $marca = $this->model->find($id);

        if (!$marca) {
            $_SESSION['error'] = 'Marca no encontrada.';
            header('Location: /marcas');
            exit;
        }

        return $this->render('admin/configuracion/marcas/formulario', [
            'marca' => (object) $marca,
            'action' => "/admin/configuracion/marcas/update/{$id}",
            'title' => 'Editar Marca'
        ]);
    }

    /**
     * Actualizar marca
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/configuracion/marcas/edit/{$id}");
            exit;
        }

        $marca = $this->model->find($id);
        if (!$marca) {
            $_SESSION['error'] = 'Marca no encontrada.';
            header('Location: /marcas');
            exit;
        }

        $data = $this->model->sanitizeData($_POST);
        $errors = $this->model->validate($data, true, $id);

        if (!empty($errors)) {
            return $this->render('admin/configuracion/marcas/formulario', [
                'marca' => (object) array_merge($marca, $data),
                'errors' => $errors,
                'action' => "/admin/configuracion/marcas/update/{$id}",
                'title' => 'Editar Marca'
            ]);
        }

        $success = $this->model->update($id, $data);

        if ($success) {
            $_SESSION['success'] = 'Marca actualizada exitosamente.';
            header('Location: /marcas');
        } else {
            $_SESSION['error'] = 'Error al actualizar la marca.';
            header("Location: /admin/configuracion/marcas/edit/{$id}");
        }
        exit;
    }

    /**
     * Cambiar estado de la marca
     */
    public function toggle($id)
    {
        $marca = $this->model->find($id);
        if (!$marca) {
            $_SESSION['error'] = 'Marca no encontrada.';
            header('Location: /marcas');
            exit;
        }

        // Verificar si está en uso antes de desactivar
        if ($marca['marca_estado'] == 1 && $this->model->isInUse($id)) {
            $_SESSION['error'] = 'No se puede desactivar la marca porque está siendo utilizada por productos.';
            header('Location: /marcas');
            exit;
        }

        $success = $this->model->toggleStatus($id);

        if ($success) {
            $action = $marca['marca_estado'] == 1 ? 'desactivada' : 'activada';
            $_SESSION['success'] = "Marca {$action} exitosamente.";
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado de la marca.';
        }

        header('Location: /marcas');
        exit;
    }

    /**
     * Ver detalles de una marca con sus productos
     */
    public function show($id)
    {
        $marca = $this->model->find($id);
        if (!$marca) {
            $_SESSION['error'] = 'Marca no encontrada.';
            header('Location: /marcas');
            exit;
        }

        $productos = $this->model->getProductos($id, 20);

        return $this->render('admin/configuracion/marcas/detalle', [
            'marca' => $marca,
            'productos' => $productos,
            'title' => 'Detalle de Marca: ' . $marca['marca_descripcion']
        ]);
    }

    /**
     * Mostrar estadísticas de marcas
     */
    public function stats()
    {
        $stats = $this->model->getStats();
        $marcasActivas = $this->model->getActive();

        return $this->render('admin/configuracion/marcas/stats', [
            'stats' => $stats,
            'marcas_activas' => $marcasActivas,
            'title' => 'Estadísticas de Marcas'
        ]);
    }

    /**
     * API para obtener marcas activas (JSON)
     */
    public function api_active()
    {
        header('Content-Type: application/json');
        $marcas = $this->model->getActive();
        echo json_encode($marcas);
        exit;
    }

    /**
     * Buscar marcas por término
     */
    public function search()
    {
        if (empty($_GET['term'])) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $marcas = $this->model->searchByTerm($_GET['term']);
        
        header('Content-Type: application/json');
        echo json_encode($marcas);
        exit;
    }
}
