<?php

namespace App\Controllers;

use App\Models\TipoContacto;
use App\Core\Validator;

class TiposContactosController
{
    private $model;

    public function __construct()
    {
        $this->model = new TipoContacto();
    }

    /**
     * Mostrar listado de tipos de contactos
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

        require_once 'app/Views/admin/configuracion/tipos_contactos/listado.php';
    }

    /**
     * Mostrar formulario para nuevo tipo de contacto
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
                    'tipocontacto_descripcion' => $data['tipocontacto_descripcion'],
                    'tipocontacto_estado' => 1
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Tipo de contacto creado exitosamente';
                    header('Location: /tipos-contactos');
                    return;
                } else {
                    $errors[] = 'Error al crear el tipo de contacto';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/tipos_contactos/formulario.php';
    }

    /**
     * Mostrar formulario para editar tipo de contacto
     */
    public function edit($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $tipoContacto = $this->model->findById($id);
        if (!$tipoContacto) {
            header('Location: /404');
            return;
        }

        $errors = [];
        $data = $tipoContacto;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_merge($tipoContacto, $this->sanitizeInput($_POST));
            $errors = $this->validateInput($data);

            if (empty($errors)) {
                $success = $this->model->update($id, [
                    'tipocontacto_descripcion' => $data['tipocontacto_descripcion']
                ]);

                if ($success) {
                    $_SESSION['success_message'] = 'Tipo de contacto actualizado exitosamente';
                    header('Location: /tipos-contactos');
                    return;
                } else {
                    $errors[] = 'Error al actualizar el tipo de contacto';
                }
            }
        }

        require_once 'app/Views/admin/configuracion/tipos_contactos/formulario.php';
    }

    /**
     * Eliminar tipo de contacto (baja lógica)
     */
    public function delete($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $tipoContacto = $this->model->findById($id);
        if (!$tipoContacto) {
            header('Location: /404');
            return;
        }

        if ($this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede eliminar el tipo de contacto porque está siendo utilizado en personas o contactos';
            header('Location: /tipos-contactos');
            return;
        }

        $success = $this->model->update($id, ['tipocontacto_estado' => 0]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Tipo de contacto eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el tipo de contacto';
        }

        header('Location: /tipos-contactos');
    }

    /**
     * Restaurar tipo de contacto
     */
    public function restore($id)
    {
        if (!$this->hasPermission()) {
            header('Location: /403');
            return;
        }

        $success = $this->model->update($id, ['tipocontacto_estado' => 1]);
        
        if ($success) {
            $_SESSION['success_message'] = 'Tipo de contacto restaurado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al restaurar el tipo de contacto';
        }

        header('Location: /tipos-contactos');
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

        $tipoContacto = $this->model->findById($id);
        if (!$tipoContacto) {
            header('Location: /404');
            return;
        }

        // Si está activo y se quiere desactivar, verificar que no esté en uso
        if ($tipoContacto['tipocontacto_estado'] == 1 && $this->model->isInUse($id)) {
            $_SESSION['error_message'] = 'No se puede desactivar el tipo de contacto porque está siendo utilizado en personas o contactos';
            header('Location: /tipos-contactos');
            return;
        }

        $success = $this->model->toggleStatus($id);
        $action = $tipoContacto['tipocontacto_estado'] == 1 ? 'desactivado' : 'activado';
        
        if ($success) {
            $_SESSION['success_message'] = "Tipo de contacto {$action} exitosamente";
        } else {
            $_SESSION['error_message'] = "Error al cambiar el estado del tipo de contacto";
        }

        header('Location: /tipos-contactos');
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
        
        $tiposConCount = $this->model->getWithUsageCount();
        $monthlyStats = $this->model->getMonthlyStats($year);
        $mostUsed = $this->model->getMostUsed(10);
        $usageSummary = $this->model->getUsageSummary();

        require_once 'app/Views/admin/configuracion/tipos_contactos/stats.php';
    }

    /**
     * Verificar permisos
     */
    private function hasPermission()
    {
        return isset($_SESSION['usuario_logueado']) && 
               (in_array($_SESSION['usuario_perfil'], [1, 2]) || 
                in_array('tipos_contactos', $_SESSION['permisos'] ?? []));
    }

    /**
     * Sanitizar datos de entrada
     */
    private function sanitizeInput($data)
    {
        return [
            'tipocontacto_descripcion' => trim($data['tipocontacto_descripcion'] ?? '')
        ];
    }

    /**
     * Validar datos de entrada
     */
    private function validateInput($data)
    {
        $validator = new Validator();
        $errors = [];

        if (empty($data['tipocontacto_descripcion'])) {
            $errors[] = 'La descripción del tipo de contacto es obligatoria';
        } elseif (strlen($data['tipocontacto_descripcion']) > 100) {
            $errors[] = 'La descripción no puede superar los 100 caracteres';
        }

        return $errors;
    }
}
