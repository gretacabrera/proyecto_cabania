<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MetodoPago;

class MetodosPagosController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new MetodoPago();
    }

    /**
     * Mostrar listado de métodos de pago
     */
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $limit = 10;

        // Obtener filtros de búsqueda
        $filters = [];
        if (!empty($_GET['metododepago_descripcion'])) {
            $filters['metododepago_descripcion'] = $_GET['metododepago_descripcion'];
        }
        if (isset($_GET['metododepago_estado']) && $_GET['metododepago_estado'] !== '') {
            $filters['metododepago_estado'] = $_GET['metododepago_estado'];
        }

        $result = $this->model->getWithFilters($filters, $page, $limit);

        return $this->render('admin/configuracion/metodos_pagos/listado', [
            'metodos' => $result['data'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'current_page' => $result['current_page'],
            'filters' => $filters,
            'title' => 'Métodos de Pago'
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return $this->render('admin/configuracion/metodos_pagos/formulario', [
            'method' => null,
            'action' => '/admin/configuracion/metodos_pagos/store',
            'title' => 'Nuevo Método de Pago'
        ]);
    }

    /**
     * Guardar nuevo método de pago
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/configuracion/metodos_pagos/create');
            exit;
        }

        $data = $this->model->sanitizeData($_POST);
        $errors = $this->model->validate($data);

        if (!empty($errors)) {
            return $this->render('admin/configuracion/metodos_pagos/formulario', [
                'method' => (object) $data,
                'errors' => $errors,
                'action' => '/admin/configuracion/metodos_pagos/store',
                'title' => 'Nuevo Método de Pago'
            ]);
        }

        // Establecer estado por defecto si no se proporciona
        if (!isset($data['metododepago_estado'])) {
            $data['metododepago_estado'] = 1;
        }

        $id = $this->model->create($data);

        if ($id) {
            $_SESSION['success'] = 'Método de pago creado exitosamente.';
            header('Location: /metodos_pagos');
        } else {
            $_SESSION['error'] = 'Error al crear el método de pago.';
            header('Location: /admin/configuracion/metodos_pagos/create');
        }
        exit;
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $method = $this->model->find($id);

        if (!$method) {
            $_SESSION['error'] = 'Método de pago no encontrado.';
            header('Location: /metodos_pagos');
            exit;
        }

        return $this->render('admin/configuracion/metodos_pagos/formulario', [
            'method' => (object) $method,
            'action' => "/admin/configuracion/metodos_pagos/update/{$id}",
            'title' => 'Editar Método de Pago'
        ]);
    }

    /**
     * Actualizar método de pago
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/configuracion/metodos_pagos/edit/{$id}");
            exit;
        }

        $method = $this->model->find($id);
        if (!$method) {
            $_SESSION['error'] = 'Método de pago no encontrado.';
            header('Location: /metodos_pagos');
            exit;
        }

        $data = $this->model->sanitizeData($_POST);
        $errors = $this->model->validate($data, true, $id);

        if (!empty($errors)) {
            return $this->render('admin/configuracion/metodos_pagos/formulario', [
                'method' => (object) array_merge($method, $data),
                'errors' => $errors,
                'action' => "/admin/configuracion/metodos_pagos/update/{$id}",
                'title' => 'Editar Método de Pago'
            ]);
        }

        $success = $this->model->update($id, $data);

        if ($success) {
            $_SESSION['success'] = 'Método de pago actualizado exitosamente.';
            header('Location: /metodos_pagos');
        } else {
            $_SESSION['error'] = 'Error al actualizar el método de pago.';
            header("Location: /admin/configuracion/metodos_pagos/edit/{$id}");
        }
        exit;
    }

    /**
     * Cambiar estado del método de pago
     */
    public function toggle($id)
    {
        $method = $this->model->find($id);
        if (!$method) {
            $_SESSION['error'] = 'Método de pago no encontrado.';
            header('Location: /metodos_pagos');
            exit;
        }

        // Verificar si está en uso antes de desactivar
        if ($method['metododepago_estado'] == 1 && $this->model->isInUse($id)) {
            $_SESSION['error'] = 'No se puede desactivar el método de pago porque está siendo utilizado.';
            header('Location: /metodos_pagos');
            exit;
        }

        $success = $this->model->toggleStatus($id);

        if ($success) {
            $action = $method['metododepago_estado'] == 1 ? 'desactivado' : 'activado';
            $_SESSION['success'] = "Método de pago {$action} exitosamente.";
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado del método de pago.';
        }

        header('Location: /metodos_pagos');
        exit;
    }

    /**
     * Mostrar estadísticas de métodos de pago
     */
    public function stats()
    {
        $stats = $this->model->getStats();
        $metodosActivos = $this->model->getActive();

        return $this->render('admin/configuracion/metodos_pagos/stats', [
            'stats' => $stats,
            'metodos_activos' => $metodosActivos,
            'title' => 'Estadísticas de Métodos de Pago'
        ]);
    }

    /**
     * API para obtener métodos activos (JSON)
     */
    public function api_active()
    {
        header('Content-Type: application/json');
        $metodos = $this->model->getActive();
        echo json_encode($metodos);
        exit;
    }

    /**
     * Buscar métodos por término
     */
    public function search()
    {
        if (empty($_GET['term'])) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $metodos = $this->model->searchByTerm($_GET['term']);
        
        header('Content-Type: application/json');
        echo json_encode($metodos);
        exit;
    }
}
