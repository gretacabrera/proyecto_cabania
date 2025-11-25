<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Huesped;
use App\Models\Persona;
use App\Models\Ubicacion;

/**
 * Controlador para el manejo de huéspedes
 */
class HuespedesController extends Controller
{
    protected $huespedModel;
    protected $personaModel;
    protected $ubicacionModel;

    public function __construct()
    {
        parent::__construct();
        $this->huespedModel = new Huesped();
        $this->personaModel = new Persona();
        $this->ubicacionModel = new Ubicacion();
    }

    /**
     * Listar huéspedes del usuario actual (para vistas públicas)
     * Si recibe reserva_id, filtra por esa reserva específica
     */
    public function index()
    {
        // Verificar autenticación para huéspedes públicos
        if (!\App\Core\Auth::check()) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para ver los huéspedes', 'error');
            return;
        }
        
        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para ver los huéspedes', 'error');
            return;
        }
        
        $reservaId = $this->get('reserva_id');
        
        // Validar que se haya proporcionado reserva_id
        if (!$reservaId) {
            $this->redirect('/mis-reservas', 'Debe seleccionar una reserva', 'error');
            return;
        }
        
        // Obtener información de la reserva
        $reservaModel = new \App\Models\Reserva();
        $reserva = $reservaModel->find($reservaId);
        
        if (!$reserva) {
            $this->redirect('/mis-reservas', 'Reserva no encontrada', 'error');
            return;
        }
        
        // Verificar que es el propietario de la reserva
        if (!$reservaModel->isReservaOwner($reservaId, $userId)) {
            $this->redirect('/mis-reservas', 'No tiene permisos para ver esta información', 'error');
            return;
        }
        
        // Obtener huéspedes de la reserva
        $huespedes = $this->huespedModel->getByReserva($reservaId);
        
        // Obtener ubicaciones para el select
        $ubicaciones = $this->ubicacionModel->getAll();
        
        // Obtener condiciones de salud
        $condicionesModel = new \App\Models\CondicionSalud();
        $condicionesSalud = $condicionesModel->getAll();

        $data = [
            'title' => 'Huéspedes de la Reserva #' . $reservaId,
            'huespedes' => $huespedes,
            'reserva_id' => $reservaId,
            'reserva' => $reserva,
            'ubicaciones' => $ubicaciones,
            'condicionesSalud' => $condicionesSalud,
            'isPublicArea' => true
        ];

        return $this->render('public/huespedes/listado', $data, 'main');
    }

    /**
     * Crear nuevo huésped (modo público con JSON response)
     */
    public function create()
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
                return;
            }
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }

        if ($this->isPost()) {
            return $this->store();
        }

        // Detectar si es área pública (tiene reserva_id en URL)
        $reservaId = $this->get('reserva_id');
        $isPublicArea = !empty($reservaId);

        // Obtener condiciones de salud activas
        $condicionSaludModel = new \App\Models\CondicionSalud();
        $condicionesSalud = $condicionSaludModel->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");

        // Obtener ubicaciones
        $ubicaciones = $this->ubicacionModel->getAll();

        if ($isPublicArea) {
            // Verificar que la reserva existe y pertenece al usuario
            $userId = $_SESSION['usuario_id'] ?? null;
            $reservaModel = new \App\Models\Reserva();
            $reserva = $reservaModel->find($reservaId);
            
            if (!$reserva || !$reservaModel->isReservaOwner($reservaId, $userId)) {
                $this->redirect('/mis-reservas', 'No tiene permisos para esta reserva', 'error');
                return;
            }

            $data = [
                'title' => 'Nuevo Huésped',
                'condicionesSalud' => $condicionesSalud,
                'ubicaciones' => $ubicaciones,
                'reserva_id' => $reservaId,
                'huesped' => null,
                'isEdit' => false,
                'isPublicArea' => true
            ];

            return $this->render('public/huespedes/formulario', $data, 'main');
        } else {
            // Área de administración
            $reservaModel = new \App\Models\Reserva();
            $reservas = $reservaModel->findAll("reserva_fhfin > NOW()", "reserva_fhfin ASC");

            $data = [
                'title' => 'Nuevo Huésped',
                'condicionesSalud' => $condicionesSalud,
                'reservas' => $reservas,
                'ubicaciones' => $ubicaciones,
                'huesped' => [],
                'isEdit' => false,
                'isAdminArea' => true
            ];

            return $this->render('admin/operaciones/huespedes/formulario', $data, 'main');
        }
    }

    /**
     * Guardar nuevo huésped (persona + huésped + condiciones de salud + reserva en transacción)
     */
    public function store()
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        // Datos de persona física
        $dni = $this->post('persona_dni');
        $nombre = $this->post('persona_nombre');
        $apellido = $this->post('persona_apellido');
        $fechaNac = $this->post('persona_fechanac');
        $direccion = $this->post('persona_direccion');
        $ubicacion = $this->post('rela_ubicacion');

        // Condiciones de salud seleccionadas
        $condicionesSeleccionadas = $this->post('condiciones_salud', []);
        
        // Reserva (automática desde URL)
        $idReserva = $this->post('rela_reserva');

        // Validaciones
        if (empty($dni)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El DNI es obligatorio']);
            return;
        }
        if (empty($nombre)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }
        if (empty($apellido)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El apellido es obligatorio']);
            return;
        }
        if (empty($fechaNac)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'La fecha de nacimiento es obligatoria']);
            return;
        }
        if (empty($direccion)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'La dirección es obligatoria']);
            return;
        }

        // Validar DNI duplicado
        $personaFisicaModel = new \App\Models\PersonaFisica();
        $dniExistente = $personaFisicaModel->findWhere('personafisica_dni = ?', [$dni]);
        if ($dniExistente) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ya existe un huésped con este DNI']);
            return;
        }

        // Datos de persona
        $personaData = [
            'persona_direccion' => $direccion,
            'rela_estadopersona' => 1
        ];

        try {
            // Iniciar transacción
            $this->huespedModel->beginTransaction();

            // 1. Crear persona
            $idPersona = $this->personaModel->create($personaData);
            if (!$idPersona) {
                throw new \Exception('Error al crear la persona');
            }

            // 2. Crear persona física
            $personaFisicaModel = new \App\Models\PersonaFisica();
            $personaFisicaData = [
                'rela_persona' => $idPersona,
                'personafisica_dni' => $dni,
                'personafisica_nombre' => $nombre,
                'personafisica_apellido' => $apellido,
                'personafisica_fechanac' => $fechaNac
            ];
            $idPersonaFisica = $personaFisicaModel->create($personaFisicaData);
            if (!$idPersonaFisica) {
                throw new \Exception('Error al crear la persona física');
            }

            // 3. Crear huésped
            $huespedData = [
                'rela_persona' => $idPersona,
                'rela_ubicacion' => $ubicacion ? (int)$ubicacion : null,
                'huesped_estado' => 1
            ];
            $idHuesped = $this->huespedModel->create($huespedData);
            if (!$idHuesped) {
                throw new \Exception('Error al crear el huésped');
            }

            // 4. Obtener TODAS las condiciones de salud activas
            $condicionSaludModel = new \App\Models\CondicionSalud();
            $todasCondiciones = $condicionSaludModel->findAll("condicionsalud_estado = 1");
            
            // 5. Guardar TODAS las condiciones con estado según selección
            if (!$this->huespedModel->saveCondicionesSalud($idHuesped, $todasCondiciones, $condicionesSeleccionadas)) {
                throw new \Exception('Error al asignar condiciones de salud');
            }

            // 6. Asociar reserva (obligatoria desde parámetro URL)
            if (!empty($idReserva)) {
                if (!$this->huespedModel->asociarReserva($idHuesped, $idReserva)) {
                    throw new \Exception('Error al asociar la reserva');
                }
            }

            // Commit de la transacción
            $this->huespedModel->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Huésped creado exitosamente']);
        } catch (\Exception $e) {
            // Rollback en caso de error
            $this->huespedModel->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al crear el huésped: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar huésped específico
     */
    public function show($id)
    {
        $this->requirePermission('huespedes');

        $huesped = $this->huespedModel->findWithPersona($id);
        if (!$huesped) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del huésped
        $estadisticas = $this->huespedModel->getStatistics($id);

        // Obtener condiciones de salud del huésped
        $condicionesHuesped = $this->huespedModel->getCondicionesSalud($id);
        
        // Cargar todas las condiciones de salud para mostrar las que tiene
        $condicionSaludModel = new \App\Models\CondicionSalud();
        $todasCondiciones = $condicionSaludModel->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");

        // Obtener reserva asociada (si tiene)
        $reservaAsociada = null;
        $reservaActualId = $this->huespedModel->getReservaAsociada($id);
        if ($reservaActualId) {
            $reservaModel = new \App\Models\Reserva();
            $reservaAsociada = $reservaModel->find($reservaActualId);
        }

        $data = [
            'title' => 'Detalle de Huésped',
            'huesped' => $huesped,
            'estadisticas' => $estadisticas,
            'condicionesHuesped' => $condicionesHuesped,
            'todasCondiciones' => $todasCondiciones,
            'reservaAsociada' => $reservaAsociada,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/huespedes/detalle', $data, 'main');
    }

    /**
     * Método AJAX para obtener datos del huésped para edición
     */
    public function editAjax($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        $huesped = $this->huespedModel->findWithPersona($id);
        if (!$huesped) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Huésped no encontrado']);
            return;
        }

        // Obtener DNI de PersonaFisica - ya viene en findWithPersona()
        // El DNI ya está disponible en $huesped['persona_dni']

        // Obtener condiciones de salud del huésped
        $condicionesIds = $this->huespedModel->getCondicionesSaludIds($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'huesped' => $huesped,
            'condiciones' => $condicionesIds
        ]);
    }

    /**
     * Actualizar huésped (modo público con JSON response)
     */
    public function edit($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        $huesped = $this->huespedModel->findWithPersona($id);
        if (!$huesped) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Huésped no encontrado']);
            return;
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Detectar si es área pública (tiene reserva_id en URL)
        $reservaId = $this->get('reserva_id');
        $isPublicArea = !empty($reservaId);

        // Obtener todas las condiciones de salud activas
        $condicionSaludModel = new \App\Models\CondicionSalud();
        $condicionesSalud = $condicionSaludModel->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");
        
        // Obtener condiciones de salud asignadas al huésped (array de IDs)
        $condicionesSeleccionadas = $this->huespedModel->getCondicionesSaludIds($id);

        // Obtener ubicaciones
        $ubicaciones = $this->ubicacionModel->getAll();

        if ($isPublicArea) {
            // Verificar que la reserva existe y pertenece al usuario
            $userId = $_SESSION['usuario_id'] ?? null;
            $reservaModel = new \App\Models\Reserva();
            $reserva = $reservaModel->find($reservaId);
            
            if (!$reserva || !$reservaModel->isReservaOwner($reservaId, $userId)) {
                $this->redirect('/mis-reservas', 'No tiene permisos para esta reserva', 'error');
                return;
            }

            $data = [
                'title' => 'Editar Huésped',
                'huesped' => $huesped,
                'condicionesSalud' => $condicionesSalud,
                'condicionesSeleccionadas' => $condicionesSeleccionadas,
                'ubicaciones' => $ubicaciones,
                'reserva_id' => $reservaId,
                'isEdit' => true,
                'isPublicArea' => true
            ];

            return $this->render('public/huespedes/formulario', $data, 'main');
        } else {
            // Área de administración
            $estadisticas = $this->huespedModel->getStatistics($id);
            $condicionesHuesped = $this->huespedModel->getCondicionesSalud($id);
            $reservaModel = new \App\Models\Reserva();
            $reservas = $reservaModel->findAll("reserva_fhfin > NOW()", "reserva_fhfin ASC");
            $reservaActualId = $this->huespedModel->getReservaAsociada($id);

            $data = [
                'title' => 'Editar Huésped',
                'huesped' => $huesped,
                'estadisticas' => $estadisticas,
                'condicionesSalud' => $condicionesSalud,
                'condicionesHuesped' => $condicionesHuesped,
                'reservas' => $reservas,
                'reservaActualId' => $reservaActualId,
                'ubicaciones' => $ubicaciones,
                'isEdit' => true,
                'isAdminArea' => true
            ];

            return $this->render('admin/operaciones/huespedes/formulario', $data, 'main');
        }
    }

    /**
     * Actualizar huésped (usado por edit)
     */
    public function update($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Huésped no encontrado']);
            return;
        }

        // Datos de persona física
        $dni = $this->post('persona_dni');
        $nombre = $this->post('persona_nombre');
        $apellido = $this->post('persona_apellido');
        $fechaNac = $this->post('persona_fechanac');
        $direccion = $this->post('persona_direccion');
        $ubicacion = $this->post('rela_ubicacion');

        // Condiciones de salud seleccionadas
        $condicionesSeleccionadas = $this->post('condiciones_salud', []);

        // Validaciones
        if (empty($dni)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El DNI es obligatorio']);
            return;
        }
        if (empty($nombre)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }
        if (empty($apellido)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El apellido es obligatorio']);
            return;
        }
        if (empty($fechaNac)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'La fecha de nacimiento es obligatoria']);
            return;
        }
        if (empty($direccion)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'La dirección es obligatoria']);
            return;
        }

        // Validar DNI duplicado (excepto el actual)
        $personaFisicaModel = new \App\Models\PersonaFisica();
        $dniExistente = $personaFisicaModel->dniExisteExceptoPersona($dni, $huesped['rela_persona']);
        if ($dniExistente) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ya existe otro huésped con este DNI']);
            return;
        }

        // Datos de persona
        $personaData = [
            'persona_direccion' => $direccion
        ];

        // Datos del huésped - solo actualizar ubicación si se proporcionó
        $huespedData = [];
        if (!empty($ubicacion)) {
            $huespedData['rela_ubicacion'] = (int)$ubicacion;
        }

        try {
            // Iniciar transacción
            $this->huespedModel->beginTransaction();

            // 1. Actualizar datos de la persona
            $idPersona = $huesped['rela_persona'];
            if (!$this->personaModel->update($idPersona, $personaData)) {
                throw new \Exception('Error al actualizar los datos de la persona');
            }

            // 2. Actualizar persona física
            $personaFisicaModel = new \App\Models\PersonaFisica();
            // Obtener id_personafisica desde la tabla persona
            $persona = $this->personaModel->find($idPersona);
            if ($persona && $persona['rela_personafisica']) {
                $personaFisicaData = [
                    'personafisica_dni' => $dni,
                    'personafisica_nombre' => $nombre,
                    'personafisica_apellido' => $apellido,
                    'personafisica_fechanac' => $fechaNac
                ];
                if (!$personaFisicaModel->update($persona['rela_personafisica'], $personaFisicaData)) {
                    throw new \Exception('Error al actualizar la persona física');
                }
            }

            // 3. Actualizar datos del huésped (ubicación) - solo si hay datos
            if (!empty($huespedData)) {
                if (!$this->huespedModel->update($id, $huespedData)) {
                    throw new \Exception('Error al actualizar el huésped');
                }
            }

            // 4. Obtener TODAS las condiciones de salud activas
            $condicionSaludModel = new \App\Models\CondicionSalud();
            $todasCondiciones = $condicionSaludModel->findAll("condicionsalud_estado = 1");

            // 5. Actualizar condiciones de salud
            if (!$this->huespedModel->updateCondicionesSalud($id, $todasCondiciones, $condicionesSeleccionadas)) {
                throw new \Exception('Error al actualizar condiciones de salud');
            }

            // Commit de la transacción
            $this->huespedModel->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Huésped actualizado correctamente']);
        } catch (\Exception $e) {
            // Rollback en caso de error
            $this->huespedModel->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el huésped: ' . $e->getMessage()]);
        }
    }

    /**
     * Baja lógica de huésped (modo público con JSON response)
     */
    public function delete($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Huésped no encontrado']);
            return;
        }

        // Obtener reserva_id del contexto
        $reservaId = $this->get('reserva_id');
        if (!$reservaId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No se especificó la reserva']);
            return;
        }

        try {
            // Verificar que el huésped esté asociado activamente a la reserva
            if (!$this->huespedModel->estaAsociadoAReserva($id, $reservaId)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'El huésped no está asociado a esta reserva o ya fue eliminado']);
                return;
            }

            // Cambiar solo el estado del registro en huesped_reserva
            // NO se modifica el estado del huésped ni de la persona
            if (!$this->huespedModel->updateEstadoEnReserva($id, $reservaId, 0)) {
                throw new \Exception('Error al actualizar el estado');
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Huésped eliminado de la reserva correctamente']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el huésped: ' . $e->getMessage()]);
        }
    }

    /**
     * Restaurar huésped en una reserva
     */
    public function restore($id)
    {
        $this->requirePermission('huespedes');

        // Obtener reserva_id del contexto
        $reservaId = $this->get('reserva_id');
        if (!$reservaId) {
            $this->redirect('/huespedes', 'No se especificó la reserva', 'error');
            return;
        }

        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            $this->redirect('/huespedes?reserva_id=' . $reservaId, 'Huésped no encontrado', 'error');
            return;
        }

        try {
            // Restaurar solo el estado del registro en huesped_reserva
            // NO se modifica el estado del huésped ni de la persona
            if (!$this->huespedModel->updateEstadoEnReserva($id, $reservaId, 1)) {
                throw new \Exception('Error al restaurar el huésped en la reserva');
            }

            $this->redirect('/huespedes?reserva_id=' . $reservaId, 'Huésped restaurado en la reserva correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect('/huespedes?reserva_id=' . $reservaId, 'Error al restaurar el huésped: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Cambiar estado de huésped (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('huespedes');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que el huésped existe
        $huesped = $this->huespedModel->find($id);
        if (!$huesped) {
            return $this->json(['success' => false, 'message' => 'Huésped no encontrado'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
        }

        // Actualizar el estado
        $data = ['huesped_estado' => $nuevoEstado];
        $resultado = $this->huespedModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactivo', 'activo'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizado';
            return $this->json([
                'success' => true, 
                'message' => "Huésped marcado como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado del huésped'
            ], 500);
        }
    }

    /**
     * Exportar huéspedes a Excel
     */
    public function exportar()
    {
        $this->requirePermission('huespedes');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'persona_nombre' => $this->get('persona_nombre'),
                'persona_dni' => $this->get('persona_dni'),
                'rela_ubicacion' => $this->get('rela_ubicacion'),
                'huesped_estado' => $this->get('huesped_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->huespedModel->getAllWithDetailsForExport($filters);
            $huespedes = $result['data'];

            if (empty($huespedes)) {
                $this->redirect('/huespedes', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Huéspedes');

            // Definir encabezados
            $headers = [
                'A1' => 'Nombre',
                'B1' => 'Apellido',
                'C1' => 'Fecha Nacimiento',
                'D1' => 'Dirección',
                'E1' => 'Ubicación',
                'F1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:F1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($huespedes as $huesped) {
                // Mapear estado a texto
                $estadoTexto = $huesped['huesped_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $huesped['persona_nombre']);
                $worksheet->setCellValue('B' . $row, $huesped['persona_apellido']);
                $worksheet->setCellValue('C' . $row, $huesped['persona_fechanac']);
                $worksheet->setCellValue('D' . $row, $huesped['persona_direccion']);
                $worksheet->setCellValue('E' . $row, $huesped['ubicacion_descripcion'] ?? '');
                $worksheet->setCellValue('F' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(20);
            $worksheet->getColumnDimension('B')->setWidth(20);
            $worksheet->getColumnDimension('C')->setWidth(15);
            $worksheet->getColumnDimension('D')->setWidth(30);
            $worksheet->getColumnDimension('E')->setWidth(25);
            $worksheet->getColumnDimension('F')->setWidth(12);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "huespedes_{$fecha}.xlsx";

            // Headers para descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            // Enviar archivo
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar huéspedes: " . $e->getMessage());
            $this->redirect('/huespedes', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar huéspedes a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('huespedes');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'persona_nombre' => $this->get('persona_nombre'),
                'persona_apellido' => $this->get('persona_apellido'),
                'huesped_ubicacion' => $this->get('huesped_ubicacion'),
                'huesped_estado' => $this->get('huesped_estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->huespedModel->getAllWithDetailsForExport($filters);
            $huespedes = $result['data'];

            if (empty($huespedes)) {
                $this->redirect('/huespedes', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF en orientación vertical (retrato) con tamaño A4 estándar
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Huéspedes');
            $pdf->SetSubject('Exportación de Huéspedes');
            $pdf->SetKeywords('huéspedes, listado, exportación');

            // Configurar márgenes mínimos para maximizar espacio de la tabla
            $pdf->SetMargins(8, 15, 8);
            $pdf->SetHeaderMargin(3);
            $pdf->SetFooterMargin(8);

            // Configurar auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 25);

            // Configurar escala de imagen
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Establecer fuente
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 9);

            // Agregar página
            $pdf->AddPage();

            // Título del documento
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Huéspedes', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados (si hay)
            $filtrosTexto = [];
            if (!empty($filters['persona_nombre'])) {
                $filtrosTexto[] = 'Nombre: ' . $filters['persona_nombre'];
            }
            if (!empty($filters['persona_apellido'])) {
                $filtrosTexto[] = 'Apellido: ' . $filters['persona_apellido'];
            }
            if (!empty($filters['rela_ubicacion'])) {
                $ubicacion = $this->ubicacionModel->find($filters['rela_ubicacion']);
                $filtrosTexto[] = 'Ubicación: ' . ($ubicacion['ubicacion_descripcion'] ?? 'N/A');
            }
            if (isset($filters['huesped_estado']) && $filters['huesped_estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['huesped_estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($huespedes) . ' | Formato: A4 Vertical';
            $pdf->Cell(0, 10, $infoFormato, 0, 1, 'L');
            $pdf->Ln(5);
            
            // Crear tabla HTML optimizada para A4 vertical
            $html = '<style>
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                    table-layout: fixed;
                }
                th { 
                    background-color: #E3F2FD; 
                    border: 1px solid #333; 
                    padding: 3px; 
                    text-align: center; 
                    font-weight: bold; 
                    font-size: 8px;
                    word-wrap: break-word;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 2px; 
                    font-size: 7px; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow: hidden;
                }
                .nombre { width: 30%; }
                .fecha { text-align: center; width: 15%; }
                .direccion { width: 30%; }
                .ubicacion { width: 15%; }
                .estado { text-align: center; width: 10%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="nombre">Nombre Completo</th>
                        <th class="fecha">F. Nacimiento</th>
                        <th class="direccion">Dirección</th>
                        <th class="ubicacion">Ubicación</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($huespedes as $huesped) {
                // Mapear estado a texto y clase CSS
                $estadoTexto = $huesped['huesped_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $huesped['huesped_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';
                
                // Formato: Apellido, Nombre
                $nombreCompleto = htmlspecialchars($huesped['persona_apellido']) . ', ' . htmlspecialchars($huesped['persona_nombre']);

                $html .= '<tr>
                    <td class="nombre">' . $nombreCompleto . '</td>
                    <td class="fecha">' . date('d/m/Y', strtotime($huesped['persona_fechanac'])) . '</td>
                    <td class="direccion">' . htmlspecialchars($huesped['persona_direccion']) . '</td>
                    <td class="ubicacion">' . htmlspecialchars($huesped['ubicacion_descripcion'] ?? '-') . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "huespedes_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar huéspedes a PDF: " . $e->getMessage());
            $this->redirect('/huespedes', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Buscar persona por DNI (AJAX)
     */
    public function buscarPorDni()
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        $dni = $this->get('dni');
        $reservaId = $this->get('reserva_id');
        
        if (empty($dni)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'DNI requerido']);
            return;
        }

        if (empty($reservaId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Reserva ID requerido']);
            return;
        }

        try {
            // Buscar persona física por DNI
            $personaFisicaModel = new \App\Models\PersonaFisica();
            $personaFisica = $personaFisicaModel->findByDNI($dni);

            if (!$personaFisica) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false]);
                return;
            }

            // Buscar si existe un huésped asociado a esta persona física
            $huesped = $this->huespedModel->findByPersonaFisicaId($personaFisica['id_personafisica']);

            if (!$huesped) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false]);
                return;
            }

            // Verificar si el huésped ya está asociado a esta reserva
            if ($this->huespedModel->estaAsociadoAReserva($huesped['id_huesped'], $reservaId)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Este huésped ya está asociado a esta reserva'
                ]);
                return;
            }

            // Retornar información del huésped encontrado para confirmación
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'huesped' => [
                    'id_huesped' => $huesped['id_huesped'],
                    'persona_dni' => $personaFisica['personafisica_dni'],
                    'persona_nombre' => $personaFisica['personafisica_nombre'],
                    'persona_apellido' => $personaFisica['personafisica_apellido'],
                    'persona_fechanac' => $personaFisica['personafisica_fechanac'],
                    'persona_direccion' => $personaFisica['persona_direccion'] ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error al buscar persona por DNI: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
        }
    }

    /**
     * Asociar huésped existente a reserva (AJAX)
     */
    public function asociarHuespedExistente()
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
            return;
        }

        $huespedId = $this->post('huesped_id');
        $reservaId = $this->post('reserva_id');

        if (empty($huespedId) || empty($reservaId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        try {
            // Verificar que el huésped existe
            $huesped = $this->huespedModel->find($huespedId);
            if (!$huesped) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Huésped no encontrado']);
                return;
            }

            // Verificar que no esté ya asociado
            if ($this->huespedModel->estaAsociadoAReserva($huespedId, $reservaId)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Este huésped ya está asociado a esta reserva']);
                return;
            }

            // Asociar huésped a la reserva
            if ($this->huespedModel->asociarReserva($huespedId, $reservaId)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Huésped asociado correctamente a la reserva']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al asociar el huésped']);
            }
        } catch (\Exception $e) {
            error_log("Error al asociar huésped: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al asociar huésped']);
        }
    }
}
