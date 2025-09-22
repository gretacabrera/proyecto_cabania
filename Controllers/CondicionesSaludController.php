<?php

namespace App\Controllers;

require_once 'app/Models/CondicionSalud.php';

use App\Models\CondicionSalud;

class CondicionesSaludController
{
    private $condicionSaludModel;

    public function __construct()
    {
        $this->condicionSaludModel = new CondicionSalud();
    }

    /**
     * Mostrar listado de condiciones de salud
     */
    public function index()
    {
        $filters = [
            'condicionsalud_descripcion' => $_GET['condicionsalud_descripcion'] ?? '',
            'condicionsalud_estado' => isset($_GET['condicionsalud_estado']) ? $_GET['condicionsalud_estado'] : ''
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 10);
        $orderBy = $_GET['order_by'] ?? 'condicionsalud_descripcion';
        $orderDir = $_GET['order_dir'] ?? 'ASC';

        $result = $this->condicionSaludModel->getWithFilters($filters, $page, $limit, $orderBy, $orderDir);

        $data = [
            'condiciones' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'per_page' => $result['per_page'],
                'total_records' => $result['total']
            ],
            'filters' => $filters,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ];

        $this->render('admin/configuracion/condiciones_salud/listado', $data);
    }

    /**
     * Mostrar formulario para nueva condición
     */
    public function create()
    {
        $data = [
            'condicionsalud_descripcion' => '',
            'condicionsalud_estado' => 1
        ];

        $this->render('admin/configuracion/condiciones_salud/formulario', ['data' => $data]);
    }

    /**
     * Procesar creación de nueva condición
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /condiciones-salud');
            exit;
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validateInput($data);

        if (empty($errors)) {
            $data['condicionsalud_estado'] = 1; // Nueva condición siempre activa
            $sanitizedData = $this->condicionSaludModel->sanitizeData($data);

            if ($this->condicionSaludModel->create($sanitizedData)) {
                $_SESSION['flash_message'] = 'Condición de salud creada exitosamente.';
                $_SESSION['flash_type'] = 'success';
                header('Location: /condiciones-salud');
                exit;
            } else {
                $errors[] = 'Error al crear la condición de salud. Por favor, intente nuevamente.';
            }
        }

        $this->render('admin/configuracion/condiciones_salud/formulario', [
            'data' => $data,
            'errors' => $errors
        ]);
    }

    /**
     * Mostrar formulario para editar condición
     */
    public function edit($id)
    {
        $condicion = $this->condicionSaludModel->find($id);
        
        if (!$condicion) {
            $_SESSION['flash_message'] = 'Condición de salud no encontrada.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /condiciones-salud');
            exit;
        }

        $this->render('admin/configuracion/condiciones_salud/formulario', ['data' => $condicion]);
    }

    /**
     * Procesar actualización de condición
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /condiciones-salud');
            exit;
        }

        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            $_SESSION['flash_message'] = 'Condición de salud no encontrada.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /condiciones-salud');
            exit;
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validateInput($data, true, $id);

        if (empty($errors)) {
            $sanitizedData = $this->condicionSaludModel->sanitizeData($data);

            if ($this->condicionSaludModel->update($id, $sanitizedData)) {
                $_SESSION['flash_message'] = 'Condición de salud actualizada exitosamente.';
                $_SESSION['flash_type'] = 'success';
                header('Location: /condiciones-salud');
                exit;
            } else {
                $errors[] = 'Error al actualizar la condición de salud. Por favor, intente nuevamente.';
            }
        }

        $data['id_condicionsalud'] = $id;
        $this->render('admin/configuracion/condiciones_salud/formulario', [
            'data' => $data,
            'errors' => $errors
        ]);
    }

    /**
     * Cambiar estado de condición (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            $_SESSION['flash_message'] = 'Condición de salud no encontrada.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /condiciones-salud');
            exit;
        }

        // Verificar si está en uso antes de desactivar
        if ($condicion['condicionsalud_estado'] == 1 && $this->condicionSaludModel->isInUse($id)) {
            $_SESSION['flash_message'] = 'No se puede desactivar una condición que está siendo utilizada por huéspedes.';
            $_SESSION['flash_type'] = 'warning';
        } else {
            $newStatus = $condicion['condicionsalud_estado'] == 1 ? 0 : 1;
            $statusText = $newStatus == 1 ? 'activada' : 'desactivada';

            if ($this->condicionSaludModel->update($id, ['condicionsalud_estado' => $newStatus])) {
                $_SESSION['flash_message'] = "Condición de salud {$statusText} exitosamente.";
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Error al cambiar el estado de la condición.';
                $_SESSION['flash_type'] = 'error';
            }
        }

        header('Location: /condiciones-salud');
        exit;
    }

    /**
     * Mostrar estadísticas de condiciones de salud
     */
    public function stats()
    {
        $stats = $this->condicionSaludModel->getStats();
        $condicionesCriticas = $this->condicionSaludModel->getCondicionesCriticas();
        $agrupadas = $this->condicionSaludModel->getGroupedByLetter();

        // Obtener todas las condiciones para estadísticas detalladas
        $todasCondiciones = $this->condicionSaludModel->getWithFilters([], 1, 1000)['data'];

        $data = [
            'stats' => $stats,
            'condiciones_criticas' => $condicionesCriticas,
            'agrupadas_por_letra' => $agrupadas,
            'condiciones_detalle' => $todasCondiciones
        ];

        $this->render('admin/configuracion/condiciones_salud/stats', $data);
    }

    /**
     * Búsqueda AJAX de condiciones
     */
    public function search()
    {
        $term = $_GET['q'] ?? '';
        $limit = (int) ($_GET['limit'] ?? 10);

        if (strlen($term) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $resultados = $this->condicionSaludModel->search($term, $limit);

        header('Content-Type: application/json');
        echo json_encode($resultados);
        exit;
    }

    /**
     * Ver detalles de una condición y sus huéspedes asociados
     */
    public function show($id)
    {
        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            $_SESSION['flash_message'] = 'Condición de salud no encontrada.';
            $_SESSION['flash_type'] = 'error';
            header('Location: /condiciones-salud');
            exit;
        }

        $huespedes = $this->condicionSaludModel->getHuespedes($id);

        $data = [
            'condicion' => $condicion,
            'huespedes' => $huespedes
        ];

        $this->render('admin/configuracion/condiciones_salud/detalle', $data);
    }

    /**
     * Validar datos de entrada
     */
    private function validateInput($data, $isUpdate = false, $id = null)
    {
        return $this->condicionSaludModel->validate($data, $isUpdate, $id);
    }

    /**
     * Sanitizar datos de entrada
     */
    private function sanitizeInput($data)
    {
        $sanitized = [];

        // Descripción
        if (isset($data['condicionsalud_descripcion'])) {
            $sanitized['condicionsalud_descripcion'] = trim($data['condicionsalud_descripcion']);
            $sanitized['condicionsalud_descripcion'] = filter_var(
                $sanitized['condicionsalud_descripcion'], 
                FILTER_SANITIZE_STRING, 
                FILTER_FLAG_NO_ENCODE_QUOTES
            );
        }

        // Estado
        if (isset($data['condicionsalud_estado'])) {
            $sanitized['condicionsalud_estado'] = (int) $data['condicionsalud_estado'];
        }

        return $sanitized;
    }

    /**
     * Renderizar vista
     */
    private function render($view, $data = [])
    {
        // Verificar permisos (implementar según sistema de permisos)
        if (!$this->checkPermissions()) {
            header('HTTP/1.1 403 Forbidden');
            include 'app/Views/errors/403.php';
            exit;
        }

        // Extraer datos para usar en la vista
        extract($data);

        // Incluir vista
        $viewPath = "app/Views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Vista no encontrada
            header('HTTP/1.1 404 Not Found');
            include 'app/Views/errors/404.php';
            exit;
        }
    }

    /**
     * Verificar permisos del usuario
     */
    private function checkPermissions()
    {
        // Implementar verificación de permisos según el sistema
        // Por ahora retorna true, pero debería verificar sesión y permisos
        return isset($_SESSION) && !empty($_SESSION);
    }

    /**
     * Obtener condiciones críticas para alertas
     */
    public function getCriticas()
    {
        $condicionesCriticas = $this->condicionSaludModel->getCondicionesCriticas();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $condicionesCriticas,
            'count' => count($condicionesCriticas)
        ]);
        exit;
    }

    /**
     * Exportar condiciones a formato CSV
     */
    public function export()
    {
        $condiciones = $this->condicionSaludModel->getWithFilters([], 1, 1000)['data'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="condiciones_salud_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // Encabezados
        fputcsv($output, ['ID', 'Descripción', 'Estado', 'Estado Texto']);

        // Datos
        foreach ($condiciones as $condicion) {
            fputcsv($output, [
                $condicion['id_condicionsalud'],
                $condicion['condicionsalud_descripcion'],
                $condicion['condicionsalud_estado'],
                $condicion['condicionsalud_estado'] == 1 ? 'Activo' : 'Inactivo'
            ]);
        }

        fclose($output);
        exit;
    }
}
