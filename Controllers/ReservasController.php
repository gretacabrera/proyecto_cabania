<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reserva;
use App\Models\Cabania;
use App\Models\Persona;
use App\Models\Servicio;
use App\Models\Consumo;
use App\Models\EstadoReserva;

class ReservasController extends Controller
{
    protected $reservaModel;
    protected $cabaniaModel;
    protected $personaModel;
    protected $servicioModel;
    protected $consumoModel;
    protected $estadoReservaModel;

    public function __construct()
    {
        parent::__construct();
        $this->reservaModel = new Reserva();
        $this->cabaniaModel = new Cabania();
        $this->personaModel = new Persona();
        $this->servicioModel = new Servicio();
        $this->consumoModel = new Consumo();
        $this->estadoReservaModel = new EstadoReserva();
    }

    public function index()
    {
        $this->requireAuth();
        
        // Obtener perfil del usuario
        $userProfile = \App\Core\Auth::getUserProfile();
        
        // Debug: Log para verificar qué perfil se está detectando
        error_log("DEBUG ReservasController@index: Perfil detectado = '$userProfile'");
        
        // Preparar datos base
        $page = (int) $this->get('page', 1);
        $filters = [
            'estado' => $this->get('estado'),
            'cabania' => $this->get('cabania'),
            'fecha_inicio' => $this->get('fecha_inicio'),
            'fecha_fin' => $this->get('fecha_fin'),
            'persona' => $this->get('persona')
        ];
        
        // Datos específicos según el perfil
        switch ($userProfile) {
            case 'administrador':
                error_log('DEBUG: Redirigiendo a indexAdministrador');
                return $this->indexAdministrador($page, $filters);
            case 'cajero':
                error_log('DEBUG: Redirigiendo a indexCajero');
                return $this->indexCajero($page, $filters);
            case 'recepcionista':
                error_log('DEBUG: Redirigiendo a indexRecepcionista');
                return $this->indexRecepcionista($page, $filters);
            case 'huesped':
                error_log('DEBUG: Redirigiendo a indexHuesped');
                return $this->indexHuesped();
            default:
                error_log("DEBUG: Perfil no autorizado: '$userProfile'");
                $this->redirect('/', 'Perfil no autorizado para gestionar reservas', 'error');
        }
    }

    /**
     * Vista de reservas para administrador - Control total
     */
    private function indexAdministrador($page, $filters)
    {
        $this->requirePermission('reservas');
        
        $result = $this->reservaModel->getWithDetails($page, 15, $filters);
        $cabanias = $this->cabaniaModel->getActive();
        $estadosReserva = $this->estadoReservaModel->getActive();
        
        $data = [
            'title' => 'Gestión de Reservas - Administración',
            'reservas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'cabanias' => $cabanias,
            'estados_reserva' => $estadosReserva,
            'totalReservas' => $this->getTotalReservas(),
            'reservasActivas' => $this->getReservasActivas(),
            'ingresosMes' => $this->getIngresosMes(),
            'ocupacionPromedio' => $this->getOcupacionPromedio(),
            'userProfile' => 'administrador',
            'isAdminArea' => true
        ];
        
        return $this->render('admin/operaciones/reservas/listado', $data);
    }
    
    /**
     * Vista de reservas para cajero - Enfoque en facturación y pagos
     */
    private function indexCajero($page, $filters)
    {
        $this->requirePermission('reservas');
        
        // Filtrar por reservas que necesitan gestión de pago
        $filters['estado_pago'] = $this->get('estado_pago');
        
        $result = $this->reservaModel->getWithDetails($page, 15, $filters);
        $cabanias = $this->cabaniaModel->getActive();
        
        $data = [
            'title' => 'Gestión de Reservas - Facturación',
            'reservas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'cabanias' => $cabanias,
            'reservasPendientesPago' => $this->getReservasPendientesPago(),
            'facturasHoy' => $this->getFacturasHoy(),
            'ingresosHoy' => $this->getIngresosHoy(),
            'metodosPagoMes' => $this->getMetodosPagoMes(),
            'userProfile' => 'cajero',
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/reservas/listado', $data);
    }
    
    /**
     * Vista de reservas para recepcionista - Enfoque en gestión operativa
     */
    private function indexRecepcionista($page, $filters)
    {
        $this->requirePermission('reservas');
        
        $result = $this->reservaModel->getWithDetails($page, 15, $filters);
        $cabanias = $this->cabaniaModel->getActive();
        $estadosReserva = $this->estadoReservaModel->getActive();
        
        $data = [
            'title' => 'Gestión de Reservas - Recepción',
            'reservas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'cabanias' => $cabanias,
            'estados_reserva' => $estadosReserva,
            'checkinsHoy' => $this->getCheckinsHoy(),
            'checkoutsHoy' => $this->getCheckoutsHoy(),
            'reservasHoy' => $this->getReservasHoy(),
            'ocupacionPromedio' => $this->getOcupacionPromedio(),
            'userProfile' => 'recepcionista',
            'isAdminArea' => true
        ];
        
        return $this->render('admin/operaciones/reservas/listado', $data);
    }
    
    /**
     * Vista de reservas para huésped - Solo sus propias reservas
     */
    private function indexHuesped($page = 1, $filters = [])
    {
        // Debug: Log para verificar que se está llamando este método
        error_log('DEBUG indexHuesped: Método llamado para usuario huésped');
        
        // Obtener persona asociada al usuario
        $persona = $this->personaModel->findByUsuario(\App\Core\Auth::user());
        
        if (!$persona) {
            error_log('DEBUG indexHuesped: No se encontró persona para el usuario');
            $this->redirect('/', 'No se encontraron datos de huésped', 'error');
            return;
        }
        
        error_log('DEBUG indexHuesped: Persona encontrada, llamando a misReservas()');
        // Para huéspedes, usar el método específico existente
        return $this->misReservas();
    }

    public function create()
    {
        $this->requirePermission('reservas');
        if ($this->isPost()) {
            return $this->store();
        }
        $cabanias = $this->cabaniaModel->getActive();
        
        // Obtener métodos de pago según el perfil del usuario actual
        $metodosPago = $this->getMetodosPagoPorPerfil();
        
        $userModel = new \App\Models\Usuario();
        
        $data = [
            'title' => 'Nueva Reserva',
            'cabanias' => $cabanias,
            'metodos_pago' => $metodosPago,
            'es_cajero' => $userModel->esPerfilCajero(),
            'es_huesped' => $userModel->esPerfilHuesped()
        ];
        return $this->render('admin/operaciones/reservas/formulario', $data);
    }

    public function store()
    {
        $this->requirePermission('reservas');
        $data = [
            'reserva_online' => 0, // Marcar como reserva in-situ (admin)
            'rela_cabania' => $this->post('rela_cabania'),
            'rela_persona' => $this->post('rela_persona'),
            'reserva_fechainicio' => $this->post('reserva_fechainicio'),
            'reserva_fechafin' => $this->post('reserva_fechafin'),
            'reserva_cantidadpersonas' => $this->post('reserva_cantidadpersonas'),
            'rela_metodopago' => $this->post('rela_metodopago'),
            'reserva_observaciones' => $this->post('reserva_observaciones', ''),
            'rela_estadoreserva' => 1
        ];
        if (empty($data['rela_cabania']) || empty($data['rela_persona']) || 
            empty($data['reserva_fechainicio']) || empty($data['reserva_fechafin'])) {
            $this->redirect('/admin/operaciones/reservas/formulario', 'Complete los campos obligatorios', 'error');
        }
        try {
            $id = $this->reservaModel->createReservation($data);
            if ($id) {
                $this->redirect('/reservas', 'Reserva creada correctamente', 'exito');
            } else {
                $this->redirect('/admin/operaciones/reservas/formulario', 'Error al crear la reserva', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/admin/operaciones/reservas/formulario', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    public function show($id)
    {
        $this->requirePermission('reservas');
        $result = $this->reservaModel->getWithDetails(1, 1, ['id' => $id]);
        if (empty($result['data'])) {
            return $this->view->error(404);
        }
        $reserva = $result['data'][0];
        $consumos = $this->reservaModel->getConsumptions($id);
        $data = [
            'title' => 'Detalle de Reserva',
            'reserva' => $reserva,
            'consumos' => $consumos
        ];
        return $this->render('admin/operaciones/reservas/detalle', $data);
    }

    public function edit($id)
    {
        $this->requirePermission('reservas');
        $reserva = $this->reservaModel->find($id);
        if (!$reserva) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $cabanias = $this->cabaniaModel->getActive();
        $data = [
            'title' => 'Editar Reserva',
            'reserva' => $reserva,
            'cabanias' => $cabanias
        ];

        return $this->render('admin/operaciones/reservas/formulario', $data);
    }

    public function update($id)
    {
        $this->requirePermission('reservas');
        $reserva = $this->reservaModel->find($id);
        if (!$reserva) {
            return $this->view->error(404);
        }
        $data = [
            'reserva_fechainicio' => $this->post('reserva_fechainicio'),
            'reserva_fechafin' => $this->post('reserva_fechafin'),
            'reserva_cantidadpersonas' => $this->post('reserva_cantidadpersonas'),
            'reserva_observaciones' => $this->post('reserva_observaciones', '')
        ];
        try {
            if ($this->reservaModel->update($id, $data)) {
                $this->redirect('/reservas', 'Reserva actualizada correctamente', 'exito');
            } else {
                $this->redirect('/admin/operaciones/reservas/editar/' . $id, 'Error al actualizar la reserva', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/admin/operaciones/reservas/editar/' . $id, 'Error: ' . $e->getMessage(), 'error');
        }
    }

    public function online()
    {
        // Las reservas online se gestionan únicamente a través del catálogo público
        // Redirigir a los huéspedes al flujo correcto
        $this->redirect('/catalogo', 'Para hacer una reserva, seleccione una cabaña del catálogo', 'info');
    }

    /**
     * Mostrar vista de confirmación de reserva desde catálogo público
     */
    public function confirmar()
    {
        $this->requireAuth();
        
        // Obtener datos de la reserva desde los parámetros GET o sesión
        $cabaniaId = $this->get('cabania_id');
        $fechaInicio = $this->get('fecha_inicio');
        $fechaFin = $this->get('fecha_fin');
        
        // Si no hay datos en GET, verificar en sesión (desde pending_reservation)
        if (!$cabaniaId && isset($_SESSION['pending_reservation'])) {
            $pending = $_SESSION['pending_reservation'];
            $cabaniaId = $pending['cabania_id'];
            $fechaInicio = $pending['fecha_inicio'];
            $fechaFin = $pending['fecha_fin'];
            // Limpiar los datos de la sesión
            unset($_SESSION['pending_reservation']);
        }
        
        // Validar que tenemos todos los datos necesarios
        if (!$cabaniaId || !$fechaInicio || !$fechaFin) {
            $this->redirect('/catalogo', 'Error: datos de reserva incompletos', 'error');
            return;
        }
        
        // Obtener información de la cabaña
        $cabania = $this->cabaniaModel->find($cabaniaId);
        if (!$cabania || $cabania['cabania_estado'] != 1) {
            $this->redirect('/catalogo', 'Error: cabaña no disponible', 'error');
            return;
        }
        
        // Calcular días y precio total
        $fechaInicioObj = new \DateTime($fechaInicio);
        $fechaFinObj = new \DateTime($fechaFin);
        $dias = $fechaInicioObj->diff($fechaFinObj)->days;
        $precioTotal = $dias * $cabania['cabania_precio'];
        
        // Obtener datos del usuario logueado y su persona asociada
        $userId = \App\Core\Auth::id();
        $userModel = new \App\Models\Usuario();
        $usuario = $userModel->findWithProfile($userId);
        
        if (!$usuario || !$usuario['rela_persona']) {
            $this->redirect('/catalogo', 'Error: datos de usuario incompletos', 'error');
            return;
        }
        
        // Obtener datos de la persona con sus contactos
        $persona = $this->personaModel->getWithContacts($usuario['rela_persona']);
        
        if (!$persona) {
            $this->redirect('/catalogo', 'Error: datos de huésped no encontrados', 'error');
            return;
        }
        
        // Preparar datos para la vista
        $reservaData = [
            'cabania_id' => $cabaniaId,
            'cabania_nombre' => $cabania['cabania_nombre'],
            'cabania_codigo' => $cabania['cabania_codigo'],
            'cabania_descripcion' => $cabania['cabania_descripcion'],
            'cabania_capacidad' => $cabania['cabania_capacidad'],
            'cabania_precio' => $cabania['cabania_precio'],
            'cabania_imagen' => $cabania['cabania_foto'] ?? 'default.jpg',
            'fecha_ingreso' => $fechaInicio,
            'fecha_salida' => $fechaFin,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'noches' => $dias,
            'dias_estancia' => $dias,
            'precio_total' => $precioTotal,
            'subtotal' => $precioTotal, // subtotal = precio base sin servicios adicionales
            'total' => $precioTotal,    // total = subtotal (sin servicios adicionales por ahora)
            'cantidad_personas' => 1    // valor por defecto
        ];
        
        // Datos del huésped desde el usuario logueado
        $huesped = [
            'id_persona' => $persona['id_persona'],
            'nombre' => $persona['persona_nombre'],
            'apellido' => $persona['persona_apellido'],
            'fecha_nacimiento' => $persona['persona_fechanac'],
            'email' => $persona['contacto_email'],
            'telefono' => $persona['contacto_telefono']
        ];
        
        // Guardar datos temporales básicos en la sesión para el flujo de reserva
        $_SESSION['reserva_temporal_basica'] = [
            'cabania_id' => $cabaniaId,
            'fecha_ingreso' => $fechaInicio,
            'fecha_salida' => $fechaFin,
            'cantidad_personas' => 1, // Por defecto
            'id_persona' => $persona['id_persona'],
            'subtotal_alojamiento' => $precioTotal,
            'servicios' => [],
            'total_servicios' => 0,
            'total_general' => $precioTotal,
            'huesped_nombre' => $persona['persona_nombre'] . ' ' . $persona['persona_apellido'],
            'huesped_email' => $persona['contacto_email']
        ];

        $data = [
            'title' => 'Confirmar Reserva',
            'reserva' => $reservaData,
            'huesped' => $huesped,
            'isAdminArea' => false
        ];
        
        return $this->render('public/reservas/confirmar', $data, 'main');
    }

    /**
     * Mostrar vista de servicios adicionales
     */
    public function servicios()
    {
        $this->requireAuth();
        
        if ($this->isPost()) {
            // Datos enviados desde el formulario de confirmación
            $reservaData = [
                'cabania_id' => $this->post('cabania_id'),
                'fecha_ingreso' => $this->post('fecha_ingreso'),
                'fecha_salida' => $this->post('fecha_salida'),
                'cantidad_personas' => $this->post('cantidad_personas'),
                'id_persona' => $this->post('id_persona'),
                'subtotal' => $this->post('subtotal')
            ];
            
            // Validar datos básicos
            if (empty($reservaData['cabania_id']) || empty($reservaData['fecha_ingreso']) || empty($reservaData['fecha_salida'])) {
                $this->redirect('/catalogo', 'Error: datos de reserva incompletos', 'error');
                return;
            }
            
            // Obtener información de la cabaña
            $cabania = $this->cabaniaModel->find($reservaData['cabania_id']);
            if (!$cabania) {
                $this->redirect('/catalogo', 'Error: cabaña no encontrada', 'error');
                return;
            }
            
            // Calcular días de estadía
            $fechaInicioObj = new \DateTime($reservaData['fecha_ingreso']);
            $fechaFinObj = new \DateTime($reservaData['fecha_salida']);
            $dias = $fechaInicioObj->diff($fechaFinObj)->days;
            
            // NO crear reserva aquí - se creará más tarde con servicios incluidos en una sola transacción
            // Solo preparar los datos para la sesión
            $reservaData['cabania_nombre'] = $cabania['cabania_nombre'];
            $reservaData['cabania_precio'] = $cabania['cabania_precio'];
            $reservaData['cabania_imagen'] = $cabania['cabania_foto'] ?? 'default.jpg';
            $reservaData['noches'] = $dias;
            $reservaData['total'] = $reservaData['subtotal']; // Agregar el total para la vista
            
            // Obtener servicios disponibles para reservas (tipo 3, estado activo)
            $servicios = $this->servicioModel->getServiciosParaReservas();
            
            $data = [
                'title' => 'Servicios Adicionales',
                'reserva' => $reservaData,
                'servicios' => $servicios,
                'isAdminArea' => false
            ];
            
            return $this->render('public/reservas/servicios', $data, 'main');
        } else {
            // Si se accede por GET, redirigir al catálogo
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
        }
    }

    public function procesarServicios()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
            return;
        }

        // Obtener datos de la reserva
        $reservaData = [
            'cabania_id' => $this->post('cabania_id'),
            'fecha_ingreso' => $this->post('fecha_ingreso'),
            'fecha_salida' => $this->post('fecha_salida'),
            'cantidad_personas' => $this->post('cantidad_personas'),
            'id_persona' => $this->post('id_persona'),
            'subtotal_alojamiento' => $this->post('subtotal_alojamiento')
        ];

        // Validar datos básicos
        if (empty($reservaData['cabania_id']) || empty($reservaData['fecha_ingreso']) || 
            empty($reservaData['fecha_salida']) || empty($reservaData['id_persona'])) {
            $this->redirect('/catalogo', 'Error: datos de reserva incompletos', 'error');
            return;
        }

        // Obtener servicios seleccionados
        $serviciosSeleccionados = $this->post('servicios', []);
        
        try {
            // Calcular total de servicios
            $totalServicios = 0;
            $serviciosDetalle = [];
            
            if (!empty($serviciosSeleccionados)) {
                foreach ($serviciosSeleccionados as $servicioId) {
                    $servicio = $this->servicioModel->find($servicioId);
                    if ($servicio) {
                        $totalServicios += $servicio['servicio_precio'];
                        $serviciosDetalle[] = [
                            'id' => $servicio['id_servicio'],
                            'nombre' => $servicio['servicio_nombre'],
                            'precio' => $servicio['servicio_precio']
                        ];
                    }
                }
            }
            
            // Calcular total general
            $subtotalAlojamiento = (float)$reservaData['subtotal_alojamiento'];
            $totalGeneral = $subtotalAlojamiento + $totalServicios;
            
            // Guardar datos completos de la reserva en sesión para el resumen
            // IMPORTANTE: No crear la reserva aquí, solo preparar datos
            // La reserva se creará en una sola transacción con servicios cuando se procese el pago
            $_SESSION['reserva_temporal'] = [
                'cabania_id' => $reservaData['cabania_id'],
                'fecha_ingreso' => $reservaData['fecha_ingreso'],
                'fecha_salida' => $reservaData['fecha_salida'],
                'cantidad_personas' => $reservaData['cantidad_personas'],
                'id_persona' => $reservaData['id_persona'],
                'subtotal_alojamiento' => $subtotalAlojamiento,
                'servicios' => $serviciosDetalle, // Servicios preparados para transacción
                'total_servicios' => $totalServicios,
                'total_general' => $totalGeneral,
                'huesped_nombre' => $reservaData['huesped_nombre'] ?? '',
                'huesped_email' => $reservaData['huesped_email'] ?? ''
            ];

            error_log("INFO: Datos temporales guardados con " . count($serviciosDetalle) . " servicios - Total: $totalGeneral");

            // Redirigir al resumen
            $this->redirect('/reservas/resumen', '', 'info');

        } catch (\Exception $e) {
            $this->redirect('/catalogo', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    public function resumen()
    {
        $this->requireAuth();
        
        // Verificar que existan datos temporales de la reserva
        if (!isset($_SESSION['reserva_temporal']) && !isset($_SESSION['reserva_temporal_basica'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        // Usar datos completos si están disponibles, sino usar datos básicos
        if (isset($_SESSION['reserva_temporal'])) {
            $reservaTemporal = $_SESSION['reserva_temporal'];
        } else {
            // Usar datos básicos como fallback
            $reservaTemporal = $_SESSION['reserva_temporal_basica'];
        }
        
        // Obtener información adicional necesaria
        $cabania = $this->cabaniaModel->find($reservaTemporal['cabania_id']);
        $persona = $this->personaModel->find($reservaTemporal['id_persona']);
        
        if (!$cabania || !$persona) {
            $this->redirect('/catalogo', 'Error: datos no encontrados', 'error');
            return;
        }
        
        // Calcular días de estadía
        $fechaInicioObj = new \DateTime($reservaTemporal['fecha_ingreso']);
        $fechaFinObj = new \DateTime($reservaTemporal['fecha_salida']);
        $dias = $fechaInicioObj->diff($fechaFinObj)->days;
        
        // Generar CSRF token si no existe
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $data = [
            'title' => 'Resumen de Reserva',
            'reserva' => $reservaTemporal,
            'cabania' => $cabania,
            'persona' => $persona,
            'noches' => $dias,
            'isAdminArea' => false
        ];
        
        return $this->render('public/reservas/resumen', $data, 'main');
    }

    public function procederPago()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            error_log('DEBUG procederPago: Acceso no es POST, redirigiendo a catálogo');
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
            return;
        }
        
        error_log('DEBUG procederPago: Iniciando proceso de pago');
        
        // Verificar que existan datos temporales de la reserva
        if (!isset($_SESSION['reserva_temporal']) && !isset($_SESSION['reserva_temporal_basica'])) {
            error_log('DEBUG procederPago: No hay datos de reserva en sesión');
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        // Usar datos completos si están disponibles, sino usar datos básicos
        if (isset($_SESSION['reserva_temporal'])) {
            $reservaTemporal = $_SESSION['reserva_temporal'];
            error_log('DEBUG procederPago: Usando reserva_temporal completa');
            
            // Si los datos temporales no tienen reserva_id, crear la reserva
            if (!isset($reservaTemporal['reserva_id'])) {
                error_log('DEBUG procederPago: No existe reserva_id, creando reserva temporal');
                try {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                    error_log("DEBUG procederPago: Reserva temporal creada exitosamente con ID: $reservaId");
                } catch (\Exception $e) {
                    error_log('ERROR procederPago creando reserva temporal: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            } else {
                error_log('DEBUG procederPago: Reserva_id ya existe: ' . $reservaTemporal['reserva_id']);
            }
        } else {
            // Usar datos básicos como fallback
            $reservaTemporal = $_SESSION['reserva_temporal_basica'];
            error_log('DEBUG procederPago: Usando reserva_temporal_basica');
            
            if (!isset($reservaTemporal['reserva_id'])) {
                error_log('DEBUG procederPago: No existe reserva_id en datos básicos, creando reserva temporal');
                try {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal_basica']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                    error_log("DEBUG procederPago: Reserva temporal básica creada exitosamente con ID: $reservaId");
                } catch (\Exception $e) {
                    error_log('ERROR procederPago creando reserva temporal básica: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            } else {
                error_log('DEBUG procederPago: Reserva_id ya existe en datos básicos: ' . $reservaTemporal['reserva_id']);
            }
        }
        
        try {
            // El flujo simplificado: la reserva ya fue creada con servicios
            // Solo necesitamos redirigir directamente a la pasarela
            
            // Verificar que la reserva tenga ID (debería tenerlo por la lógica anterior)
            if (!isset($reservaTemporal['reserva_id'])) {
                throw new \Exception('No se encontró el ID de la reserva después del proceso');
            }
            
            error_log('DEBUG procederPago: Redirigiendo a pasarela con reserva_id: ' . $reservaTemporal['reserva_id']);
            
            // Redirigir directamente a la pasarela de pago
            $this->redirect('/reservas/pasarela', '', 'info');

        } catch (\Exception $e) {
            error_log('ERROR final en procederPago: ' . $e->getMessage());
            $this->redirect('/reservas/resumen', 'Error al proceder al pago: ' . $e->getMessage(), 'error');
        }
    }

    public function pago()
    {
        $this->requireAuth();
        
        // Si es POST, viene desde resumen, procesar la transición
        if ($this->isPost()) {
            // Verificar CSRF token básico
            $csrf_token = $this->post('csrf_token');
            if (!$csrf_token || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
                $this->redirect('/reservas/resumen', 'Error de seguridad', 'error');
                return;
            }
        }
        
        // Verificar que existan datos temporales de la reserva
        if (!isset($_SESSION['reserva_temporal']) && !isset($_SESSION['reserva_temporal_basica'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        // Usar datos completos si están disponibles, sino usar datos básicos
        if (isset($_SESSION['reserva_temporal'])) {
            $reservaTemporal = $_SESSION['reserva_temporal'];
        } else {
            // Usar datos básicos como fallback y crear una reserva temporal
            $reservaTemporal = $_SESSION['reserva_temporal_basica'];
            
            // Crear la reserva real en la base de datos si aún no existe
            if (!isset($reservaTemporal['reserva_id'])) {
                try {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal_basica']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                } catch (\Exception $e) {
                    error_log('Error creando reserva temporal en pago: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            }
        }
        
        // Obtener información de la cabaña
        $cabania = $this->cabaniaModel->find($reservaTemporal['cabania_id']);
        
        if (!$cabania) {
            $this->redirect('/catalogo', 'Error: cabaña no encontrada', 'error');
            return;
        }
        
        // Obtener métodos de pago disponibles para reservas online (público)
        $metodosPago = $this->getMetodosPagoPorPerfil('huesped');
        
        // Si no hay métodos de pago para online, usar fallback básico
        if (empty($metodosPago)) {
            $metodosPago = [
                ['id_metododepago' => 3, 'metododepago_descripcion' => 'DEBITO'],
                ['id_metododepago' => 4, 'metododepago_descripcion' => 'CREDITO']
            ];
        }
        
        $data = [
            'title' => 'Procesamiento de Pago',
            'reserva' => $reservaTemporal,
            'cabania' => $cabania,
            'metodos_pago' => $metodosPago,
            'isAdminArea' => false
        ];
        
        return $this->render('public/reservas/pago', $data, 'main');
    }

    /**
     * Vista de pasarela de pago simulada
     */
    public function pasarela()
    {
        $this->requireAuth();
        
        // Permitir tanto GET como POST para mostrar la pasarela
        // GET: Mostrar formulario de pasarela
        // POST: Procesar datos del formulario de pago y mostrar pasarela
        
        // Verificar que existan datos temporales de la reserva
        if (!isset($_SESSION['reserva_temporal']) && !isset($_SESSION['reserva_temporal_basica'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        // Usar datos completos si están disponibles, sino usar datos básicos
        if (isset($_SESSION['reserva_temporal'])) {
            $reservaTemporal = $_SESSION['reserva_temporal'];
            // Si los datos temporales no tienen reserva_id, crear la reserva
            if (!isset($reservaTemporal['reserva_id'])) {
                try {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                } catch (\Exception $e) {
                    error_log('Error creando reserva temporal en pasarela: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            }
        } else {
            $reservaTemporal = $_SESSION['reserva_temporal_basica'];
            if (!isset($reservaTemporal['reserva_id'])) {
                try {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal_basica']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                } catch (\Exception $e) {
                    error_log('Error creando reserva temporal básica en pasarela: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            }
        }
        
        // Obtener método de pago seleccionado
        $metodoPago = null;
        $numeroTarjeta = '';
        
        if ($this->isPost()) {
            // Datos del formulario de pago
            $metodoPago = $this->post('metodo_pago');
            $numeroTarjeta = $this->post('numero_tarjeta', '');
            
            // Guardar datos del método de pago en sesión
            $_SESSION['pago_datos'] = [
                'metodo_pago' => $metodoPago,
                'numero_tarjeta' => $numeroTarjeta,
                'titular_tarjeta' => $this->post('titular_tarjeta', ''),
                'vencimiento' => $this->post('vencimiento', ''),
                'codigo_seguridad' => $this->post('codigo_seguridad', '')
            ];
        } else {
            // Acceso directo por GET - verificar si hay datos previos en sesión
            if (isset($_SESSION['pago_datos'])) {
                $pagoData = $_SESSION['pago_datos'];
                $metodoPago = $pagoData['metodo_pago'] ?? null;
                $numeroTarjeta = $pagoData['numero_tarjeta'] ?? '';
            } else {
                // Si no hay datos de pago previos, inicializar con valores por defecto
                // Esto permite acceso directo desde el resumen
                $_SESSION['pago_datos'] = [
                    'metodo_pago' => 'Pendiente',
                    'numero_tarjeta' => '',
                    'titular_tarjeta' => '',
                    'vencimiento' => '',
                    'codigo_seguridad' => ''
                ];
                $metodoPago = 'Pendiente selección';
                $numeroTarjeta = '';
            }
        }
        
        $data = [
            'title' => 'Pasarela de Pago - Simulación',
            'reserva' => $reservaTemporal,
            'metodo_pago' => $metodoPago,
            'numero_tarjeta' => $numeroTarjeta,
            'isAdminArea' => false
        ];
        
        return $this->render('public/reservas/pasarela', $data, 'main');
    }

    /**
     * Procesar resultado de pasarela simulada
     */
    public function procesarPasarela()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
            return;
        }
        
        // Buscar el parámetro de estado en cualquier formato
        $estado = $this->post('estado') ?? $this->post('accion');
        
        // Debug: Ver qué parámetros llegan
        error_log("procesarPasarela - Estado recibido: " . ($estado ?? 'null'));
        error_log("procesarPasarela - Todos los POST: " . print_r($_POST, true));
        
        if ($estado === 'rechazar' || $estado === 'rechazado') {
            // Simular pago rechazado - redirigir al resumen con mensaje de error
            $_SESSION['error_message'] = 'Pago rechazado por la pasarela de pago. Verifique sus datos e intente nuevamente.';
            $this->redirect('/reservas/resumen');
        } elseif ($estado === 'aprobar' || $estado === 'aprobado') {
            try {
                // Obtener datos de la reserva para verificaciones
                $reservaTemporal = $_SESSION['reserva_temporal'] ?? $_SESSION['reserva_temporal_basica'] ?? null;
                
                if (!$reservaTemporal || !isset($reservaTemporal['reserva_id'])) {
                    throw new \Exception('Datos de reserva no encontrados');
                }
                
                $reservaId = $reservaTemporal['reserva_id'];
                
                // 1. Verificar que la reserva no haya expirado
                if ($this->reservaExpirada($reservaId)) {
                    throw new \Exception('Su reserva ha expirado. Por favor, inicie el proceso nuevamente desde el catálogo.');
                }
                
                // 2. Verificar disponibilidad real de la cabaña al momento del pago
                if (!$this->cabaniaDisponible(
                    $reservaTemporal['cabania_id'],
                    $reservaTemporal['fecha_ingreso'],
                    $reservaTemporal['fecha_salida'],
                    $reservaId
                )) {
                    throw new \Exception('La cabaña ya no está disponible para las fechas seleccionadas. Por favor, seleccione otras fechas.');
                }
                
                // 3. Determinar tipo de procesamiento según perfil del usuario
                $userModel = new \App\Models\Usuario();
                $esHuesped = $userModel->esPerfilHuesped();
                
                if ($esHuesped) {
                    // ESCENARIO 1: Usuario huésped - Pago automático desde pasarela externa
                    
                    // El pago ya fue validado en procesarPasarela(), aquí solo procesamos
                    // Debug: Ver qué datos llegan desde la pasarela
                    error_log("Datos POST recibidos en pago(): " . print_r($_POST, true));
                    
                    $metodoPasarela = $_POST['metodo_pasarela'] ?? 'credito';
                    $metodoInterno = $this->mapearMetodoPasarelaAInterno($metodoPasarela);
                    
                    // Actualizar datos de pago en sesión
                    if (!isset($_SESSION['pago_datos'])) {
                        $_SESSION['pago_datos'] = [];
                    }
                    
                    $_SESSION['pago_datos']['metodo_pago_id'] = $metodoInterno['id'];
                    $_SESSION['pago_datos']['metodo_pago_nombre'] = $metodoInterno['nombre'];
                    $_SESSION['pago_datos']['metodo_pasarela'] = $metodoPasarela;
                    $_SESSION['pago_datos']['procesado_por_pasarela'] = true;
                    
                } else {
                    // ESCENARIO 2: Usuario cajero - Selección manual del método de pago
                    
                    // Validar que se haya seleccionado un método de pago manualmente
                    $metodoPagoId = $_POST['metodo_pago_id'] ?? $_SESSION['pago_datos']['metodo_pago_id'] ?? null;
                    
                    if (!$metodoPagoId) {
                        throw new \Exception('Debe seleccionar un método de pago.');
                    }
                    
                    // Obtener información del método seleccionado
                    $metodoPagoModel = new \App\Models\MetodoPago();
                    $metodoPago = $metodoPagoModel->find($metodoPagoId);
                    
                    if (!$metodoPago) {
                        throw new \Exception('Método de pago no válido.');
                    }
                    
                    // Actualizar datos de pago en sesión
                    if (!isset($_SESSION['pago_datos'])) {
                        $_SESSION['pago_datos'] = [];
                    }
                    
                    $_SESSION['pago_datos']['metodo_pago_id'] = $metodoPago['id_metododepago'];
                    $_SESSION['pago_datos']['metodo_pago_nombre'] = $metodoPago['metododepago_descripcion'];
                    $_SESSION['pago_datos']['procesado_por_pasarela'] = false;
                }
                
                // 4. Preparar datos y confirmar pago
                $this->prepararDatosPagoYConfirmar();
                
            } catch (\Exception $e) {
                // En caso de error durante el procesamiento, redirigir al resumen con el mensaje de error
                error_log('Error procesando pago aprobado: ' . $e->getMessage());
                $_SESSION['error_message'] = $e->getMessage();
                $this->redirect('/reservas/resumen');
            }
        } else {
            $_SESSION['error_message'] = 'Acción no válida en la pasarela de pago.';
            $this->redirect('/reservas/resumen');
        }
    }
    
    /**
     * Mapea métodos de la pasarela externa a métodos internos de la aplicación
     */
    private function mapearMetodoPasarelaAInterno($metodoPasarela)
    {
        try {
            $metodoPagoModel = new \App\Models\MetodoPago();
            
            // Debug: Ver el método que se está intentando mapear
            error_log("Mapeando método de pasarela: '$metodoPasarela'");
            
            // Mapeo de nombres de pasarela a descripciones en BD
            $mapeoDescripciones = [
                'debito' => 'DEBITO',
                'credito' => 'CREDITO',
                'mercado_credito' => 'MERCADO CREDITO', // Viene del payload del frontend
                'mercadopago_credito' => 'MERCADO CREDITO', // Alternativo
                'tarjeta' => 'CREDITO' // Fallback para tarjeta genérica
            ];
            
            $descripcionBuscada = $mapeoDescripciones[$metodoPasarela] ?? 'EFECTIVO';
            error_log("Descripción a buscar en BD: '$descripcionBuscada'");
            
            // Buscar el método de pago por descripción
            $metodo = $metodoPagoModel->findByDescripcion($descripcionBuscada);
            error_log("Método encontrado en BD: " . print_r($metodo, true));
            
            if ($metodo) {
                return [
                    'id' => $metodo['id_metododepago'],
                    'nombre' => $metodo['metododepago_descripcion']
                ];
            }
            
            // Fallback: buscar EFECTIVO como método por defecto
            $metodoPorDefecto = $metodoPagoModel->findByDescripcion('EFECTIVO');
            if ($metodoPorDefecto) {
                return [
                    'id' => $metodoPorDefecto['id_metododepago'],
                    'nombre' => $metodoPorDefecto['metododepago_descripcion']
                ];
            }
            
            // Último fallback: usar ID 1
            return ['id' => 1, 'nombre' => 'EFECTIVO'];
            
        } catch (\Exception $e) {
            error_log("Error en mapearMetodoPasarelaAInterno: " . $e->getMessage());
            // Fallback en caso de error
            return ['id' => 1, 'nombre' => 'EFECTIVO'];
        }
    }

    /**
     * Obtener métodos de pago según el perfil del usuario
     */
    private function getMetodosPagoPorPerfil($perfilUsuario = null)
    {
        $metodoPagoModel = new \App\Models\MetodoPago();
        $userModel = new \App\Models\Usuario();
        
        // Si no se especifica perfil, detectar automáticamente
        if ($perfilUsuario === null) {
            $perfilUsuario = $userModel->getTipoPerfil();
        }
        
        // Para huéspedes: solo métodos disponibles en pasarela externa (mapeo automático)
        if ($perfilUsuario === 'huesped') {
            $metodosOnline = ['DEBITO', 'CREDITO', 'MERCADO CREDITO'];
            $metodosPago = [];
            
            foreach ($metodosOnline as $descripcion) {
                $metodo = $metodoPagoModel->findByDescripcion($descripcion);
                if ($metodo) {
                    $metodosPago[] = $metodo;
                }
            }
            
            return $metodosPago;
        } 
        // Para cajeros: todos los métodos disponibles en BD (selección manual)
        else if ($perfilUsuario === 'cajero') {
            return $metodoPagoModel->getActive();
        }
        // Para otros perfiles de admin: todos los métodos disponibles
        else {
            return $metodoPagoModel->getActive();
        }
    }


    
    private function prepararDatosPagoYConfirmar()
    {
        try {
            // Verificar que existan datos de reserva
            if (!isset($_SESSION['reserva_temporal']) && !isset($_SESSION['reserva_temporal_basica'])) {
                throw new \Exception('No hay datos de reserva disponibles');
            }
            
            // Obtener reserva temporal
            if (isset($_SESSION['reserva_temporal'])) {
                $reservaTemporal = $_SESSION['reserva_temporal'];
            } else {
                $reservaTemporal = $_SESSION['reserva_temporal_basica'];
                if (!isset($reservaTemporal['reserva_id'])) {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal_basica']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                }
            }
            
            // Verificar datos de pago en sesión
            if (!isset($_SESSION['pago_datos'])) {
                throw new \Exception('No se encontraron datos de pago válidos');
            }
            
            $pagoData = $_SESSION['pago_datos'];
            
            // Preparar datos para confirmarPago
            $_SESSION['datos_pago'] = [
                'reserva_id' => $reservaTemporal['reserva_id'],
                'metodo_pago_id' => $pagoData['metodo_pago_id'] ?? 1,
                'metodo_pago_nombre' => $pagoData['metodo_pago_nombre'] ?? 'Tarjeta de Crédito/Débito',
                'metodo_pasarela' => $pagoData['metodo_pasarela'] ?? 'tarjeta',
                'numero_tarjeta' => '', // Simulado - en producción vendría de la pasarela
                'nombre_titular' => '' // Simulado - en producción vendría de la pasarela
            ];
            
            // Ahora llamar a confirmarPago
            return $this->confirmarPago();
            
        } catch (\Exception $e) {
            error_log('Error preparando datos de pago: ' . $e->getMessage());
            throw $e; // Re-lanzar la excepción para que sea manejada por el método que la llama
        }
    }

    /**
     * Registrar pago manual en módulo de caja (solo para cajeros)
     */
    public function registrarPagoManual()
    {
        $this->requireAuth();
        
        // Verificar que el usuario sea cajero
        $userModel = new \App\Models\Usuario();
        if (!$userModel->esPerfilCajero()) {
            $this->redirect('/admin/operaciones/reservas', 'No tiene permisos para registrar pagos manuales.', 'error');
            return;
        }
        
        if ($this->isPost()) {
            try {
                $reservaId = $this->post('reserva_id');
                $metodoPagoId = $this->post('metodo_pago_id');
                $montoPago = $this->post('monto_pago');
                
                // Validaciones
                if (!$reservaId || !$metodoPagoId || !$montoPago) {
                    throw new \Exception('Complete todos los campos obligatorios.');
                }
                
                if ($montoPago <= 0) {
                    throw new \Exception('El monto debe ser mayor a cero.');
                }
                
                // Verificar que la reserva existe
                $reserva = $this->reservaModel->find($reservaId);
                if (!$reserva) {
                    throw new \Exception('Reserva no encontrada.');
                }
                
                // Verificar que el método de pago existe
                $metodoPagoModel = new \App\Models\MetodoPago();
                $metodoPago = $metodoPagoModel->find($metodoPagoId);
                if (!$metodoPago) {
                    throw new \Exception('Método de pago no válido.');
                }
                
                // Registrar el pago
                $pagoModel = new \App\Models\Pago();
                $pagoId = $pagoModel->createPago($reservaId, [
                    'total' => $montoPago,
                    'metodo_pago_id' => $metodoPagoId
                ]);
                
                if ($pagoId) {
                    $this->redirect("/admin/operaciones/reservas/detalle/{$reservaId}", 
                                  'Pago registrado correctamente.', 'exito');
                } else {
                    throw new \Exception('Error al registrar el pago.');
                }
                
            } catch (\Exception $e) {
                $this->redirect("/admin/operaciones/reservas", 
                              'Error: ' . $e->getMessage(), 'error');
            }
            return;
        }
        
        // Mostrar formulario de registro de pago
        $reservaId = $this->get('reserva_id');
        $reserva = $this->reservaModel->find($reservaId);
        
        if (!$reserva) {
            $this->redirect('/admin/operaciones/reservas', 'Reserva no encontrada.', 'error');
            return;
        }
        
        // Obtener todos los métodos de pago disponibles para cajeros
        $metodosPago = $this->getMetodosPagoPorPerfil('cajero');
        
        // Obtener pagos ya registrados para esta reserva
        $pagoModel = new \App\Models\Pago();
        $pagosExistentes = $pagoModel->getPagosByReserva($reservaId);
        $totalPagado = $pagoModel->getTotalPagadoReserva($reservaId);
        
        $data = [
            'title' => 'Registrar Pago Manual',
            'reserva' => $reserva,
            'metodos_pago' => $metodosPago,
            'pagos_existentes' => $pagosExistentes,
            'total_pagado' => $totalPagado
        ];
        
        return $this->render('admin/operaciones/reservas/pago_manual', $data);
    }

    public function confirmarPago()
    {
        try {
            // Verificar datos de pago en sesión
            if (!isset($_SESSION['datos_pago'])) {
                throw new \Exception('No se encontraron datos de pago válidos');
            }

            $datosPago = $_SESSION['datos_pago'];
            $reservaId = $datosPago['reserva_id'];
            
            // Obtener datos temporales para el total
            $reservaTemporal = $_SESSION['reserva_temporal'] ?? $_SESSION['reserva_temporal_basica'] ?? null;
            if (!$reservaTemporal) {
                throw new \Exception('Datos temporales de reserva no encontrados');
            }
            
            // Preparar datos de pago para la transacción
            $paymentData = [
                'metodo_pago_id' => $datosPago['metodo_pago_id']
            ];
            
            // TRANSACCIÓN CRÍTICA: Confirmar pago + Generar factura + Cambiar estados
            // Esta transacción incluye: insertar pago, generar factura, cambiar estado reserva y cabaña
            error_log("INFO: Iniciando transacción de confirmación de pago para reserva ID: $reservaId");
            error_log("DEBUG: Datos de pago para transacción: " . json_encode($paymentData));
            error_log("DEBUG: Datos temporales disponibles: " . json_encode($reservaTemporal));
            
            $resultado = $this->reservaModel->confirmPayment($reservaId, $paymentData);
            
            if (!$resultado['success']) {
                throw new \Exception($resultado['message'] ?? 'Error procesando el pago');
            }
            
            error_log("INFO: Transacción de confirmación completada exitosamente");
            
            // Obtener total general desde el resultado de la transacción
            $totalGeneral = $resultado['total_pagado'] ?? $reservaTemporal['total_general'] ?? $reservaTemporal['subtotal_alojamiento'] ?? 0;
            
            // Enviar notificación por email (opcional, fuera de la transacción para no afectar el proceso crítico)
            try {
                $reserva = $this->reservaModel->find($reservaId);
                $this->enviarNotificacionConfirmacion($reserva);
                error_log("INFO: Email de confirmación enviado exitosamente");
            } catch (\Exception $emailError) {
                // Log error pero no fallar el proceso de pago ya completado
                error_log('WARNING: Error enviando email de confirmación (proceso de pago ya completado): ' . $emailError->getMessage());
            }
            
            // Guardar datos para página de éxito
            $_SESSION['reserva_exitosa'] = [
                'reserva_id' => $reservaId,
                'total_pagado' => $totalGeneral,
                'fecha_confirmacion' => $resultado['fecha_confirmacion'],
                'metodo_pago_id' => $paymentData['metodo_pago_id'],
                'pago_id' => $resultado['pago_id'],
                'factura_id' => $resultado['factura_id']
            ];
            
            // Limpiar datos de sesión
            unset($_SESSION['reserva_temporal']);
            unset($_SESSION['reserva_temporal_basica']);
            unset($_SESSION['servicios_seleccionados']);
            unset($_SESSION['datos_pago']);
            unset($_SESSION['pago_datos']);
            
            // Redirigir a página de éxito
            $this->redirect('/reservas/exito');
            
        } catch (\Exception $e) {
            error_log('Error confirmando pago: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Error procesando el pago: ' . $e->getMessage() . '. Por favor, intente nuevamente o contacte al soporte.';
            $this->redirect('/reservas/resumen');
        }
    }
    
    /**
     * Enviar email de confirmación de reserva con información completa
     */
    private function enviarNotificacionConfirmacion($reserva) {
        try {
            // Obtener datos completos de la reserva usando el método interno del modelo
            $reservaCompleta = $this->obtenerDatosCompletosReserva($reserva['id_reserva']);
            
            if (!$reservaCompleta) {
                throw new \Exception("No se pudieron obtener los datos completos de la reserva");
            }
            
            // Debug: verificar que tenemos email válido
            error_log("DEBUG: Datos de reserva para email - ID: " . $reservaCompleta['reserva_id'] . 
                     ", Email: '" . ($reservaCompleta['huesped_email'] ?? 'VACÍO') . 
                     "', Nombre: '" . ($reservaCompleta['huesped_nombre_completo'] ?? 'VACÍO') . "'");
            
            // Si no hay email, intentar obtenerlo directamente de la BD como último recurso
            if (empty($reservaCompleta['huesped_email'])) {
                error_log("WARNING: Intentando obtener email como último recurso para reserva " . $reserva['id_reserva']);
                
                try {
                    $database = \App\Core\Database::getInstance();
                    
                    // Consulta directa para obtener email
                    $sqlDirecto = "SELECT CONCAT(p.persona_nombre, ' ', p.persona_apellido) as nombre_completo,
                                          c.contacto_descripcion as email
                                  FROM reserva r
                                  INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                                  INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                                  INNER JOIN persona p ON h.rela_persona = p.id_persona
                                  INNER JOIN contacto c ON p.id_persona = c.rela_persona 
                                      AND c.rela_tipocontacto = 1 AND c.contacto_estado = 1
                                  WHERE r.id_reserva = ? AND c.contacto_descripcion IS NOT NULL AND c.contacto_descripcion != ''
                                  LIMIT 1";
                    
                    $stmtDirecto = $database->prepare($sqlDirecto);
                    $stmtDirecto->execute([$reserva['id_reserva']]);
                    $emailDirecto = $stmtDirecto->fetch();
                    
                    if ($emailDirecto && !empty($emailDirecto['email'])) {
                        $reservaCompleta['huesped_email'] = $emailDirecto['email'];
                        $reservaCompleta['huesped_nombre_completo'] = $emailDirecto['nombre_completo'];
                        error_log("SUCCESS: Email obtenido como último recurso: " . $emailDirecto['email']);
                    }
                } catch (\Exception $e) {
                    error_log("ERROR: Fallo último recurso obtener email: " . $e->getMessage());
                }
                
                // Si aún no hay email, abortar
                if (empty($reservaCompleta['huesped_email'])) {
                    throw new \Exception("Email del huésped no disponible para la reserva ID: " . $reserva['id_reserva']);
                }
            }
            
            // Configurar y enviar email
            $emailService = new \App\Core\EmailService();
            
            $subject = "Confirmación de Reserva - Casa de Palos Cabañas";
            $htmlBody = $this->construirEmailConfirmacion($reservaCompleta);
            $textBody = $this->construirEmailConfirmacionTexto($reservaCompleta);
            
            // Enviar email al huésped
            $result = $emailService->sendEmail(
                $reservaCompleta['huesped_email'],
                $reservaCompleta['huesped_nombre_completo'],
                $subject,
                $htmlBody,
                $textBody
            );
            
            if ($result['success']) {
                error_log("Email de confirmación enviado exitosamente a: " . $reservaCompleta['huesped_email']);
                return true;
            } else {
                error_log("Error enviando email: " . $result['message']);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log('Error enviando email de confirmación: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener datos completos de la reserva para email
     */
    private function obtenerDatosCompletosReserva($reservaId) {
        try {
            // Obtener información básica de la reserva
            $reserva = $this->reservaModel->find($reservaId);
            if (!$reserva) {
                return null;
            }

            // Obtener información de la cabaña
            $cabania = $this->cabaniaModel->find($reserva['rela_cabania']);
            
            // Obtener información del huésped con contactos
            $huesped = $this->obtenerHuespedReserva($reservaId);
            error_log("DEBUG: Datos de huésped obtenidos: " . json_encode($huesped));
            
            // Obtener método de pago
            $metodoPago = $this->obtenerMetodoPagoReserva($reservaId);
            
            // Obtener total pagado
            $totalPagado = $this->obtenerTotalPagadoReserva($reservaId);
            
            // Calcular días de estadía
            $fechaInicio = new \DateTime($reserva['reserva_fhinicio']);
            $fechaFin = new \DateTime($reserva['reserva_fhfin']);
            $dias = $fechaInicio->diff($fechaFin)->days;
            
            // Debug: Log de datos de huéspedes
            $adultos = $reserva['reserva_adultos'] ?? 0;
            $menores = $reserva['reserva_ninos'] ?? 0;
            error_log("DEBUG: Datos de huéspedes en reserva - Adultos: '$adultos', Menores: '$menores'");
            
            $resultado = [
                'reserva_id' => $reserva['id_reserva'],
                'fecha_llegada' => $fechaInicio->format('d/m/Y'),
                'fecha_salida' => $fechaFin->format('d/m/Y'),
                'dias_estancia' => $dias,
                'cabania_nombre' => $cabania['cabania_nombre'] ?? 'No especificada',
                'cabania_codigo' => $cabania['cabania_codigo'] ?? '',
                'huesped_nombre_completo' => $huesped['nombre_completo'] ?? 'Usuario',
                'huesped_email' => $huesped['email'] ?? '',
                'metodo_pago' => $metodoPago['descripcion'] ?? 'No especificado',
                'monto_pagado' => $totalPagado ?? 0,
                'fecha_confirmacion' => date('d/m/Y H:i:s'),
                'adultos' => intval($adultos),
                'menores' => intval($menores),
                'estado_reserva' => 'Confirmada'
            ];
            
            // Log final para debug
            error_log("DEBUG: Datos finales para email - Email: '" . $resultado['huesped_email'] . "', Nombre: '" . $resultado['huesped_nombre_completo'] . "', Adultos: " . $resultado['adultos'] . ", Menores: " . $resultado['menores']);
            
            return $resultado;
            
        } catch (\Exception $e) {
            error_log('Error obteniendo datos completos de reserva: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener información del huésped de una reserva
     */
    private function obtenerHuespedReserva($reservaId) {
        try {
            $database = \App\Core\Database::getInstance();
            
            // Usar la consulta que YA CONFIRMAMOS que funciona
            $sql = "SELECT CONCAT(p.persona_nombre, ' ', p.persona_apellido) as nombre_completo,
                           c.contacto_descripcion as email
                    FROM reserva r
                    INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN contacto c ON p.id_persona = c.rela_persona 
                        AND c.rela_tipocontacto = 1 AND c.contacto_estado = 1
                    WHERE r.id_reserva = ?
                    LIMIT 1";

            // Para MySQLi necesitamos hacer bind y get_result
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            error_log("DEBUG obtenerHuespedReserva: Resultado consulta: " . json_encode($result));
            
            if ($result && !empty($result['email'])) {
                error_log("INFO: Email encontrado para reserva $reservaId: " . $result['email']);
                return $result;
            }
            
            error_log("WARNING: No se encontró email para reserva $reservaId");
            return ['nombre_completo' => 'Usuario', 'email' => ''];
            
        } catch (\Exception $e) {
            error_log('Error obteniendo huésped: ' . $e->getMessage());
            return ['nombre_completo' => 'Usuario', 'email' => ''];
        }
    }

    /**
     * Obtener método de pago de una reserva
     */
    private function obtenerMetodoPagoReserva($reservaId) {
        try {
            $database = \App\Core\Database::getInstance()->getConnection();
            $sql = "SELECT mp.metododepago_descripcion as descripcion
                    FROM pago p
                    INNER JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
                    WHERE p.rela_reserva = ?
                    ORDER BY p.id_pago DESC
                    LIMIT 1";

            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result ?: ['descripcion' => 'Tarjeta de Crédito/Débito'];
            
        } catch (\Exception $e) {
            error_log('Error obteniendo método de pago: ' . $e->getMessage());
            return ['descripcion' => 'Tarjeta de Crédito/Débito'];
        }
    }

    /**
     * Obtener total pagado de una reserva
     */
    private function obtenerTotalPagadoReserva($reservaId) {
        try {
            $database = \App\Core\Database::getInstance()->getConnection();
            $sql = "SELECT SUM(p.pago_total) as total
                    FROM pago p
                    WHERE p.rela_reserva = ?";

            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            return $row['total'] ?? 0;
            
        } catch (\Exception $e) {
            error_log('Error obteniendo total pagado: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener datos del complejo desde la configuración
     */
    private function obtenerDatosComplejo() {
        $config = require __DIR__ . '/../Core/config.php';
        return $config['complejo'] ?? [
            'nombre' => 'Casa de Palos Cabañas',
            'direccion' => 'Dirección del complejo',
            'telefono' => 'Teléfono de contacto',
            'email' => 'info@casadepaloscabanias.com',
            'website' => 'www.casadepaloscabanias.com',
            'politicas' => [
                'check_in' => '15:00',
                'check_out' => '10:00',
                'mascotas' => 'No se permiten mascotas',
                'fumar' => 'No fumar en las instalaciones',
                'limpieza' => 'Mantener el orden y la limpieza'
            ]
        ];
    }

    /**
     * Renderizar vista de email
     */
    private function renderEmailView($viewPath, $data = []) {
        // Extraer variables para la vista
        extract($data);
        
        // Capturar el output de la vista
        ob_start();
        include __DIR__ . '/../Views/' . $viewPath . '.php';
        return ob_get_clean();
    }

    /**
     * Construir template HTML del email de confirmación
     */
    private function construirEmailConfirmacion($datos) {
        $complejo = $this->obtenerDatosComplejo();
        
        return $this->renderEmailView('shared/emails/reserva_confirmacion', [
            'datos' => $datos,
            'complejo' => $complejo,
            'formato' => 'html'
        ]);
    }

    /**
     * Construir versión de texto plano del email
     */
    private function construirEmailConfirmacionTexto($datos) {
        $complejo = $this->obtenerDatosComplejo();
        
        return $this->renderEmailView('shared/emails/reserva_confirmacion', [
            'datos' => $datos,
            'complejo' => $complejo,
            'formato' => 'texto'
        ]);
    }

    public function exito()
    {
        $this->requireAuth();
        
        // Debug: Verificar contenido de la sesión
        error_log("DEBUG: Contenido completo de sesión en exito(): " . print_r($_SESSION, true));
        
        // Verificar que existan datos de reserva exitosa (por sesión o por parámetro)
        $reservaId = $this->get('id');
        
        if (!isset($_SESSION['reserva_exitosa']) && !$reservaId) {
            error_log("DEBUG: No se encontró reserva_exitosa en sesión ni ID en URL, redirigiendo al catálogo");
            $this->redirect('/catalogo', 'No hay información de reserva disponible', 'error');
            return;
        }
        
        // Priorizar datos de sesión, pero usar ID de URL como fallback
        if (isset($_SESSION['reserva_exitosa'])) {
            $reservaExitosa = $_SESSION['reserva_exitosa'];
        } else {
            // Crear datos básicos desde la base de datos
            $reservaExitosa = [
                'reserva_id' => $reservaId,
                'fecha_confirmacion' => date('Y-m-d H:i:s')
            ];
        }
        
        // Obtener información completa de la reserva
        $reserva = $this->reservaModel->find($reservaExitosa['reserva_id']);
        $cabania = null;
        $metodoPago = null;
        
        if ($reserva) {
            $cabania = $this->cabaniaModel->find($reserva['rela_cabania']);
            
            // Agregar nombre de la cabaña a reserva_exitosa si no está
            if (!isset($reservaExitosa['cabania_nombre']) && $cabania) {
                $reservaExitosa['cabania_nombre'] = $cabania['cabania_nombre'];
            }
            
            // Obtener método de pago si existe
            if (isset($reservaExitosa['metodo_pago_id'])) {
                $metodoPagoModel = new \App\Models\MetodoPago();
                $metodoPago = $metodoPagoModel->find($reservaExitosa['metodo_pago_id']);
                if ($metodoPago) {
                    $reservaExitosa['metodo_pago'] = $metodoPago['metododepago_descripcion'];
                }
            }
            
            // Obtener email del huésped usando nuestros métodos mejorados
            try {
                error_log("DEBUG EXITO: Intentando obtener datos completos para reserva " . $reservaExitosa['reserva_id']);
                
                $datosCompletos = $this->obtenerDatosCompletosReserva($reservaExitosa['reserva_id']);
                
                error_log("DEBUG EXITO: Datos completos obtenidos: " . json_encode($datosCompletos));
                
                if ($datosCompletos && !empty($datosCompletos['huesped_email'])) {
                    $reserva['huesped_email'] = $datosCompletos['huesped_email'];
                    $reserva['huesped_nombre_completo'] = $datosCompletos['huesped_nombre_completo'];
                    error_log("DEBUG EXITO: Email asignado a reserva: " . $datosCompletos['huesped_email']);
                } else {
                    error_log("DEBUG EXITO: Datos completos vacíos, probando fallback");
                    
                    // Fallback: usar método del modelo si existe
                    $reflection = new \ReflectionClass($this->reservaModel);
                    $method = $reflection->getMethod('getReservaCompleteData');
                    $method->setAccessible(true);
                    
                    $reservaCompleta = $method->invoke($this->reservaModel, $reservaExitosa['reserva_id']);
                    
                    error_log("DEBUG EXITO: Fallback resultado: " . json_encode($reservaCompleta));
                    
                    if ($reservaCompleta && isset($reservaCompleta['email_persona'])) {
                        $reserva['huesped_email'] = $reservaCompleta['email_persona'];
                        error_log("DEBUG EXITO: Email obtenido via fallback: " . $reservaCompleta['email_persona']);
                    } else {
                        error_log("DEBUG EXITO: Fallback también falló");
                    }
                }
                
                error_log("DEBUG EXITO: Estado final - reserva[huesped_email]: '" . ($reserva['huesped_email'] ?? 'NO DEFINIDO') . "'");
                
            } catch (\Exception $e) {
                error_log("ERROR EXITO: " . $e->getMessage() . " en línea " . $e->getLine());
            }
        }
        
        $data = [
            'title' => 'Reserva Confirmada',
            'reserva_exitosa' => $reservaExitosa,
            'reserva' => $reserva,
            'cabania' => $cabania,
            'metodo_pago' => $metodoPago,
            'isAdminArea' => false
        ];
        
        // Limpiar datos de éxito después de mostrar la vista
        unset($_SESSION['reserva_exitosa']);
        
        return $this->render('public/reservas/exito', $data, 'main');
    }

    /* OBSOLETO: método storeOnline() removido
     * Las reservas online ahora se procesan através del flujo público:
     * /catalogo -> /reservas/confirmar -> /reservas/servicios -> /reservas/resumen -> /reservas/pasarela
     */

    public function confirm($id)
    {
        $this->requirePermission('reservas');
        $result = $this->reservaModel->confirm($id);
        $message = $result['success'] ? $result['message'] : $result['message'];
        $type = $result['success'] ? 'exito' : 'error';
        $this->redirect('/reservas', $message, $type);
    }

    /**
     * Cancelar reserva por parte del huésped (estado CANCELADA)
     */
    public function cancelarReserva($id)
    {
        try {
            $reserva = $this->reservaModel->find($id);
            
            if (!$reserva) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
                return;
            }
            
            // Solo se pueden cancelar reservas PENDIENTES o CONFIRMADAS
            if (!$this->estadoReservaModel->puedeSerCancelada($reserva['rela_estadoreserva'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No se puede cancelar esta reserva']);
                return;
            }
            
            // Actualizar estado a CANCELADA
            $estadoCancelada = $this->estadoReservaModel->getId(EstadoReserva::CANCELADA);
            $result = $this->reservaModel->update($id, [
                'rela_estadoreserva' => $estadoCancelada
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Reserva cancelada exitosamente']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al cancelar la reserva']);
            }
            
        } catch (\Exception $e) {
            error_log('Error cancelando reserva: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Anular reserva por parte del administrador (estado ANULADA)
     */
    public function anularReserva($id)
    {
        $this->requirePermission('reservas');
        
        try {
            $reserva = $this->reservaModel->find($id);
            
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Se pueden anular reservas según la lógica del modelo
            if (!$this->estadoReservaModel->puedeSerAnulada($reserva['rela_estadoreserva'])) {
                $this->redirect('/reservas', 'No se puede anular esta reserva', 'error');
                return;
            }
            
            // Actualizar estado a ANULADA
            $estadoAnulada = $this->estadoReservaModel->getId(EstadoReserva::ANULADA);
            $result = $this->reservaModel->update($id, [
                'rela_estadoreserva' => $estadoAnulada
            ]);
            
            if ($result) {
                $this->redirect('/reservas', 'Reserva anulada exitosamente', 'exito');
            } else {
                $this->redirect('/reservas', 'Error al anular la reserva', 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Error anulando reserva: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error interno del servidor', 'error');
        }
    }
    
    /**
     * Mostrar las reservas del usuario logueado
     */
    public function misReservas()
    {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login', 'Debe iniciar sesión para ver sus reservas', 'info');
            return;
        }
        
        try {
            // Buscar persona asociada al usuario
            $usuarioId = $_SESSION['usuario_id'];
            $sql = "SELECT p.id_persona FROM persona p 
                    INNER JOIN usuario u ON p.id_persona = u.rela_persona 
                    WHERE u.id_usuario = ?";
            
            $database = \App\Core\Database::getInstance();
            $stmt = $database->prepare($sql);
            $stmt->execute([$usuarioId]);
            $persona = $stmt->fetch();
            
            if (!$persona) {
                $this->redirect('/', 'No se encontró información de perfil', 'error');
                return;
            }
            
            // Obtener reservas del usuario con detalles
            $sqlReservas = "SELECT r.*, 
                                   c.cabania_nombre, c.cabania_codigo,
                                   er.estadoreserva_descripcion,
                                   p.persona_nombre, p.persona_apellido
                            FROM reserva r
                            INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                            INNER JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                            INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                            INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                            INNER JOIN persona p ON h.rela_persona = p.id_persona
                            WHERE p.id_persona = ?
                            ORDER BY r.reserva_fhinicio DESC";
            
            $stmt = $database->prepare($sqlReservas);
            $stmt->execute([$persona['id_persona']]);
            
            $reservas = [];
            while ($row = $stmt->fetch()) {
                $reservas[] = $row;
            }
            
            $data = [
                'title' => 'Mis Reservas',
                'reservas' => $reservas
            ];
            
            return $this->render('public/reservas/mis-reservas', $data);
            
        } catch (\Exception $e) {
            error_log('Error obteniendo reservas del usuario: ' . $e->getMessage());
            $this->redirect('/', 'Error interno del servidor', 'error');
        }
    }
    
    /**
     * Método legacy para compatibilidad
     * @deprecated Usar cancelarReserva() o anularReserva() según corresponda
     */
    public function cancel($id)
    {
        $this->requirePermission('reservas');
        return $this->anularReserva($id);
    }

    public function getAvailableCabins()
    {
        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $fechaInicio = $this->post('fecha_inicio', '');
        $fechaFin = $this->post('fecha_fin', '');
        if (empty($fechaInicio) || empty($fechaFin)) {
            http_response_code(400);
            echo json_encode(['error' => 'Fechas requeridas']);
            return;
        }
        try {
            $cabanias = $this->reservaModel->getAvailableCabins($fechaInicio, $fechaFin);
            header('Content-Type: application/json');
            echo json_encode($cabanias);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Verificar si una reserva pendiente ha expirado
     */
    private function reservaExpirada($reservaId)
    {
        $reserva = $this->reservaModel->find($reservaId);
        
        if (!$reserva) {
            return true; // Si no existe, considerar expirada
        }
        
        // Si ya está marcada como EXPIRADA
        if ($this->estadoReservaModel->estaExpirada($reserva['rela_estadoreserva'])) {
            return true;
        }
        
        // Si nunca expira según la lógica del modelo
        if ($this->estadoReservaModel->nuncaExpira($reserva['rela_estadoreserva'])) {
            return false;
        }
        
        // Si puede expirar, verificar fecha de expiración
        if ($this->estadoReservaModel->puedeExpirar($reserva['rela_estadoreserva']) && $reserva['reserva_fhexpiracion']) {
            return strtotime($reserva['reserva_fhexpiracion']) < time();
        }
        
        return false;
    }
    
    /**
     * Verificar disponibilidad de cabaña excluyendo reserva específica
     */
    private function cabaniaDisponible($cabaniaId, $fechaInicio, $fechaFin, $excluirReservaId = null)
    {
        try {
            $database = \App\Core\Database::getInstance();
            
            // Obtener estados que bloquean disponibilidad usando el modelo
            $estadosQueBloquean = $this->estadoReservaModel->getEstadosQueBloquean();
            
            if (empty($estadosQueBloquean)) {
                return true; // Si no hay estados que bloqueen, está disponible
            }
            
            $estadosPlaceholders = str_repeat('?,', count($estadosQueBloquean) - 1) . '?';
            
            $sql = "SELECT COUNT(*) as conflictos FROM reserva 
                    WHERE rela_cabania = ? 
                    AND rela_estadoreserva IN ($estadosPlaceholders)
                    AND (reserva_fhinicio < ? AND reserva_fhfin > ?)";
            
            $params = array_merge([$cabaniaId], $estadosQueBloquean, [$fechaFin, $fechaInicio]);
            
            // Excluir reserva específica si se proporciona
            if ($excluirReservaId) {
                $sql .= " AND id_reserva != ?";
                $params[] = $excluirReservaId;
            }
            
            $stmt = $database->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            // Verificar que se obtuvo resultado válido
            if ($result === false) {
                return true; // Si no hay resultados, no hay conflictos
            }
            
            return $result['conflictos'] == 0;
            
        } catch (\Exception $e) {
            error_log('Error verificando disponibilidad: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear una reserva temporal en la base de datos
     */
    private function crearReservaTemporal($datosReserva)
    {
        try {
            error_log("DEBUG crearReservaTemporal: Iniciando con datos: " . json_encode($datosReserva));
            
            // 0. Limpiar reservas pendientes expiradas antes de verificar disponibilidad
            $this->limpiarReservasExpiradas();
            
            // Calcular fecha de expiración (20 minutos desde ahora)
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            
            // 1. Preparar datos para crear la reserva
            $reservaData = [
                'reserva_online' => 1, // Marcar como reserva online
                'reserva_fhinicio' => $datosReserva['fecha_ingreso'],
                'reserva_fhfin' => $datosReserva['fecha_salida'],
                'rela_cabania' => $datosReserva['cabania_id'],
                'rela_estadoreserva' => 1, // Estado PENDIENTE
                'rela_periodo' => 1,  // Periodo por defecto (podría calcularse según fechas)
                'reserva_fhexpiracion' => $fechaExpiracion,
                'rela_persona' => $datosReserva['id_persona']
            ];
            
            error_log("DEBUG crearReservaTemporal: Datos de reserva preparados: " . json_encode($reservaData));

            // 2. Obtener servicios seleccionados de la sesión si existen
            $servicios = [];
            if (isset($datosReserva['servicios']) && !empty($datosReserva['servicios'])) {
                $servicios = $datosReserva['servicios'];
                error_log("DEBUG crearReservaTemporal: Servicios encontrados: " . count($servicios) . " servicios");
                error_log("DEBUG crearReservaTemporal: Servicios detalle: " . json_encode($servicios));
            } else {
                error_log("DEBUG crearReservaTemporal: No hay servicios seleccionados");
            }
            
            // 3. Crear reserva con servicios en una sola transacción atómica
            // TRANSACCIÓN CRÍTICA: Reserva + Servicios como consumos en una sola operación
            error_log("DEBUG crearReservaTemporal: Iniciando transacción para crear reserva con servicios");
            $reservaId = $this->reservaModel->createReservationWithServices($reservaData, $servicios);
            
            if (!$reservaId) {
                throw new \Exception("Error al crear la reserva con servicios - ID nulo");
            }
            
            error_log("DEBUG crearReservaTemporal: Reserva creada exitosamente con ID: $reservaId");
            
            return $reservaId;
            
        } catch (\Exception $e) {
            error_log("ERROR crearReservaTemporal: " . $e->getMessage());
            error_log("ERROR crearReservaTemporal stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    // Métodos auxiliares para obtener datos específicos según el perfil
    
    private function getTotalReservas()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as total FROM reserva");
        return $result->fetch_assoc()['total'];
    }
    
    private function getReservasActivas()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as total FROM reserva WHERE rela_estadoreserva IN (1, 2, 3) AND reserva_fhfin >= CURDATE()");
        return $result->fetch_assoc()['total'];
    }
    
    private function getIngresosMes()
    {
        $db = \App\Core\Database::getInstance();
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $stmt = $db->prepare("SELECT COALESCE(SUM(factura_total), 0) as total FROM factura WHERE DATE(factura_fechahora) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $inicioMes, $finMes);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    
    private function getOcupacionPromedio()
    {
        $db = \App\Core\Database::getInstance();
        $totalCabanias = $db->query("SELECT COUNT(*) as total FROM cabania WHERE cabania_estado IN (1, 2)")->fetch_assoc()['total'];
        $ocupadas = $db->query("SELECT COUNT(*) as ocupadas FROM cabania WHERE cabania_estado = 2")->fetch_assoc()['ocupadas'];
        return $totalCabanias > 0 ? round(($ocupadas / $totalCabanias) * 100, 1) : 0;
    }
    
    private function getReservasPendientesPago()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido, c.cabania_precio
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE r.rela_estadoreserva = 1
                               ORDER BY r.reserva_fhinicio ASC");
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getFacturasHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM factura WHERE DATE(factura_fechahora) = ?");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    
    private function getIngresosHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT COALESCE(SUM(factura_total), 0) as total FROM factura WHERE DATE(factura_fechahora) = ?");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    
    private function getMetodosPagoMes()
    {
        try {
            $db = \App\Core\Database::getInstance();
            $inicioMes = date('Y-m-01');
            $finMes = date('Y-m-t');
            $stmt = $db->prepare("SELECT mdp.metododepago_descripcion, COUNT(*) as cantidad, SUM(p.pago_total) as total
                                   FROM pago p
                                   LEFT JOIN metododepago mdp ON p.rela_metododepago = mdp.id_metododepago
                                   WHERE DATE(p.pago_fechahora) BETWEEN ? AND ?
                                   GROUP BY mdp.metododepago_descripcion");
            $stmt->bind_param("ss", $inicioMes, $finMes);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $metodos = [];
            while ($row = $result->fetch_assoc()) {
                $metodos[] = $row;
            }
            return $metodos;
        } catch (\Exception $e) {
            error_log("Error en getMetodosPagoMes: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCheckinsHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhinicio) = ? AND r.rela_estadoreserva = 2
                               ORDER BY r.reserva_fhinicio ASC");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $checkins = [];
        while ($row = $result->fetch_assoc()) {
            $checkins[] = $row;
        }
        return $checkins;
    }
    
    private function getCheckoutsHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhfin) = ? AND r.rela_estadoreserva IN (2, 3)
                               ORDER BY r.reserva_fhfin ASC");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $checkouts = [];
        while ($row = $result->fetch_assoc()) {
            $checkouts[] = $row;
        }
        return $checkouts;
    }
    
    private function getReservasHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhinicio) = ?
                               ORDER BY r.reserva_fhinicio ASC");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getReservasHuesped($personaId)
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, c.cabania_codigo, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               WHERE h.rela_persona = ?
                               ORDER BY r.reserva_fhinicio DESC");
        $stmt->bind_param("i", $personaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getReservasProximasHuesped($personaId)
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, c.cabania_codigo, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               WHERE h.rela_persona = ? 
                               AND r.reserva_fhinicio >= CURDATE()
                               AND r.rela_estadoreserva IN (1, 2)
                               ORDER BY r.reserva_fhinicio ASC
                               LIMIT 3");
        $stmt->bind_param("i", $personaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getHistorialReservasHuesped($personaId)
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, c.cabania_codigo, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               WHERE h.rela_persona = ? 
                               AND r.reserva_fhfin < CURDATE()
                               ORDER BY r.reserva_fhinicio DESC
                               LIMIT 5");
        $stmt->bind_param("i", $personaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    /**
     * Limpiar reservas PENDIENTES expiradas
     * Este método debería ejecutarse periódicamente (cron job)
     * Cambia el estado de reservas pendientes expiradas a EXPIRADA
     */
    public function limpiarReservasExpiradas()
    {
        try {
            $database = \App\Core\Database::getInstance();
            
            // Marcar como EXPIRADAS las reservas pendientes que hayan expirado
            $estadoReservaModel = new EstadoReserva();
            $estadoExpirada = $estadoReservaModel->getId(EstadoReserva::EXPIRADA);
            $estadoPendiente = $estadoReservaModel->getId(EstadoReserva::PENDIENTE);
            
            $sql = "UPDATE reserva 
                    SET rela_estadoreserva = ? 
                    WHERE rela_estadoreserva = ? 
                    AND reserva_fhexpiracion < NOW()";
            
            $stmt = $database->prepare($sql);
            $stmt->bind_param('ii', $estadoExpirada, $estadoPendiente);
            $result = $stmt->execute();
            
            $expiradas = $stmt->affected_rows;
            
            if ($expiradas > 0) {
                error_log("Limpieza automática: $expiradas reservas pendientes marcadas como expiradas");
            }
            
            return ['success' => true, 'expiradas' => $expiradas];
            
        } catch (\Exception $e) {
            error_log('Error limpiando reservas expiradas: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Control de permisos por perfil específico de reservas
     */
    private function hasReservationPermission($action, $userProfile)
    {
        $permissions = [
            'administrador' => [
                'view_all', 'create', 'edit', 'delete', 'manage_payments', 
                'manage_invoices', 'manage_guests', 'view_reports', 'manage_online_reservations'
            ],
            'cajero' => [
                'view_all', 'create', 'edit', 'manage_payments', 'manage_invoices', 'view_reports'
            ],
            'recepcionista' => [
                'view_all', 'create', 'edit', 'manage_guests', 'manage_checkin', 'manage_checkout'
            ],
            'huesped' => [
                'view_own', 'create_own', 'cancel_own'
            ]
        ];

        return isset($permissions[$userProfile]) && in_array($action, $permissions[$userProfile]);
    }

    /**
     * Verificar si el usuario puede acceder a una funcionalidad específica de reservas
     */
    private function requireReservationPermission($action)
    {
        $userProfile = \App\Core\Auth::getUserProfile();
        
        if (!$this->hasReservationPermission($action, $userProfile)) {
            $this->redirect('/dashboard', 'No tiene permisos para realizar esta acción', 'error');
            return false;
        }
        
        return true;
    }

    /**
     * Gestión específica de facturación para cajeros
     */
    public function facturacion()
    {
        $this->requireAuth();
        if (!$this->requireReservationPermission('manage_invoices')) {
            return;
        }

        $reservasPendientes = $this->reservaModel->getByStatus(1); // Estado: Confirmada
        $facturas = $this->getFacturasRecientes();

        $data = [
            'title' => 'Gestión de Facturación',
            'userProfile' => \App\Core\Auth::getUserProfile(),
            'reservas_pendientes' => $reservasPendientes,
            'facturas_recientes' => $facturas
        ];

        return $this->render('admin/operaciones/reservas/listado', $data);
    }

    /**
     * Gestión específica de pagos para cajeros
     */
    public function pagos()
    {
        $this->requireAuth();
        if (!$this->requireReservationPermission('manage_payments')) {
            return;
        }

        $pagosPendientes = $this->getReservasPendientesPago();
        $metodosPago = $this->getMetodosPagoPorPerfil('cajero');

        $data = [
            'title' => 'Gestión de Pagos',
            'userProfile' => \App\Core\Auth::getUserProfile(),
            'pagos_pendientes' => $pagosPendientes,
            'metodos_pago' => $metodosPago
        ];

        return $this->render('admin/operaciones/reservas/listado', $data);
    }

    /**
     * Gestión específica de huéspedes para recepcionistas
     */
    public function huespedes()
    {
        $this->requireAuth();
        if (!$this->requireReservationPermission('manage_guests')) {
            return;
        }

        $checkinsHoy = $this->getCheckinsHoy();
        $checkoutsHoy = $this->getCheckoutsHoy();

        $data = [
            'title' => 'Gestión de Huéspedes',
            'userProfile' => \App\Core\Auth::getUserProfile(),
            'checkins_hoy' => $checkinsHoy,
            'checkouts_hoy' => $checkoutsHoy
        ];

        return $this->render('admin/operaciones/reservas/listado', $data);
    }

    /**
     * Check-in específico para recepcionistas
     */
    public function checkin($reservaId = null)
    {
        $this->requireAuth();
        if (!$this->requireReservationPermission('manage_checkin')) {
            return;
        }

        if (!$reservaId) {
            $this->redirect('/reservas/huespedes', 'ID de reserva requerido', 'error');
            return;
        }

        $reserva = $this->reservaModel->find($reservaId);
        if (!$reserva) {
            $this->redirect('/reservas/huespedes', 'Reserva no encontrada', 'error');
            return;
        }

        if ($this->isPost()) {
            // Procesar check-in
            try {
                $this->reservaModel->update($reservaId, ['rela_estadoreserva' => 3]); // Estado: En curso
                $this->redirect('/reservas/huespedes', 'Check-in realizado exitosamente', 'success');
            } catch (\Exception $e) {
                $this->redirect('/reservas/huespedes', 'Error al realizar check-in: ' . $e->getMessage(), 'error');
            }
            return;
        }

        $data = [
            'title' => 'Check-in Reserva #' . $reservaId,
            'reserva' => $reserva
        ];

        return $this->render('admin/operaciones/reservas/detalle', $data);
    }

    /**
     * Check-out específico para recepcionistas
     */
    public function checkout($reservaId = null)
    {
        $this->requireAuth();
        if (!$this->requireReservationPermission('manage_checkout')) {
            return;
        }

        if (!$reservaId) {
            $this->redirect('/reservas/huespedes', 'ID de reserva requerido', 'error');
            return;
        }

        $reserva = $this->reservaModel->find($reservaId);
        if (!$reserva) {
            $this->redirect('/reservas/huespedes', 'Reserva no encontrada', 'error');
            return;
        }

        if ($this->isPost()) {
            // Procesar check-out
            try {
                $this->reservaModel->update($reservaId, ['rela_estadoreserva' => 4]); // Estado: Finalizada
                $this->redirect('/reservas/huespedes', 'Check-out realizado exitosamente', 'success');
            } catch (\Exception $e) {
                $this->redirect('/reservas/huespedes', 'Error al realizar check-out: ' . $e->getMessage(), 'error');
            }
            return;
        }

        $data = [
            'title' => 'Check-out Reserva #' . $reservaId,
            'reserva' => $reserva
        ];

        return $this->render('admin/operaciones/reservas/detalle', $data);
    }

    /**
     * Obtener facturas recientes para el dashboard del cajero
     */
    private function getFacturasRecientes($limit = 10)
    {
        try {
            $sql = "SELECT f.*, r.id_reserva, c.cabania_nombre 
                    FROM factura f 
                    LEFT JOIN reserva r ON f.rela_reserva = r.id_reserva 
                    LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania 
                    ORDER BY f.factura_fecha DESC LIMIT ?";
            
            // Retornar array vacío por ahora - se puede implementar específicamente después
            return [];
        } catch (\Exception $e) {
            error_log('Error obteniendo facturas recientes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Cancelar reserva (solo para huéspedes propias o administradores)
     */
    public function cancelar($reservaId = null)
    {
        $this->requireAuth();
        
        if (!$reservaId) {
            $this->redirect('/reservas', 'ID de reserva requerido', 'error');
            return;
        }

        $userProfile = \App\Core\Auth::getUserProfile();
        $reserva = $this->reservaModel->find($reservaId);
        
        if (!$reserva) {
            $this->redirect('/reservas', 'Reserva no encontrada', 'error');
            return;
        }

        // Verificar permisos: administrador puede cancelar cualquiera, huésped solo las propias
        if ($userProfile === 'huesped') {
            $userId = \App\Core\Auth::id();
            if (!$this->reservaModel->isReservaOwner($reservaId, $userId)) {
                $this->redirect('/reservas', 'No tiene permisos para cancelar esta reserva', 'error');
                return;
            }
        } elseif (!$this->hasReservationPermission('delete', $userProfile)) {
            $this->redirect('/reservas', 'No tiene permisos para realizar esta acción', 'error');
            return;
        }

        if ($this->isPost()) {
            try {
                $this->reservaModel->update($reservaId, ['rela_estadoreserva' => 5]); // Estado: Cancelada
                $this->redirect('/reservas', 'Reserva cancelada exitosamente', 'success');
            } catch (\Exception $e) {
                $this->redirect('/reservas', 'Error al cancelar reserva: ' . $e->getMessage(), 'error');
            }
            return;
        }

        $data = [
            'title' => 'Cancelar Reserva #' . $reservaId,
            'reserva' => $reserva
        ];

        return $this->render('admin/operaciones/reservas/detalle', $data);
    }

}