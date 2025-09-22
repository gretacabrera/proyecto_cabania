<?php

namespace App\Controllers;

use App\Models\Periodo;
use App\Core\Validator;

class PeriodosController
{
    private $model;

    public function __construct()
    {
        $this->model = new Periodo();
    }

    /**
     * Mostrar listado de periodos
     */
    public function index()
    {
        // Verificar permisos
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';
        $estado = $_GET['estado'] ?? '';

        $filters = [
            'search' => $search,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'estado' => $estado
        ];

        $result = $this->model->search($filters, $page);
        $totalPages = $this->model->getTotalPages($filters);

        require_once 'app/Views/admin/configuracion/periodos/listado.php';
    }

    /**
     * Mostrar formulario para nuevo periodo
     */
    public function create()
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $errors = [];
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeInput($_POST);
            $errors = $this->validateInput($data);

            if (empty($errors)) {
                $success = $this->model->create([
                    'periodo_descripcion' => $data['periodo_descripcion'],
                    'periodo_fechainicio' => $data['periodo_fechainicio'],
                    'periodo_fechafin' => $data['periodo_fechafin'],
                    'periodo_estado' => 1
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Periodo creado exitosamente';
                    header('Location: /periodos');
                    return;
                } else {
                    $errors[] = 'Error al crear el periodo';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/periodos/formulario.php';
    }

    /**
     * Mostrar formulario para editar periodo
     */
    public function edit($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $periodo = $this->model->findById($id);
        if (!$periodo) {
            header('Location: /404');
            return;
        }

        $errors = [];
        $data = $periodo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_merge($periodo, $this->sanitizeInput($_POST));
            $errors = $this->validateInput($data, $id);

            if (empty($errors)) {
                $success = $this->model->update($id, [
                    'periodo_descripcion' => $data['periodo_descripcion'],
                    'periodo_fechainicio' => $data['periodo_fechainicio'],
                    'periodo_fechafin' => $data['periodo_fechafin']
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Periodo actualizado exitosamente';
                    header('Location: /periodos');
                    return;
                } else {
                    $errors[] = 'Error al actualizar el periodo';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/periodos/formulario.php';
    }

    /**
     * Eliminar periodo (baja lógica)
     */
    public function delete($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $periodo = $this->model->findById($id);
        if (!$periodo) {
            header('Location: /404');
            return;
        }

        if ($this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede eliminar el periodo porque está siendo utilizado en reservas o precios';
            header('Location: /periodos');
            return;
        }

        $success = $this->model->update($id, ['periodo_estado' => 0]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Periodo eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el periodo';
        }

        header('Location: /periodos');
    }

    /**
     * Restaurar periodo
     */
    public function restore($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $success = $this->model->update($id, ['periodo_estado' => 1]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Periodo restaurado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al restaurar el periodo';
        }

        header('Location: /periodos');
    }

    /**
     * Cambiar estado (alta/baja lógica)
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $periodo = $this->model->findById($id);
        if (!$periodo) {
            header('Location: /404');
            return;
        }

        // Si está activo y se quiere desactivar, verificar que no esté en uso
        if ($periodo['periodo_estado'] == 1 && $this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede desactivar el periodo porque está siendo utilizado en reservas o precios';
            header('Location: /periodos');
            return;
        }

        $success = $this->model->toggleStatus($id);
        $action = $periodo['periodo_estado'] == 1 ? 'desactivado' : 'activado';
        
        if ($success) {
            $_SESSION['success_message'] = "Periodo {$action} exitosamente";
        } else {
            $_SESSION['error_message'] = "Error al cambiar el estado del periodo";
        }

        header('Location: /periodos');
    }

    /**
     * Búsqueda con AJAX
     */
    public function search()
    {
        if (!$this->hasPermission()) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos']);
            return;
        }

        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';
        $estado = $_GET['estado'] ?? '';

        $filters = [
            'search' => $search,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'estado' => $estado
        ];

        $result = $this->model->search($filters, $page);
        $totalPages = $this->model->getTotalPages($filters);

        header('Content-Type: application/json');
        echo json_encode([
            'data' => $result['data'],
            'pagination' => $result['pagination'],
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Mostrar estadísticas
     */
    public function stats()
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $year = $_GET['year'] ?? date('Y');
        
        $periodosConCount = $this->model->getWithReservasCount();
        $monthlyStats = $this->model->getMonthlyStats($year);
        $averageDuration = $this->model->getAverageDuration();
        $periodoActual = $this->model->getPeriodoActual();

        require_once 'app/Views/admin/configuracion/periodos/stats.php';
    }

    /**
     * Verificar permisos
     */
    private function hasPermission()
    {
        return isset($_SESSION['usuario_logueado']) && 
               (in_array($_SESSION['usuario_perfil'], [1, 2]) || 
                in_array('periodos', $_SESSION['permisos'] ?? []));
    }

    /**
     * Sanitizar datos de entrada
     */
    private function sanitizeInput($data)
    {
        return [
            'periodo_descripcion' => trim($data['periodo_descripcion'] ?? ''),
            'periodo_fechainicio' => trim($data['periodo_fechainicio'] ?? ''),
            'periodo_fechafin' => trim($data['periodo_fechafin'] ?? '')
        ];
    }

    /**
     * Validar datos de entrada
     */
    private function validateInput($data, $excludeId = null)
    {
        $validator = new Validator();
        $errors = [];

        // Validar descripción
        if (empty($data['periodo_descripcion'])) {
            $errors[] = 'La descripción del periodo es obligatoria';
        } elseif (strlen($data['periodo_descripcion']) > 100) {
            $errors[] = 'La descripción no puede superar los 100 caracteres';
        }

        // Validar fecha de inicio
        if (empty($data['periodo_fechainicio'])) {
            $errors[] = 'La fecha de inicio es obligatoria';
        } elseif (!$this->isValidDate($data['periodo_fechainicio'])) {
            $errors[] = 'La fecha de inicio no tiene un formato válido';
        }

        // Validar fecha de fin
        if (empty($data['periodo_fechafin'])) {
            $errors[] = 'La fecha de fin es obligatoria';
        } elseif (!$this->isValidDate($data['periodo_fechafin'])) {
            $errors[] = 'La fecha de fin no tiene un formato válido';
        }

        // Validar que fecha fin sea posterior a fecha inicio
        if (!empty($data['periodo_fechainicio']) && !empty($data['periodo_fechafin'])) {
            if (strtotime($data['periodo_fechafin']) <= strtotime($data['periodo_fechainicio'])) {
                $errors[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }

        // Verificar solapamiento de fechas
        if (empty($errors) && !empty($data['periodo_fechainicio']) && !empty($data['periodo_fechafin'])) {
            if ($this->model->checkDateOverlap($data['periodo_fechainicio'], $data['periodo_fechafin'], $excludeId)) {
                $errors[] = 'Las fechas se solapan con otro periodo existente';
            }
        }

        return $errors;
    }

    /**
     * Validar formato de fecha
     */
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
