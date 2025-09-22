<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cabania;

/**
 * Controlador para el manejo de cabañas
 */
class CabaniasController extends Controller
{
    protected $cabaniaModel;

    public function __construct()
    {
        parent::__construct();
        $this->cabaniaModel = new Cabania();
    }

    /**
     * Listar cabañas
     */
    public function index()
    {
        $this->requirePermission('cabanias');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'cabania_codigo' => $this->get('cabania_codigo'),
            'cabania_nombre' => $this->get('cabania_nombre'),
            'cabania_ubicacion' => $this->get('cabania_ubicacion'),
            'cabania_capacidad' => $this->get('cabania_capacidad'),
            'cabania_habitaciones' => $this->get('cabania_habitaciones'),
            'cabania_banios' => $this->get('cabania_banios'),
            'cabania_estado' => $this->get('cabania_estado')
        ];

        $result = $this->cabaniaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Cabañas',
            'cabanias' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva cabaña
     */
    public function create()
    {
        $this->requirePermission('cabanias');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Cabaña',
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/formulario', $data, 'main');
    }

    /**
     * Guardar nueva cabaña
     */
    public function store()
    {
        $this->requirePermission('cabanias');

        // Manejar subida de foto
        $cabania_foto = null;
        if (isset($_FILES['cabania_foto']) && $_FILES['cabania_foto']['error'] == 0) {
            $target_dir = "imagenes/cabanias/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["cabania_foto"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["cabania_foto"]["tmp_name"], $target_file)) {
                $cabania_foto = $new_filename;
            }
        }

        $data = [
            'cabania_codigo' => $this->post('cabania_codigo'),
            'cabania_nombre' => $this->post('cabania_nombre'),
            'cabania_descripcion' => $this->post('cabania_descripcion'),
            'cabania_capacidad' => $this->post('cabania_capacidad'),
            'cabania_precio' => $this->post('cabania_precio'),
            'cabania_ubicacion' => $this->post('cabania_ubicacion'),
            'cabania_cantidadbanios' => $this->post('cabania_cantidadbanios'),
            'cabania_cantidadhabitaciones' => $this->post('cabania_cantidadhabitaciones'),
            'cabania_foto' => $cabania_foto,
            'cabania_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['cabania_codigo']) || empty($data['cabania_nombre'])) {
            $this->redirect('/admin/cabanias/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->cabaniaModel->create($data);
            if ($id) {
                $this->redirect('/cabanias', 'Cabaña creada correctamente', 'exito');
            } else {
                $this->redirect('/cabanias/create', 'Error al crear la cabaña', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/cabanias/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar cabaña específica
     */
    public function show($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la cabaña
        $estadisticas = $this->cabaniaModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Cabaña',
            'cabania' => $cabania,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $data = [
            'title' => 'Editar Cabaña',
            'cabania' => $cabania,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cabanias/formulario', $data, 'main');
    }

    /**
     * Actualizar cabaña
     */
    public function update($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        // Manejar subida de foto
        $cabania_foto = $cabania['cabania_foto']; // Mantener foto actual por defecto
        if (isset($_FILES['cabania_foto']) && $_FILES['cabania_foto']['error'] == 0) {
            $target_dir = "imagenes/cabanias/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["cabania_foto"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["cabania_foto"]["tmp_name"], $target_file)) {
                // Eliminar foto anterior si existe
                if ($cabania['cabania_foto'] && file_exists($target_dir . $cabania['cabania_foto'])) {
                    unlink($target_dir . $cabania['cabania_foto']);
                }
                $cabania_foto = $new_filename;
            }
        }

        $data = [
            'cabania_codigo' => $this->post('cabania_codigo'),
            'cabania_nombre' => $this->post('cabania_nombre'),
            'cabania_descripcion' => $this->post('cabania_descripcion'),
            'cabania_capacidad' => $this->post('cabania_capacidad'),
            'cabania_precio' => $this->post('cabania_precio'),
            'cabania_ubicacion' => $this->post('cabania_ubicacion'),
            'cabania_cantidadbanios' => $this->post('cabania_cantidadbanios'),
            'cabania_cantidadhabitaciones' => $this->post('cabania_cantidadhabitaciones'),
            'cabania_foto' => $cabania_foto
        ];

        if (empty($data['cabania_codigo']) || empty($data['cabania_nombre'])) {
            $this->redirect("/cabanias/$id/edit", 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->cabaniaModel->update($id, $data)) {
                $this->redirect('/cabanias', 'Cabaña actualizada correctamente', 'exito');
            } else {
                $this->redirect("/cabanias/$id/edit", 'Error al actualizar la cabaña', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/cabanias/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de cabaña
     */
    public function delete($id)
    {
        $this->requirePermission('cabanias');

        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            return $this->view->error(404);
        }

        if ($this->cabaniaModel->softDelete($id, 'cabania_estado')) {
            $this->redirect('/cabanias', 'Cabaña eliminada correctamente', 'exito');
        } else {
            $this->redirect('/cabanias', 'Error al eliminar la cabaña', 'error');
        }
    }

    /**
     * Restaurar cabaña
     */
    public function restore($id)
    {
        $this->requirePermission('cabanias');

        if ($this->cabaniaModel->restore($id, 'cabania_estado')) {
            $this->redirect('/cabanias', 'Cabaña restaurada correctamente', 'exito');
        } else {
            $this->redirect('/cabanias', 'Error al restaurar la cabaña', 'error');
        }
    }

    /**
     * Verificar disponibilidad (AJAX)
     */
    public function checkAvailability()
    {
        if (!$this->isAjax()) {
            return $this->view->error(404);
        }

        $cabaniaId = $this->post('cabania_id');
        $fechaInicio = $this->post('fecha_inicio');
        $fechaFin = $this->post('fecha_fin');

        if (!$cabaniaId || !$fechaInicio || !$fechaFin) {
            return $this->json(['error' => 'Faltan parámetros'], 400);
        }

        $available = $this->cabaniaModel->checkAvailability($cabaniaId, $fechaInicio, $fechaFin);

        return $this->json([
            'available' => $available,
            'message' => $available ? 'Cabaña disponible' : 'Cabaña no disponible para las fechas seleccionadas'
        ]);
    }

    /**
     * Cambiar estado de cabaña (AJAX)
     */
    public function cambiarEstado($id)
    {
        // Log para debugging
        error_log("Petición recibida en cambiarEstado - ID: $id");
        error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('cabanias');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            error_log("Error: No es una petición AJAX");
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que la cabaña existe
        $cabania = $this->cabaniaModel->find($id);
        if (!$cabania) {
            error_log("Error: Cabaña no encontrada - ID: $id");
            return $this->json(['success' => false, 'message' => 'Cabaña no encontrada'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("Datos recibidos: " . json_encode($input));
        
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1, 2])) {
            error_log("Error: Estado inválido - Estado: " . var_export($nuevoEstado, true));
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactiva), 1 (activa), 2 (ocupada)'], 400);
        }

        // Actualizar el estado
        $data = ['cabania_estado' => $nuevoEstado];
        $resultado = $this->cabaniaModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactiva', 'activa', 'ocupada'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizada';
            error_log("Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
            return $this->json([
                'success' => true, 
                'message' => "Cabaña marcada como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            error_log("Error al actualizar el estado en la base de datos - ID: $id");
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado de la cabaña'
            ], 500);
        }
    }
}
