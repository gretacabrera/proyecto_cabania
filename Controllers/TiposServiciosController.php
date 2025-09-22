<?php

namespace App\Controllers;

use App\Models\TipoServicio;
use App\Core\Validator;

class TiposServiciosController
{
    private $model;

    public function __construct()
    {
        $this->model = new TipoServicio();
    }

    /**
     * Mostrar listado de tipos de servicios
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
        $estado = $_GET['estado'] ?? '';

        $filters = [
            'search' => $search,
            'estado' => $estado
        ];

        $result = $this->model->search($filters, $page);
        $totalPages = $this->model->getTotalPages($filters);

        require_once 'app/Views/admin/configuracion/tipos_servicios/listado.php';
    }

    /**
     * Mostrar formulario para nuevo tipo de servicio
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
                    'tiposervicio_descripcion' => $data['tiposervicio_descripcion'],
                    'tiposervicio_estado' => 1
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Tipo de servicio creado exitosamente';
                    header('Location: /tipos-servicios');
                    return;
                } else {
                    $errors[] = 'Error al crear el tipo de servicio';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/tipos_servicios/formulario.php';
    }

    /**
     * Mostrar formulario para editar tipo de servicio
     */
    public function edit($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $tipoServicio = $this->model->findById($id);
        if (!$tipoServicio) {
            header('Location: /404');
            return;
        }

        $errors = [];
        $data = $tipoServicio;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_merge($tipoServicio, $this->sanitizeInput($_POST));
            $errors = $this->validateInput($data);

            if (empty($errors)) {
                $success = $this->model->update($id, [
                    'tiposervicio_descripcion' => $data['tiposervicio_descripcion']
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Tipo de servicio actualizado exitosamente';
                    header('Location: /tipos-servicios');
                    return;
                } else {
                    $errors[] = 'Error al actualizar el tipo de servicio';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/tipos_servicios/formulario.php';
    }

    /**
     * Eliminar tipo de servicio (baja lógica)
     */
    public function delete($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $tipoServicio = $this->model->findById($id);
        if (!$tipoServicio) {
            header('Location: /404');
            return;
        }

        if ($this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede eliminar el tipo de servicio porque está siendo utilizado en servicios';
            header('Location: /tipos-servicios');
            return;
        }

        $success = $this->model->update($id, ['tiposervicio_estado' => 0]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Tipo de servicio eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el tipo de servicio';
        }

        header('Location: /tipos-servicios');
    }

    /**
     * Restaurar tipo de servicio
     */
    public function restore($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $success = $this->model->update($id, ['tiposervicio_estado' => 1]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Tipo de servicio restaurado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al restaurar el tipo de servicio';
        }

        header('Location: /tipos-servicios');
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

        $tipoServicio = $this->model->findById($id);
        if (!$tipoServicio) {
            header('Location: /404');
            return;
        }

        // Si está activo y se quiere desactivar, verificar que no esté en uso
        if ($tipoServicio['tiposervicio_estado'] == 1 && $this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede desactivar el tipo de servicio porque está siendo utilizado en servicios';
            header('Location: /tipos-servicios');
            return;
        }

        $success = $this->model->toggleStatus($id);
        $action = $tipoServicio['tiposervicio_estado'] == 1 ? 'desactivado' : 'activado';
        
        if ($success) {
            $_SESSION['success_message'] = "Tipo de servicio {$action} exitosamente";
        } else {
            $_SESSION['error_message'] = "Error al cambiar el estado del tipo de servicio";
        }

        header('Location: /tipos-servicios');
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
        $estado = $_GET['estado'] ?? '';

        $filters = [
            'search' => $search,
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
        
        $tiposConCount = $this->model->getWithServiciosCount();
        $monthlyStats = $this->model->getMonthlyStats($year);
        $mostUsed = $this->model->getMostUsed(10);

        require_once 'app/Views/admin/configuracion/tipos_servicios/stats.php';
    }

    /**
     * Verificar permisos
     */
    private function hasPermission()
    {
        return isset($_SESSION['usuario_logueado']) && 
               (in_array($_SESSION['usuario_perfil'], [1, 2]) || 
                in_array('tipos_servicios', $_SESSION['permisos'] ?? []));
    }

    /**
     * Sanitizar datos de entrada
     */
    private function sanitizeInput($data)
    {
        return [
            'tiposervicio_descripcion' => trim($data['tiposervicio_descripcion'] ?? '')
        ];
    }

    /**
     * Validar datos de entrada
     */
    private function validateInput($data)
    {
        $validator = new Validator();
        $errors = [];

        if (empty($data['tiposervicio_descripcion'])) {
            $errors[] = 'La descripción del tipo de servicio es obligatoria';
        } elseif (strlen($data['tiposervicio_descripcion']) > 100) {
            $errors[] = 'La descripción no puede superar los 100 caracteres';
        }

        return $errors;
    }
}
