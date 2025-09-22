<?php

namespace App\Controllers;

use App\Models\EstadoReserva;
use App\Core\Validator;

class EstadosReservasController
{
    private $model;

    public function __construct()
    {
        $this->model = new EstadoReserva();
    }

    /**
     * Mostrar listado de estados de reservas
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

        require_once 'app/Views/admin/configuracion/estados_reservas/listado.php';
    }

    /**
     * Mostrar formulario para nuevo estado de reserva
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
                    'estadoreserva_descripcion' => $data['estadoreserva_descripcion'],
                    'estadoreserva_estado' => 1
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Estado de reserva creado exitosamente';
                    header('Location: /estados-reservas');
                    return;
                } else {
                    $errors[] = 'Error al crear el estado de reserva';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/estados_reservas/formulario.php';
    }

    /**
     * Mostrar formulario para editar estado de reserva
     */
    public function edit($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $estadoReserva = $this->model->findById($id);
        if (!$estadoReserva) {
            header('Location: /404');
            return;
        }

        $errors = [];
        $data = $estadoReserva;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_merge($estadoReserva, $this->sanitizeInput($_POST));
            $errors = $this->validateInput($data);

            if (empty($errors)) {
                $success = $this->model->update($id, [
                    'estadoreserva_descripcion' => $data['estadoreserva_descripcion']
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Estado de reserva actualizado exitosamente';
                    header('Location: /estados-reservas');
                    return;
                } else {
                    $errors[] = 'Error al actualizar el estado de reserva';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/estados_reservas/formulario.php';
    }

    /**
     * Eliminar estado de reserva (baja lógica)
     */
    public function delete($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $estadoReserva = $this->model->findById($id);
        if (!$estadoReserva) {
            header('Location: /404');
            return;
        }

        if ($this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede eliminar el estado de reserva porque está siendo utilizado en reservas';
            header('Location: /estados-reservas');
            return;
        }

        $success = $this->model->update($id, ['estadoreserva_estado' => 0]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Estado de reserva eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el estado de reserva';
        }

        header('Location: /estados-reservas');
    }

    /**
     * Restaurar estado de reserva
     */
    public function restore($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $success = $this->model->update($id, ['estadoreserva_estado' => 1]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Estado de reserva restaurado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al restaurar el estado de reserva';
        }

        header('Location: /estados-reservas');
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

        $estadoReserva = $this->model->findById($id);
        if (!$estadoReserva) {
            header('Location: /404');
            return;
        }

        // Si está activo y se quiere desactivar, verificar que no esté en uso
        if ($estadoReserva['estadoreserva_estado'] == 1 && $this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede desactivar el estado de reserva porque está siendo utilizado en reservas';
            header('Location: /estados-reservas');
            return;
        }

        $success = $this->model->toggleStatus($id);
        $action = $estadoReserva['estadoreserva_estado'] == 1 ? 'desactivado' : 'activado';
        
        if ($success) {
            $_SESSION['success_message'] = "Estado de reserva {$action} exitosamente";
        } else {
            $_SESSION['error_message'] = "Error al cambiar el estado del estado de reserva";
        }

        header('Location: /estados-reservas');
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
        
        $estadosConCount = $this->model->getWithReservaCount();
        $monthlyStats = $this->model->getMonthlyStats($year);

        require_once 'app/Views/admin/configuracion/estados_reservas/stats.php';
    }

    /**
     * Verificar permisos
     */
    private function hasPermission()
    {
        return isset($_SESSION['usuario_logueado']) && 
               (in_array($_SESSION['usuario_perfil'], [1, 2]) || 
                in_array('estados_reservas', $_SESSION['permisos'] ?? []));
    }

    /**
     * Sanitizar datos de entrada
     */
    private function sanitizeInput($data)
    {
        return [
            'estadoreserva_descripcion' => trim($data['estadoreserva_descripcion'] ?? '')
        ];
    }

    /**
     * Validar datos de entrada
     */
    private function validateInput($data)
    {
        $validator = new Validator();
        $errors = [];

        if (empty($data['estadoreserva_descripcion'])) {
            $errors[] = 'La descripción del estado de reserva es obligatoria';
        } elseif (strlen($data['estadoreserva_descripcion']) > 100) {
            $errors[] = 'La descripción no puede superar los 100 caracteres';
        }

        return $errors;
    }
}
