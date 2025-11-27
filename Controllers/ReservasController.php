<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\NotificationService;
use App\Models\Reserva;
use App\Models\Cabania;
use App\Models\Persona;
use App\Models\Servicio;
use App\Models\Consumo;
use App\Models\EstadoReserva;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

class ReservasController extends Controller
{
    protected $reservaModel;
    protected $cabaniaModel;
    protected $personaModel;
    protected $servicioModel;
    protected $consumoModel;
    protected $estadoReservaModel;
    protected $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->reservaModel = new Reserva();
        $this->cabaniaModel = new Cabania();
        $this->personaModel = new Persona();
        $this->notificationService = new NotificationService();
        $this->servicioModel = new Servicio();
        $this->consumoModel = new Consumo();
        $this->estadoReservaModel = new EstadoReserva();
    }

    public function index()
    {
        $this->requirePermission('reservas');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar perPage
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'reserva_nro' => $this->get('reserva_nro'),
            'estado' => $this->get('estado'),
            'cabania' => $this->get('cabania'),
            'fecha_alta' => $this->get('fecha_alta'),
            'fecha_inicio' => $this->get('fecha_inicio'),
            'fecha_fin' => $this->get('fecha_fin'),
            'persona' => $this->get('persona')
        ];

        $result = $this->reservaModel->getWithDetails($page, $perPage, $filters);
        $cabanias = $this->cabaniaModel->getActive();
        $estadosReserva = $this->estadoReservaModel->getActive();

        $data = [
            'title' => 'Gestión de Reservas',
            'reservas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'cabanias' => $cabanias,
            'estados_reserva' => $estadosReserva,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/reservas/listado', $data, 'main');
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
        // Obtener persona asociada al usuario
        $persona = $this->personaModel->findByUsuario(\App\Core\Auth::user());
        
        if (!$persona) {
            $this->redirect('/', 'No se encontraron datos de huésped', 'error');
            return;
        }
        
        // Verificar y notificar reservas con pago pendiente
        $this->checkAndNotifyPagosPendientes(\App\Core\Auth::id());
        
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
            'es_huesped' => $userModel->esPerfilHuesped(),
            'isAdminArea' => true
        ];
        return $this->render('admin/operaciones/reservas/formulario', $data, 'main');
    }

    public function store()
    {
        $this->requirePermission('reservas');
        
        // Solo campos que existen en la tabla reserva
        $data = [
            'reserva_online' => 0, // Marcar como reserva in-situ (admin)
            'rela_cabania' => $this->post('rela_cabania'),
            'reserva_fhinicio' => $this->post('reserva_fhinicio'),
            'reserva_fhfin' => $this->post('reserva_fhfin'),
            'rela_estadoreserva' => 1,
            'rela_periodo' => $this->post('rela_periodo', 1) // Periodo por defecto
        ];
        
        if (empty($data['rela_cabania']) || 
            empty($data['reserva_fhinicio']) || empty($data['reserva_fhfin'])) {
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
        // Obtener reserva
        $result = $this->reservaModel->getWithDetails(1, 1, ['id' => $id]);
        if (empty($result['data'])) {
            return $this->view->error(404);
        }
        $reserva = $result['data'][0];
        
        // Verificar permisos según perfil
        $perfil = \App\Core\Auth::getUserProfile();
        
        if ($perfil === 'huesped') {
            // Huéspedes solo pueden ver sus propias reservas
            $usuarioId = \App\Core\Auth::id();
            $persona = $this->personaModel->findByUsuario(\App\Core\Auth::user());
            
            if (!$persona) {
                return $this->view->error(403); // Acceso denegado
            }
            
            // Verificar que el huésped pertenece a esta reserva
            $sql = "SELECT COUNT(*) as count 
                    FROM huesped_reserva hr
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    WHERE hr.rela_reserva = ? AND h.rela_persona = ?";
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii', $id, $persona['id_persona']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                return $this->view->error(403); // No es su reserva
            }
            
            // Renderizar vista para huésped
            $consumos = $this->reservaModel->getConsumptions($id);
            $data = [
                'title' => 'Detalle de Reserva',
                'reserva' => $reserva,
                'consumos' => $consumos,
                'isAdminArea' => false,
                'isHuesped' => true
            ];
            return $this->render('admin/operaciones/reservas/detalle', $data, 'main');
        } else {
            // Administradores y recepcionistas requieren permiso
            $this->requirePermission('reservas');
            $consumos = $this->reservaModel->getConsumptions($id);
            
            // Obtener estadísticas
            $estadisticas = [
                'total_pagos' => 0,
                'monto_pagado' => 0,
                'total_servicios' => 0,
                'total_consumos' => count($consumos)
            ];
            
            // Consultar pagos
            $db = \App\Core\Database::getInstance();
            $sqlPagos = "SELECT COUNT(*) as total, COALESCE(SUM(p.pago_total), 0) as monto 
                        FROM pago p
                        INNER JOIN factura f ON p.rela_factura = f.id_factura
                        WHERE f.rela_reserva = ?";
            $stmtPagos = $db->prepare($sqlPagos);
            $stmtPagos->bind_param('i', $id);
            $stmtPagos->execute();
            $resultPagos = $stmtPagos->get_result();
            if ($rowPagos = $resultPagos->fetch_assoc()) {
                $estadisticas['total_pagos'] = $rowPagos['total'];
                $estadisticas['monto_pagado'] = $rowPagos['monto'];
            }
            
            // Consultar servicios (consumos de tipo servicio)
            $sqlServicios = "SELECT COUNT(*) as total 
                           FROM consumo WHERE rela_reserva = ? AND rela_servicio IS NOT NULL";
            $stmtServicios = $db->prepare($sqlServicios);
            $stmtServicios->bind_param('i', $id);
            $stmtServicios->execute();
            $resultServicios = $stmtServicios->get_result();
            if ($rowServicios = $resultServicios->fetch_assoc()) {
                $estadisticas['total_servicios'] = $rowServicios['total'];
            }
            
            $data = [
                'title' => 'Detalle de Reserva',
                'reserva' => $reserva,
                'consumos' => $consumos,
                'estadisticas' => $estadisticas,
                'isAdminArea' => true,
                'isHuesped' => false
            ];
            return $this->render('admin/operaciones/reservas/detalle', $data, 'main');
        }
    }

    public function edit($id)
    {
        $this->requirePermission('reservas');
        
        // Obtener reserva con detalles (incluye datos del huésped)
        $result = $this->reservaModel->getWithDetails(1, 1, ['id' => $id]);
        if (empty($result['data'])) {
            return $this->view->error(404);
        }
        $reserva = $result['data'][0];

        if ($this->isPost()) {
            return $this->update($id);
        }

        $cabanias = $this->cabaniaModel->getActive();
        $estadosReserva = $this->estadoReservaModel->getActive();
        
        // Obtener estadísticas
        $estadisticas = [
            'total_consumos' => 0,
            'total_servicios' => 0,
            'monto_pagado' => 0
        ];
        
        $db = \App\Core\Database::getInstance();
        
        // Consultar consumos
        $sqlConsumos = "SELECT COUNT(*) as total FROM consumo WHERE rela_reserva = ?";
        $stmtConsumos = $db->prepare($sqlConsumos);
        $stmtConsumos->bind_param('i', $id);
        $stmtConsumos->execute();
        $resultConsumos = $stmtConsumos->get_result();
        if ($rowConsumos = $resultConsumos->fetch_assoc()) {
            $estadisticas['total_consumos'] = $rowConsumos['total'];
        }
        
        // Consultar servicios (consumos de tipo servicio)
        $sqlServicios = "SELECT COUNT(*) as total FROM consumo WHERE rela_reserva = ? AND rela_servicio IS NOT NULL";
        $stmtServicios = $db->prepare($sqlServicios);
        $stmtServicios->bind_param('i', $id);
        $stmtServicios->execute();
        $resultServicios = $stmtServicios->get_result();
        if ($rowServicios = $resultServicios->fetch_assoc()) {
            $estadisticas['total_servicios'] = $rowServicios['total'];
        }
        
        // Consultar pagos
        $sqlPagos = "SELECT COALESCE(SUM(p.pago_total), 0) as monto 
                    FROM pago p
                    INNER JOIN factura f ON p.rela_factura = f.id_factura
                    WHERE f.rela_reserva = ?";
        $stmtPagos = $db->prepare($sqlPagos);
        $stmtPagos->bind_param('i', $id);
        $stmtPagos->execute();
        $resultPagos = $stmtPagos->get_result();
        if ($rowPagos = $resultPagos->fetch_assoc()) {
            $estadisticas['monto_pagado'] = $rowPagos['monto'];
        }
        
        // Convertir formato de fechas para datetime-local (YYYY-MM-DDTHH:MM)
        if (isset($reserva['reserva_fhinicio']) && !empty($reserva['reserva_fhinicio'])) {
            $reserva['reserva_fhinicio'] = date('Y-m-d\TH:i', strtotime($reserva['reserva_fhinicio']));
        }
        if (isset($reserva['reserva_fhfin']) && !empty($reserva['reserva_fhfin'])) {
            $reserva['reserva_fhfin'] = date('Y-m-d\TH:i', strtotime($reserva['reserva_fhfin']));
        }
        
        $data = [
            'title' => 'Editar Reserva',
            'reserva' => $reserva,
            'cabanias' => $cabanias,
            'estados_reserva' => $estadosReserva,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/reservas/formulario', $data, 'main');
    }

    public function update($id)
    {
        $this->requirePermission('reservas');
        $reserva = $this->reservaModel->find($id);
        if (!$reserva) {
            return $this->view->error(404);
        }
        
        // Guardar estado anterior para detectar cambios
        $estadoAnterior = $reserva['rela_estadoreserva'];
        
        $data = [
            'reserva_fhinicio' => $this->post('reserva_fhinicio'),
            'reserva_fhfin' => $this->post('reserva_fhfin')
        ];
        
        // Si se envía un nuevo estado, agregarlo
        if ($this->post('rela_estadoreserva')) {
            $data['rela_estadoreserva'] = $this->post('rela_estadoreserva');
        }
        
        try {
            if ($this->reservaModel->update($id, $data)) {
                // Detectar cambio a estado "Pendiente de Pago" (id=4)
                if (isset($data['rela_estadoreserva']) && 
                    $data['rela_estadoreserva'] == 4 && 
                    $estadoAnterior != 4) {
                    
                    // Obtener usuario de la reserva y enviar notificación
                    $usuarioId = $this->reservaModel->getUsuarioIdFromReserva($id);
                    if ($usuarioId) {
                        $reservaCompleta = $this->reservaModel->find($id);
                        $montoPendiente = $reservaCompleta['reserva_montototal'] ?? 0;
                        
                        $this->notificationService->notifyPagoPendiente(
                            $reservaCompleta,
                            $montoPendiente,
                            $usuarioId
                        );
                        
                        error_log("Notificación de pago pendiente enviada por cambio de estado - Reserva: $id, Usuario: $usuarioId");
                    }
                }
                
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
        if (!$cabania || $cabania['rela_estadocabania'] != 1) {
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
            $this->redirect('/reservas', 'No hay datos de reserva disponibles', 'error');
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
        
        // Obtener datos de la persona con sus contactos (igual que en confirmar)
        $personaConContactos = $this->personaModel->getWithContacts($reservaTemporal['id_persona']);
        
        // Datos del huésped con contactos (igual que en confirmar)
        $huesped = [
            'id_persona' => $personaConContactos['id_persona'],
            'nombre' => $personaConContactos['persona_nombre'],
            'apellido' => $personaConContactos['persona_apellido'],
            'fecha_nacimiento' => $personaConContactos['persona_fechanac'],
            'email' => $personaConContactos['contacto_email'] ?? '',
            'telefono' => $personaConContactos['contacto_telefono'] ?? ''
        ];
        
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
            'huesped' => $huesped,
            'noches' => $dias,
            'isAdminArea' => false
        ];
        
        return $this->render('public/reservas/resumen', $data, 'main');
    }

    public function procederPago()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
            return;
        }
        
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
                    error_log('ERROR procederPago creando reserva temporal: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            }
        } else {
            // Usar datos básicos como fallback
            $reservaTemporal = $_SESSION['reserva_temporal_basica'];
            
            if (!isset($reservaTemporal['reserva_id'])) {
                try {
                    $reservaId = $this->crearReservaTemporal($reservaTemporal);
                    $_SESSION['reserva_temporal_basica']['reserva_id'] = $reservaId;
                    $reservaTemporal['reserva_id'] = $reservaId;
                } catch (\Exception $e) {
                    error_log('ERROR procederPago creando reserva temporal básica: ' . $e->getMessage());
                    $this->redirect('/catalogo', 'Error al procesar la reserva: ' . $e->getMessage(), 'error');
                    return;
                }
            }
        }
        
        try {
            // El flujo simplificado: la reserva ya fue creada con servicios
            // Solo necesitamos redirigir directamente a la pasarela
            
            // Verificar que la reserva tenga ID (debería tenerlo por la lógica anterior)
            if (!isset($reservaTemporal['reserva_id'])) {
                throw new \Exception('No se encontró el ID de la reserva después del proceso');
            }
            
            // Redirigir directamente a la pasarela de pago
            $this->redirect('/reservas/pasarela', '', 'info');

        } catch (\Exception $e) {
            error_log('ERROR final en procederPago: ' . $e->getMessage());
            $this->redirect('/reservas/resumen', 'Error al proceder al pago: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Retomar pago de una reserva pendiente existente
     */
    public function pagarReserva($id)
    {
        $this->requireAuth();
        
        try {
            $userId = \App\Core\Auth::id();
            
            // Verificar que la reserva existe
            $reserva = $this->reservaModel->find($id);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que la reserva pertenece al usuario
            if (!$this->reservaModel->isReservaOwner($id, $userId)) {
                $this->redirect('/reservas', 'No tiene permisos para acceder a esta reserva', 'error');
                return;
            }
            
            // Verificar que la reserva esté en estado pendiente (estado 1)
            if ($reserva['rela_estadoreserva'] != 1) {
                $this->redirect('/reservas', 'Esta reserva no está pendiente de pago', 'error');
                return;
            }
            
            // Obtener datos del usuario para obtener id_persona
            $userModel = new \App\Models\Usuario();
            $usuario = $userModel->findWithProfile($userId);
            
            if (!$usuario || !$usuario['rela_persona']) {
                $this->redirect('/reservas', 'Error: datos de usuario incompletos', 'error');
                return;
            }
            
            // Obtener datos de la cabaña
            $cabania = $this->cabaniaModel->find($reserva['rela_cabania']);
            if (!$cabania) {
                $this->redirect('/reservas', 'Cabaña no encontrada', 'error');
                return;
            }
            
            // Obtener consumos de la reserva
            $consumos = $this->reservaModel->getConsumptions($id);
            $totalConsumos = $this->reservaModel->getConsumptionsTotal($id);
            
            // Calcular totales (usar precio de cabaña * días)
            $fechaInicio = new \DateTime($reserva['reserva_fhinicio']);
            $fechaFin = new \DateTime($reserva['reserva_fhfin']);
            $dias = $fechaFin->diff($fechaInicio)->days;
            
            $subtotalAlojamiento = $cabania['cabania_precio'] * $dias;
            $totalGeneral = $subtotalAlojamiento + $totalConsumos;
            
            // Preparar datos para la sesión (formato compatible con el flujo existente)
            $_SESSION['reserva_temporal'] = [
                'reserva_id' => $id,
                'cabania_id' => $reserva['rela_cabania'],
                'cabania_nombre' => $cabania['cabania_nombre'],
                'cabania_precio' => $cabania['cabania_precio'],
                'fecha_ingreso' => $reserva['reserva_fhinicio'],
                'fecha_salida' => $reserva['reserva_fhfin'],
                'cantidad_personas' => 1,
                'id_persona' => $usuario['rela_persona'],
                'subtotal_alojamiento' => $subtotalAlojamiento,
                'servicios' => $consumos,
                'total_servicios' => $totalConsumos,
                'total_general' => $totalGeneral,
                'es_retomar_pago' => true // Flag para identificar que es retomar pago
            ];
            
            // Redirigir a la pasarela de pago
            $this->redirect('/reservas/pasarela', '', 'info');
            
        } catch (\Exception $e) {
            error_log('Error en pagarReserva: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error al procesar el pago: ' . $e->getMessage(), 'error');
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
     * Pasarela de pago con Checkout Pro de MercadoPago
     */
    public function pasarela()
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
        
        try {
            // Configurar SDK
            $config = require __DIR__ . '/../Core/config.php';
            $accessToken = $config['mercadopago']['access_token'];
            
            // Configurar MercadoPago con la API moderna
            MercadoPagoConfig::setAccessToken($accessToken);
            
            // Obtener información de la reserva
            $reserva = $this->reservaModel->find($reservaTemporal['reserva_id']);
            if (!$reserva) {
                throw new \Exception('Reserva no encontrada');
            }
            
            // Obtener cabaña desde la sesión temporal o desde la reserva en BD
            $cabaniaId = $reservaTemporal['cabania_id'] ?? $reserva['rela_cabania'];
            $cabania = $this->cabaniaModel->find($cabaniaId);
            if (!$cabania) {
                throw new \Exception('Cabaña no encontrada');
            }
            
            // Obtener datos de la persona - debe estar siempre en la sesión temporal
            if (!isset($reservaTemporal['id_persona'])) {
                throw new \Exception('Datos de la persona no encontrados en la sesión');
            }
            
            $personaId = $reservaTemporal['id_persona'];
            $persona = $this->personaModel->getWithContacts($personaId);
            
            if (!$persona) {
                throw new \Exception('Datos de la persona no encontrados');
            }
            
            // Calcular total
            $totalAmount = $reservaTemporal['total_general'] ?? $reservaTemporal['subtotal_alojamiento'];
            
            // Obtener email del contacto
            $email = '';
            if (isset($persona['contactos']) && is_array($persona['contactos'])) {
                foreach ($persona['contactos'] as $contacto) {
                    if ($contacto['rela_tipocontacto'] == 1) { // Email
                        $email = $contacto['contacto_descripcion'];
                        break;
                    }
                }
            }
            
            // Forzar email de TEST si estamos en modo TEST
            if (strpos($accessToken, 'TEST-') === 0 && !in_array($email, [
                'test_user_1316051943@testuser.com',
                'test_user_1853702@testuser.com'
            ])) {
                $email = 'test_user_1316051943@testuser.com';
                error_log("Modo TEST detectado - Usando email de prueba: $email");
            }
            
            // Construir URLs absolutas para callbacks
            // Usar base_url de ngrok para que MercadoPago pueda redirigir
            $baseUrl = rtrim($config['mercadopago']['base_url'], '/');
            $successUrl = $baseUrl . '/reservas/pago-exitoso';
            $failureUrl = $baseUrl . '/reservas/pago-fallido';
            $pendingUrl = $baseUrl . '/reservas/pago-pendiente';
            $webhookUrl = $baseUrl . '/reservas/webhook';
            
            error_log("MercadoPago URLs - Success: $successUrl, Failure: $failureUrl, Pending: $pendingUrl");
            
            // Crear request de preferencia
            $request = [
                'items' => [
                    [
                        'title' => 'Reserva Cabaña: ' . $cabania['cabania_nombre'],
                        'quantity' => 1,
                        'unit_price' => (float) $totalAmount,
                        'currency_id' => 'ARS'
                    ]
                ],
                'payer' => [
                    'name' => $persona['persona_nombre'],
                    'surname' => $persona['persona_apellido'],
                    'email' => $email
                ],
                'back_urls' => [
                    'success' => $successUrl,
                    'failure' => $failureUrl,
                    'pending' => $pendingUrl
                ],
                'auto_return' => 'approved',
                'external_reference' => strval($reserva['id_reserva']),
                'notification_url' => $webhookUrl,
                'statement_descriptor' => 'Casa de Palos'
            ];
            
            // Crear cliente de preferencias y crear la preferencia
            $client = new PreferenceClient();
            $preference = $client->create($request);
            
            if (!$preference->id) {
                throw new \Exception('Error al crear la preferencia de pago');
            }
            
            error_log("Preference creada - ID: {$preference->id}, Reserva: {$reserva['id_reserva']}");
            
            // Asegurar que la reserva tenga los datos necesarios para la vista
            // Priorizar datos de sesión temporal sobre datos de BD (que pueden estar incompletos)
            $reserva['reserva_ingreso'] = $reservaTemporal['fecha_ingreso'] ?? $reserva['reserva_ingreso'] ?? date('Y-m-d');
            $reserva['reserva_salida'] = $reservaTemporal['fecha_salida'] ?? $reserva['reserva_salida'] ?? date('Y-m-d', strtotime('+1 day'));
            $reserva['reserva_nro'] = $reserva['reserva_nro'] ?? ('R-' . str_pad($reserva['id_reserva'], 6, '0', STR_PAD_LEFT));
            
            // Renderizar vista con botón de MercadoPago
            $data = [
                'title' => 'Procesar Pago',
                'reserva' => $reserva,
                'cabania' => $cabania,
                'persona' => $persona,
                'total_amount' => $totalAmount,
                'preference_id' => $preference->id,
                'public_key' => $config['mercadopago']['public_key'],
                'isAdminArea' => false
            ];
            
            return $this->render('public/reservas/pasarela', $data, 'main');
            
        } catch (MPApiException $e) {
            error_log('Error MercadoPago API: Status ' . $e->getStatusCode() . ' - ' . json_encode($e->getApiResponse()));
            $errorMsg = 'Error al crear la preferencia de pago. Por favor, intente nuevamente.';
            $this->redirect('/reservas/resumen', $errorMsg, 'error');
            return;
        } catch (\Exception $e) {
            error_log('Error en pasarela: ' . $e->getMessage());
            $this->redirect('/reservas/resumen', 'Error al procesar el pago: ' . $e->getMessage(), 'error');
            return;
        }
    }

    /**
     * Callback de pago exitoso desde MercadoPago
     */
    public function pagoExitoso()
    {
        // NO requireAuth() - viene desde redirección de MercadoPago
        // El usuario ya tiene sesión activa del flujo anterior
        
        // Log de entrada
        error_log("=== INICIO pagoExitoso - Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . " ===");
        error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? 'none'));
        
        // Si viene desde ngrok, redirigir a localhost manteniendo parámetros
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'ngrok') !== false) {
            $localUrl = ($_ENV['APP_URL'] ?? 'http://localhost/proyecto_cabania') . '/reservas/pago-exitoso';
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            if ($queryString) {
                $localUrl .= '?' . $queryString;
            }
            error_log("Redirigiendo desde ngrok a localhost: $localUrl");
            header('Location: ' . $localUrl);
            exit;
        }
        
        error_log("Procesando pago en localhost...");
        
        try {
            // Obtener parámetros de MercadoPago
            $collectionId = $this->get('collection_id');
            $collectionStatus = $this->get('collection_status');
            $paymentId = $this->get('payment_id');
            $status = $this->get('status');
            $externalReference = $this->get('external_reference');
            $preferenceId = $this->get('preference_id');
            $merchantOrderId = $this->get('merchant_order_id');
            
            error_log("Pago exitoso - Params: collection_id=$collectionId, payment_id=$paymentId, status=$status, external_ref=$externalReference");
            
            // Verificar que tengamos el external_reference (reserva_id)
            if (!$externalReference) {
                error_log("ERROR: No se recibió external_reference");
                throw new \Exception('No se recibió la referencia de la reserva');
            }
            
            $reservaId = intval($externalReference);
            
            // Verificar que la reserva existe
            $reserva = $this->reservaModel->find($reservaId);
            if (!$reserva) {
                error_log("ERROR: Reserva $reservaId no encontrada");
                throw new \Exception('Reserva no encontrada');
            }
            
            // Preparar datos de pago con método MercadoPago (id_metododepago=5 según tu BD)
            $paymentData = [
                'metodo_pago_id' => 5 // ID de MercadoPago en tu tabla metododepago
            ];
            
            // Guardar información adicional del pago en sesión para referencia
            $_SESSION['pago_mercadopago'] = [
                'payment_id' => $paymentId,
                'collection_id' => $collectionId,
                'status' => $status,
                'merchant_order_id' => $merchantOrderId
            ];
            
            // Confirmar el pago en el sistema
            $resultado = $this->reservaModel->confirmPayment($reservaId, $paymentData);
            
            if (!$resultado['success']) {
                error_log("ERROR: confirmPayment falló - Mensaje: " . ($resultado['message'] ?? 'Sin mensaje'));
                throw new \Exception($resultado['message'] ?? 'Error procesando el pago');
            }
            
            error_log("Pago confirmado exitosamente para reserva $reservaId");
            
            // Obtener datos temporales para el total
            $reservaTemporal = $_SESSION['reserva_temporal'] ?? $_SESSION['reserva_temporal_basica'] ?? null;
            $totalGeneral = $resultado['total_pagado'] ?? ($reservaTemporal['total_general'] ?? $reservaTemporal['subtotal_alojamiento'] ?? 0);
            
            // Enviar email de confirmación
            try {
                $this->enviarNotificacionConfirmacion($reserva);
                error_log("Email de confirmación enviado para reserva $reservaId");
            } catch (\Exception $emailError) {
                error_log('WARNING: Error enviando email (pago ya procesado): ' . $emailError->getMessage());
            }
            
            // Notificar reserva cercana si el check-in es pronto
            try {
                $fechaInicio = new \DateTime($reserva['reserva_fhinicio']);
                $hoy = new \DateTime();
                $diasRestantes = $hoy->diff($fechaInicio)->days;
                
                if ($diasRestantes <= 7 && $diasRestantes >= 0) {
                    // Obtener usuario_id del huésped de la reserva
                    $usuarioId = $this->reservaModel->getUsuarioIdFromReserva($reservaId);
                    
                    if ($usuarioId) {
                        $this->notificationService->notifyReservaCercana($reserva, $diasRestantes, $usuarioId);
                    }
                }
            } catch (\Exception $notifError) {
                error_log('WARNING: Error enviando notificación de reserva cercana: ' . $notifError->getMessage());
            }
            
            // Guardar datos para página de éxito
            $_SESSION['reserva_exitosa'] = [
                'reserva_id' => $reservaId,
                'total_pagado' => $totalGeneral,
                'fecha_confirmacion' => $resultado['fecha_confirmacion'],
                'metodo_pago_id' => 5,
                'pago_id' => $resultado['pago_id'],
                'factura_id' => $resultado['factura_id'],
                'payment_id_mp' => $paymentId
            ];
            
            // Limpiar datos de sesión
            unset($_SESSION['reserva_temporal']);
            unset($_SESSION['reserva_temporal_basica']);
            unset($_SESSION['servicios_seleccionados']);
            unset($_SESSION['datos_pago']);
            unset($_SESSION['pago_datos']);
            unset($_SESSION['mercadopago_preference_id']);
            
            error_log("Redirigiendo a página de éxito: /reservas/exito");
            
            // Forzar que se guarde la sesión antes de redirigir
            session_write_close();
            
            // Limpiar cualquier salida previa
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Redirigir a página de éxito
            $exitoUrl = url('/reservas/exito');
            error_log("URL completa de redirección: $exitoUrl");
            
            // Usar header directo con exit inmediato
            header('Location: ' . $exitoUrl, true, 302);
            exit();
            
        } catch (\Exception $e) {
            error_log('========================');
            error_log('ERROR CRÍTICO en pagoExitoso');
            error_log('Mensaje: ' . $e->getMessage());
            error_log('Archivo: ' . $e->getFile() . ' línea ' . $e->getLine());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('========================');
            
            // Guardar error en sesión para mostrarlo
            $_SESSION['error_message'] = 'Error procesando el pago: ' . $e->getMessage();
            $_SESSION['error_details'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            
            // DEBUG: Mostrar página de debug en lugar de redirigir
            $appDebug = getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? 'false');
            if ($appDebug === 'true' || $appDebug === true) {
                // Limpiar buffer
                if (ob_get_level()) {
                    ob_end_clean();
                }
                include __DIR__ . '/../Views/public/debug_pago.php';
                exit;
            }
            
            // Producción: redirigir a resumen
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Location: ' . url('/reservas/resumen'));
            exit;
        }
    }
    
    /**
     * Callback de pago fallido desde MercadoPago
     */
    public function pagoFallido()
    {
        // NO requireAuth() - viene desde redirección de MercadoPago
        
        // Si viene desde ngrok, redirigir a localhost manteniendo parámetros
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'ngrok') !== false) {
            $localUrl = ($_ENV['APP_URL'] ?? 'http://localhost/proyecto_cabania') . '/reservas/pago-fallido';
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            if ($queryString) {
                $localUrl .= '?' . $queryString;
            }
            header('Location: ' . $localUrl);
            exit;
        }
        
        // Obtener parámetros de MercadoPago
        $collectionId = $this->get('collection_id');
        $collectionStatus = $this->get('collection_status');
        $paymentId = $this->get('payment_id');
        $status = $this->get('status');
        $externalReference = $this->get('external_reference');
        
        error_log("Pago fallido - Params: collection_id=$collectionId, payment_id=$paymentId, status=$status, external_ref=$externalReference");
        
        // Mensaje de error para el usuario
        $_SESSION['error_message'] = 'El pago fue rechazado. Por favor, verifique sus datos e intente nuevamente.';
        
        // Si hay external_reference, podríamos registrar el intento fallido
        if ($externalReference) {
            error_log("Intento de pago fallido para reserva ID: $externalReference");
        }
        
        // Redirigir al resumen para reintentar
        $this->redirect('/reservas/resumen');
    }
    
    /**
     * Callback de pago pendiente desde MercadoPago
     */
    public function pagoPendiente()
    {
        // NO requireAuth() - viene desde redirección de MercadoPago
        
        // Si viene desde ngrok, redirigir a localhost manteniendo parámetros
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'ngrok') !== false) {
            $localUrl = ($_ENV['APP_URL'] ?? 'http://localhost/proyecto_cabania') . '/reservas/pago-pendiente';
            $queryString = $_SERVER['QUERY_STRING'] ?? '';
            if ($queryString) {
                $localUrl .= '?' . $queryString;
            }
            header('Location: ' . $localUrl);
            exit;
        }
        
        // Obtener parámetros de MercadoPago
        $collectionId = $this->get('collection_id');
        $collectionStatus = $this->get('collection_status');
        $paymentId = $this->get('payment_id');
        $status = $this->get('status');
        $externalReference = $this->get('external_reference');
        
        error_log("Pago pendiente - Params: collection_id=$collectionId, payment_id=$paymentId, status=$status, external_ref=$externalReference");
        
        // Mensaje informativo para el usuario
        $_SESSION['info_message'] = 'Su pago está pendiente de confirmación. Le notificaremos cuando se complete.';
        
        // Si hay external_reference, podríamos actualizar el estado de la reserva
        if ($externalReference) {
            error_log("Pago pendiente para reserva ID: $externalReference");
            
            // Enviar notificación de pago pendiente
            try {
                $reserva = $this->reservaModel->find($externalReference);
                if ($reserva) {
                    $montoPendiente = $reserva['reserva_monto_total'] ?? 0;
                    
                    // Obtener usuario_id del huésped de la reserva
                    $usuarioId = $this->reservaModel->getUsuarioIdFromReserva($externalReference);
                    
                    if ($usuarioId) {
                        $this->notificationService->notifyPagoPendiente($reserva, $montoPendiente, $usuarioId);
                    }
                }
            } catch (\Exception $notifError) {
                error_log('WARNING: Error enviando notificación de pago pendiente: ' . $notifError->getMessage());
            }
            // Aquí podrías actualizar el estado de la reserva a "Pago Pendiente" si tu sistema lo soporta
        }
        
        // Redirigir a mis reservas o catálogo
        $this->redirect('/catalogo');
    }
    
    /**
     * Webhook de MercadoPago para notificaciones IPN
     */
    public function webhook()
    {
        // No requiere autenticación - viene de MercadoPago
        
        try {
            // Leer el body raw de la petición
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            
            // Log de la notificación recibida
            error_log("Webhook MercadoPago recibido: " . json_encode($data));
            
            // Verificar que sea una notificación de payment
            if (!isset($data['type']) || $data['type'] !== 'payment') {
                error_log("Webhook ignorado - tipo no es payment: " . ($data['type'] ?? 'null'));
                http_response_code(200);
                echo json_encode(['status' => 'ignored']);
                return;
            }
            
            // Obtener ID del pago
            $paymentId = $data['data']['id'] ?? null;
            if (!$paymentId) {
                error_log("Webhook error - no payment ID");
                http_response_code(400);
                echo json_encode(['error' => 'no payment id']);
                return;
            }
            
            // Configurar SDK y obtener detalles del pago
            $config = require __DIR__ . '/../Core/config.php';
            $accessToken = $config['mercadopago']['access_token'];
            MercadoPagoConfig::setAccessToken($accessToken);
            
            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get($paymentId);
            
            if (!$payment) {
                error_log("Webhook error - payment not found: $paymentId");
                http_response_code(404);
                echo json_encode(['error' => 'payment not found']);
                return;
            }
            
            error_log("Webhook - Payment status: " . $payment->status . ", External ref: " . $payment->external_reference);
            
            // Obtener reserva_id desde external_reference
            $reservaId = intval($payment->external_reference);
            if (!$reservaId) {
                error_log("Webhook error - no external reference");
                http_response_code(400);
                echo json_encode(['error' => 'no external reference']);
                return;
            }
            
            // Verificar que la reserva existe
            $reserva = $this->reservaModel->find($reservaId);
            if (!$reserva) {
                error_log("Webhook error - reserva not found: $reservaId");
                http_response_code(404);
                echo json_encode(['error' => 'reserva not found']);
                return;
            }
            
            // Procesar según el estado del pago
            switch ($payment->status) {
                case 'approved':
                    // Pago aprobado - confirmar si aún no está confirmado
                    if ($reserva['rela_estadoreserva'] == 1) { // Estado PENDIENTE
                        $paymentData = ['metodo_pago_id' => 5];
                        $resultado = $this->reservaModel->confirmPayment($reservaId, $paymentData);
                        
                        if ($resultado['success']) {
                            error_log("Webhook - Reserva $reservaId confirmada via webhook");
                            
                            // NO enviar email aquí - el email ya se envió en pagoExitoso()
                            // El webhook es solo para confirmaciones asíncronas en caso de que
                            // el usuario cierre el navegador antes de la redirección
                            error_log("Webhook - Email NO enviado (ya se envió en la redirección del usuario)");
                        }
                    } else {
                        error_log("Webhook - Reserva $reservaId ya fue confirmada previamente (estado: {$reserva['rela_estadoreserva']})");
                    }
                    break;
                    
                case 'rejected':
                case 'cancelled':
                    error_log("Webhook - Pago rechazado/cancelado para reserva $reservaId");
                    // Aquí podrías actualizar el estado de la reserva si lo deseas
                    break;
                    
                case 'pending':
                case 'in_process':
                    error_log("Webhook - Pago pendiente/en proceso para reserva $reservaId");
                    break;
            }
            
            // Responder OK a MercadoPago
            http_response_code(200);
            echo json_encode(['status' => 'processed']);
            
        } catch (\Exception $e) {
            error_log('Error en webhook: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * @deprecated Método eliminado - Los callbacks son manejados por pagoExitoso(), pagoFallido(), pagoPendiente()
     */
    public function procesarPasarela()
    {
        // Método deprecado - redirigir al catálogo
        $this->redirect('/catalogo', 'Flujo de pago actualizado', 'info');
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
                
                // Verificar si la reserva ya tiene factura
                $facturaModel = new \App\Models\Factura();
                $facturasExistentes = $facturaModel->getFacturasByReserva($reservaId);
                
                $facturaId = null;
                
                if (!empty($facturasExistentes)) {
                    // Usar la primera factura existente
                    $facturaId = $facturasExistentes[0]['id_factura'];
                    error_log("Pago manual: usando factura existente ID: $facturaId");
                } else {
                    // Crear nueva factura usando confirmPayment
                    error_log("Pago manual: no hay factura, usando confirmPayment para crear factura y pago");
                    $resultado = $this->reservaModel->confirmPayment($reservaId, [
                        'metodo_pago_id' => $metodoPagoId
                    ]);
                    
                    if ($resultado['success']) {
                        $this->redirect("/admin/operaciones/reservas/detalle/{$reservaId}", 
                                      'Pago registrado y factura generada correctamente.', 'exito');
                        return;
                    } else {
                        throw new \Exception('Error al procesar el pago: ' . $resultado['message']);
                    }
                }
                
                // Si llegamos aquí, ya existe factura - registrar solo el pago
                $pagoModel = new \App\Models\Pago();
                $pagoId = $pagoModel->createPago($reservaId, [
                    'total' => $montoPago,
                    'metodo_pago_id' => $metodoPagoId,
                    'factura_id' => $facturaId
                ]);
                
                if ($pagoId) {
                    $this->redirect("/admin/operaciones/reservas/detalle/{$reservaId}", 
                                  'Pago adicional registrado correctamente.', 'exito');
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
     * Descargar comprobante de factura en PDF
     */
    public function descargarComprobante($reservaId) {
        try {
            $this->requireAuth();
            
            // Verificar que el usuario tiene acceso a esta reserva
            $reserva = $this->reservaModel->find($reservaId);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que la reserva pertenece al usuario actual (excepto admin)
            $userId = $_SESSION['user']['id_usuario'] ?? null;
            $perfilId = $_SESSION['user']['id_perfil'] ?? null;
            
            // Admin puede ver cualquier comprobante
            if ($perfilId != 1) { // Si no es admin
                // Obtener el huésped de la reserva
                $huesped = $this->obtenerHuespedReserva($reservaId);
                $personaReserva = $huesped['id_persona'] ?? null;
                $personaUsuario = $_SESSION['user']['rela_persona'] ?? null;
                
                if ($personaReserva != $personaUsuario) {
                    $this->redirect('/reservas', 'No tienes permiso para ver este comprobante', 'error');
                    return;
                }
            }
            
            // Generar el PDF
            $pdfPath = $this->generarPDFFactura($reservaId);
            
            if (!$pdfPath || !file_exists($pdfPath)) {
                throw new \Exception('Error generando el comprobante PDF');
            }
            
            // Configurar headers para descarga
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="factura_reserva_' . $reservaId . '.pdf"');
            header('Content-Length: ' . filesize($pdfPath));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            // Enviar archivo
            readfile($pdfPath);
            
            // Limpiar archivo temporal
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
            
            exit;
            
        } catch (\Exception $e) {
            error_log('Error descargando comprobante: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error al generar el comprobante: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Generar PDF de factura para una reserva
     */
    private function generarPDFFactura($reservaId) {
        try {
            // Obtener datos completos de la reserva y factura
            $reservaCompleta = $this->obtenerDatosCompletosReserva($reservaId);
            if (!$reservaCompleta) {
                throw new \Exception("No se pudieron obtener los datos de la reserva");
            }

            // Obtener factura
            $facturaModel = new \App\Models\Factura();
            $facturas = $facturaModel->getFacturasByReserva($reservaId);
            if (empty($facturas)) {
                throw new \Exception("No se encontró la factura para esta reserva");
            }
            $factura = $facturas[0]; // Tomar la primera factura

            // Obtener detalles de la factura
            $detalles = $facturaModel->getDetallesFactura($factura['id_factura']);

            // Crear PDF
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            
            $pdf->SetCreator('Casa de Palos Cabañas');
            $pdf->SetAuthor('Casa de Palos');
            $pdf->SetTitle('Comprobante de Factura - ' . $factura['factura_nro']);
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->AddPage();

            // === HEADER DEL COMPROBANTE ===
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->SetTextColor(44, 85, 48); // Verde oscuro
            $pdf->Cell(0, 10, 'CASA DE PALOS CABAÑAS', 0, 1, 'C');
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->Cell(0, 5, 'Sistema de Reservas Online', 0, 1, 'C');
            $pdf->Ln(5);

            // === TIPO DE COMPROBANTE ===
            $pdf->SetFillColor(44, 85, 48);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 14);
            $tipoComprobante = $factura['tipocomprobante_descripcion'] ?? 'FACTURA B';
            $pdf->Cell(0, 10, $tipoComprobante, 0, 1, 'C', true);
            $pdf->Ln(3);

            // === NÚMERO DE COMPROBANTE Y FECHA ===
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(95, 7, 'Nº Comprobante: ' . $factura['factura_nro'], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(95, 7, 'Fecha: ' . date('d/m/Y H:i', strtotime($factura['factura_fechahora'])), 0, 1, 'R');
            $pdf->Ln(5);

            // === DATOS DEL CLIENTE ===
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, 'DATOS DEL CLIENTE', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'Nombre: ' . $reservaCompleta['huesped_nombre_completo'], 0, 1, 'L');
            $pdf->Cell(0, 6, 'Email: ' . $reservaCompleta['huesped_email'], 0, 1, 'L');
            $pdf->Ln(5);

            // === DATOS DE LA RESERVA ===
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, 'DETALLE DE LA RESERVA', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'Cabaña: ' . $reservaCompleta['cabania_nombre'], 0, 1, 'L');
            $pdf->Cell(95, 6, 'Check-in: ' . $reservaCompleta['fecha_llegada'], 0, 0, 'L');
            $pdf->Cell(95, 6, 'Check-out: ' . $reservaCompleta['fecha_salida'], 0, 1, 'L');
            $pdf->Cell(95, 6, 'Días de estadía: ' . $reservaCompleta['dias_estancia'], 0, 0, 'L');
            $pdf->Cell(95, 6, 'Huéspedes: ' . $reservaCompleta['total_huespedes'], 0, 1, 'L');
            $pdf->Ln(5);

            // === DETALLES DE LA FACTURA ===
            $pdf->SetFillColor(240, 240, 240);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, 'DETALLE DE LA FACTURA', 0, 1, 'L', true);
            
            // Tabla de detalles
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(80, 7, 'Descripción', 1, 0, 'L', true);
            $pdf->Cell(25, 7, 'Cantidad', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'P. Unitario', 1, 0, 'R', true);
            $pdf->Cell(35, 7, 'Total', 1, 1, 'R', true);

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetFillColor(255, 255, 255);
            
            if (!empty($detalles)) {
                foreach ($detalles as $detalle) {
                    $pdf->Cell(80, 6, substr($detalle['facturadetalle_descripcion'], 0, 40), 1, 0, 'L');
                    $pdf->Cell(25, 6, $detalle['facturadetalle_cantidad'], 1, 0, 'C');
                    $pdf->Cell(40, 6, '$' . number_format($detalle['facturadetalle_preciounitario'], 2), 1, 0, 'R');
                    $pdf->Cell(35, 6, '$' . number_format($detalle['facturadetalle_total'], 2), 1, 1, 'R');
                }
            }

            $pdf->Ln(3);

            // === TOTALES ===
            $pdf->SetFont('helvetica', 'B', 10);
            
            // Subtotal
            $pdf->Cell(145, 7, 'Subtotal:', 0, 0, 'R');
            $pdf->Cell(35, 7, '$' . number_format($factura['factura_subtotal'], 2), 1, 1, 'R');
            
            // IVA (si existe)
            if ($factura['factura_iva'] > 0) {
                $pdf->Cell(145, 7, 'IVA:', 0, 0, 'R');
                $pdf->Cell(35, 7, '$' . number_format($factura['factura_iva'], 2), 1, 1, 'R');
            }
            
            // Intereses (si existe)
            if ($factura['factura_intereses'] > 0) {
                $pdf->Cell(145, 7, 'Intereses:', 0, 0, 'R');
                $pdf->Cell(35, 7, '$' . number_format($factura['factura_intereses'], 2), 1, 1, 'R');
            }
            
            // Total
            $pdf->SetFillColor(44, 85, 48);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(145, 10, 'TOTAL:', 0, 0, 'R', true);
            $pdf->Cell(35, 10, '$' . number_format($factura['factura_total'], 2), 1, 1, 'R', true);

            $pdf->Ln(5);

            // === MÉTODO DE PAGO ===
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Método de pago: ' . $reservaCompleta['metodo_pago'], 0, 1, 'L');
            $pdf->Ln(5);

            // === PIE DEL COMPROBANTE ===
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'Este comprobante fue generado electrónicamente', 0, 1, 'C');
            $pdf->Cell(0, 5, 'Casa de Palos Cabañas - Sistema de Gestión Online', 0, 1, 'C');

            // Guardar PDF en archivo temporal
            $tempDir = sys_get_temp_dir();
            $filename = 'factura_' . $factura['factura_nro'] . '_' . time() . '.pdf';
            $filepath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            
            $pdf->Output($filepath, 'F');
            
            return $filepath;
            
        } catch (\Exception $e) {
            error_log('Error generando PDF de factura: ' . $e->getMessage());
            throw $e;
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
            
            // Generar PDF de la factura
            $pdfPath = null;
            try {
                $pdfPath = $this->generarPDFFactura($reserva['id_reserva']);
                error_log("PDF de factura generado exitosamente: $pdfPath");
            } catch (\Exception $e) {
                error_log("ERROR generando PDF de factura: " . $e->getMessage());
                // Continuar sin PDF si falla la generación
            }
            
            // Enviar email al huésped con PDF adjunto
            $result = $emailService->sendEmailWithAttachment(
                $reservaCompleta['huesped_email'],
                $reservaCompleta['huesped_nombre_completo'],
                $subject,
                $htmlBody,
                $textBody,
                $pdfPath
            );
            
            // Eliminar archivo temporal del PDF
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
                error_log("Archivo temporal PDF eliminado: $pdfPath");
            }
            
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
            
            // Obtener método de pago
            $metodoPago = $this->obtenerMetodoPagoReserva($reservaId);
            
            // Obtener total pagado
            $totalPagado = $this->obtenerTotalPagadoReserva($reservaId);
            
            // Calcular días de estadía
            $fechaInicio = new \DateTime($reserva['reserva_fhinicio']);
            $fechaFin = new \DateTime($reserva['reserva_fhfin']);
            $dias = $fechaInicio->diff($fechaFin)->days;
            
            // Contar huéspedes de la reserva
            // Para reservas online, usar datos de sesión si están disponibles
            $cantidadHuespedes = ['adultos' => 0, 'menores' => 0, 'total' => 0];
            
            // Si es reserva online y hay datos en sesión, usar esos datos
            if ($reserva['reserva_online'] == 1 && isset($_SESSION['reserva_temporal']['cantidad_personas'])) {
                $cantidadHuespedes['total'] = (int)$_SESSION['reserva_temporal']['cantidad_personas'];
                $cantidadHuespedes['adultos'] = $cantidadHuespedes['total']; // Asumimos todos adultos para reservas online
                $cantidadHuespedes['menores'] = 0;
            } else {
                // Para reservas manuales, contar desde huesped_reserva
                $cantidadHuespedes = $this->contarHuespedesReserva($reservaId);
            }
            
            $resultado = [
                'reserva_id' => $reserva['id_reserva'],
                'fecha_llegada' => $fechaInicio->format('d/m/Y'),
                'fecha_salida' => $fechaFin->format('d/m/Y'),
                'dias_estancia' => $dias,
                'cabania_nombre' => $cabania['cabania_nombre'] ?? 'No especificada',
                'cabania_codigo' => $cabania['cabania_codigo'] ?? '',
                'huesped_nombre_completo' => $huesped['nombre_completo'] ?? 'Usuario',
                'huesped_email' => $huesped['email'] ?? '',
                'metodo_pago' => $metodoPago['descripcion'] ?? 'MercadoPago',
                'monto_pagado' => $totalPagado ?? 0,
                'fecha_confirmacion' => date('d/m/Y H:i:s'),
                'adultos' => $cantidadHuespedes['adultos'],
                'menores' => $cantidadHuespedes['menores'],
                'total_huespedes' => $cantidadHuespedes['total'],
                'estado_reserva' => 'Confirmada'
            ];
            
            return $resultado;
            
        } catch (\Exception $e) {
            error_log('Error obteniendo datos completos de reserva: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Contar huéspedes de una reserva (adultos y menores)
     */
    private function contarHuespedesReserva($reservaId) {
        try {
            $database = \App\Core\Database::getInstance()->getConnection();
            
            // Contar huéspedes (sin distinción por edad ya que huesped_edad no existe)
            $sql = "SELECT 
                        COUNT(*) as total,
                        0 as adultos,
                        0 as menores
                    FROM huesped_reserva hr
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    WHERE hr.rela_reserva = ?";
            
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            return [
                'adultos' => intval($result['adultos'] ?? 0),
                'menores' => intval($result['menores'] ?? 0),
                'total' => intval($result['total'] ?? 0)
            ];
            
        } catch (\Exception $e) {
            error_log('Error contando huéspedes: ' . $e->getMessage());
            return ['adultos' => 0, 'menores' => 0, 'total' => 0];
        }
    }

    /**
     * Obtener información del huésped de una reserva
     */
    private function obtenerHuespedReserva($reservaId) {
        try {
            $database = \App\Core\Database::getInstance();
            
            // Usar la consulta con personafisica JOIN
            $sql = "SELECT CONCAT(pf.personafisica_nombre, ' ', pf.personafisica_apellido) as nombre_completo,
                           c.contacto_descripcion as email
                    FROM reserva r
                    INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                    LEFT JOIN contacto c ON p.id_persona = c.rela_persona 
                        AND c.rela_tipocontacto = 1 AND c.contacto_estado = 1
                    WHERE r.id_reserva = ?
                    LIMIT 1";

            // Para MySQLi necesitamos hacer bind y get_result
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
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
                    INNER JOIN factura f ON p.rela_factura = f.id_factura
                    INNER JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
                    WHERE f.rela_reserva = ?
                    ORDER BY p.id_pago DESC
                    LIMIT 1";

            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            return $result ?: ['descripcion' => 'MercadoPago'];
            
        } catch (\Exception $e) {
            error_log('Error obteniendo método de pago: ' . $e->getMessage());
            return ['descripcion' => 'MercadoPago'];
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
                    INNER JOIN factura f ON p.rela_factura = f.id_factura
                    WHERE f.rela_reserva = ?";

            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $reservaId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            
            $total = $row['total'] ?? 0;
            
            return $total;
            
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
        // NO requireAuth() - el usuario ya viene autenticado del flujo de pago
        // La sesión se mantiene del proceso anterior
        
        // Verificar que existan datos de reserva exitosa (por sesión o por parámetro)
        $reservaId = $this->get('id');
        
        if (!isset($_SESSION['reserva_exitosa']) && !$reservaId) {
            error_log("ERROR: No se encontró reserva_exitosa en sesión ni ID en URL");
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
                $datosCompletos = $this->obtenerDatosCompletosReserva($reservaExitosa['reserva_id']);
                
                if ($datosCompletos && !empty($datosCompletos['huesped_email'])) {
                    $reserva['huesped_email'] = $datosCompletos['huesped_email'];
                    $reserva['huesped_nombre_completo'] = $datosCompletos['huesped_nombre_completo'];
                } else {
                    // Fallback: usar método del modelo si existe
                    $reflection = new \ReflectionClass($this->reservaModel);
                    $method = $reflection->getMethod('getReservaCompleteData');
                    $method->setAccessible(true);
                    
                    $reservaCompleta = $method->invoke($this->reservaModel, $reservaExitosa['reserva_id']);
                    
                    if ($reservaCompleta && isset($reservaCompleta['email_persona'])) {
                        $reserva['huesped_email'] = $reservaCompleta['email_persona'];
                    }
                }
                
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
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $persona = $result->fetch_assoc();
            $stmt->close();
            
            if (!$persona) {
                $this->redirect('/', 'No se encontró información de perfil', 'error');
                return;
            }
            
            // Obtener reservas del usuario con TODOS los detalles necesarios
            $sqlReservas = "SELECT r.id_reserva,
                                   r.reserva_fechahora as fecha_confirmacion,
                                   r.reserva_fhinicio,
                                   r.reserva_fhfin,
                                   r.rela_estadoreserva,
                                   r.reserva_online,
                                   c.cabania_nombre, 
                                   c.cabania_codigo,
                                   er.estadoreserva_descripcion,
                                   MAX(pf.personafisica_nombre) as persona_nombre,
                                   MAX(pf.personafisica_apellido) as persona_apellido,
                                   COALESCE(f.factura_total, 0) as factura_original,
                                   (SELECT COALESCE(SUM(consumo_total), 0) 
                                    FROM consumo 
                                    WHERE rela_reserva = r.id_reserva 
                                    AND rela_estadoconsumo IN (1,2,3)) as total_consumos,
                                   (SELECT COALESCE(SUM(revision_costo), 0) 
                                    FROM revision 
                                    WHERE rela_reserva = r.id_reserva) as total_danios,
                                   (SELECT COALESCE(SUM(p.pago_total), 0) 
                                    FROM pago p
                                    INNER JOIN factura f2 ON p.rela_factura = f2.id_factura
                                    WHERE f2.rela_reserva = r.id_reserva) as total_abonado,
                                   COALESCE(f.factura_total, 0) + 
                                   (SELECT COALESCE(SUM(consumo_total), 0) 
                                    FROM consumo 
                                    WHERE rela_reserva = r.id_reserva 
                                    AND rela_estadoconsumo IN (1,2,3)) +
                                   (SELECT COALESCE(SUM(revision_costo), 0) 
                                    FROM revision 
                                    WHERE rela_reserva = r.id_reserva) as importe_total,
                                   COUNT(DISTINCT hr.rela_huesped) as total_huespedes
                            FROM reserva r
                            INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                            INNER JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                            INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                            INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                            INNER JOIN persona p ON h.rela_persona = p.id_persona
                            LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                            LEFT JOIN factura f ON r.id_reserva = f.rela_reserva
                            WHERE p.id_persona = ?
                            GROUP BY r.id_reserva, r.reserva_fechahora, r.reserva_fhinicio, r.reserva_fhfin, 
                                     r.rela_estadoreserva, r.reserva_online, c.cabania_nombre, c.cabania_codigo, 
                                     er.estadoreserva_descripcion, f.factura_total
                            ORDER BY r.reserva_fhinicio DESC";
            
            $stmt = $database->prepare($sqlReservas);
            $stmt->bind_param("i", $persona['id_persona']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservas = [];
            while ($row = $result->fetch_assoc()) {
                // Calcular saldo pendiente
                $row['saldo_pendiente'] = $row['importe_total'] - $row['total_abonado'];
                $reservas[] = $row;
            }
            $stmt->close();
            
            $data = [
                'title' => 'Mis Reservas',
                'reservas' => $reservas,
                'isAdminArea' => false
            ];
            
            return $this->render('public/reservas/mis-reservas', $data, 'main');
            
        } catch (\Exception $e) {
            error_log('Error obteniendo reservas del usuario: ' . $e->getMessage());
            $this->redirect('/', 'Error interno del servidor', 'error');
        }
    }
    
    /**
     * Marcar ingreso de una reserva (cambiar estado a "En curso")
     */
    public function marcarIngreso($id)
    {
        $this->requireAuth();
        
        try {
            // Verificar que la reserva existe y pertenece al usuario
            $reserva = $this->reservaModel->find($id);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            $usuarioId = $_SESSION['usuario_id'];
            if (!$this->reservaModel->isReservaOwner($id, $usuarioId)) {
                $this->redirect('/reservas', 'No tiene permisos para modificar esta reserva', 'error');
                return;
            }
            
            // Verificar que la reserva está en estado "Confirmada" (2)
            if ($reserva['rela_estadoreserva'] != 2) {
                $this->redirect('/reservas', 'Solo se puede marcar ingreso en reservas confirmadas', 'error');
                return;
            }
            
            // Cambiar estado de reserva a "En curso" (3) y cabaña a "Ocupada" (0)
            $resultado = $this->reservaModel->update($id, [
                'rela_estadoreserva' => 3 // EN CURSO
            ]);
            
            if ($resultado) {
                // Nota: El estado de cabaña se gestiona automáticamente por las reservas activas
                // No existe cabania_estado en la tabla cabania
                
                $this->redirect('/reservas', 'Ingreso registrado correctamente.', 'exito');
            } else {
                $this->redirect('/reservas', 'Error al registrar el ingreso', 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Error en marcarIngreso: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Marcar salida de una reserva (cambiar estado a "Finalizada")
     */
    public function marcarSalida($id)
    {
        $this->requireAuth();
        
        try {
            // Verificar que la reserva existe y pertenece al usuario
            $reserva = $this->reservaModel->find($id);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            $usuarioId = $_SESSION['usuario_id'];
            if (!$this->reservaModel->isReservaOwner($id, $usuarioId)) {
                $this->redirect('/reservas', 'No tiene permisos para modificar esta reserva', 'error');
                return;
            }
            
            // Verificar que la reserva está en estado "En curso" (3)
            if ($reserva['rela_estadoreserva'] != 3) {
                $this->redirect('/reservas', 'Solo se puede marcar salida en reservas en curso', 'error');
                return;
            }
            
            // Cambiar estado a "Pendiente de Revisión" (8)
            $resultado = $this->reservaModel->update($id, [
                'rela_estadoreserva' => 8 // PENDIENTE DE REVISIÓN
            ]);
            
            if ($resultado) {
                $this->redirect('/reservas', 'Salida registrada correctamente. La reserva está pendiente de revisión.', 'exito');
            } else {
                $this->redirect('/reservas', 'Error al registrar la salida', 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Error en marcarSalida: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Ver datos de huéspedes de una reserva
     */
    public function verHuespedes($id)
    {
        $this->requireAuth();
        
        try {
            // Verificar que la reserva existe y pertenece al usuario
            $reserva = $this->reservaModel->find($id);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            $usuarioId = $_SESSION['usuario_id'];
            if (!$this->reservaModel->isReservaOwner($id, $usuarioId)) {
                $this->redirect('/reservas', 'No tiene permisos para ver esta información', 'error');
                return;
            }
            
            // Obtener huéspedes de la reserva
            $database = \App\Core\Database::getInstance();
            $sqlHuespedes = "SELECT p.*, h.*, 
                                    GROUP_CONCAT(DISTINCT CONCAT(tc.tipocontacto_descripcion, ': ', c.contacto_descripcion) SEPARATOR '<br>') as contactos
                             FROM huesped_reserva hr
                             INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                             INNER JOIN persona p ON h.rela_persona = p.id_persona
                             LEFT JOIN contacto c ON p.id_persona = c.rela_persona AND c.contacto_estado = 1
                             LEFT JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto
                             WHERE hr.rela_reserva = ?
                             GROUP BY p.id_persona";
            
            $stmt = $database->prepare($sqlHuespedes);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $huespedes = [];
            while ($row = $result->fetch_assoc()) {
                $huespedes[] = $row;
            }
            $stmt->close();
            
            $data = [
                'title' => 'Huéspedes de la Reserva',
                'reserva' => $reserva,
                'huespedes' => $huespedes,
                'isAdminArea' => false
            ];
            
            return $this->render('public/reservas/huespedes', $data, 'main');
            
        } catch (\Exception $e) {
            error_log('Error en verHuespedes: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Ver y registrar consumos de una reserva
     */
    public function gestionarConsumos($id)
    {
        $this->requireAuth();
        
        try {
            // Verificar que la reserva existe y pertenece al usuario
            $reserva = $this->reservaModel->find($id);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            $usuarioId = $_SESSION['usuario_id'];
            if (!$this->reservaModel->isReservaOwner($id, $usuarioId)) {
                $this->redirect('/reservas', 'No tiene permisos para ver esta información', 'error');
                return;
            }
            
            // Obtener consumos de la reserva
            $consumos = $this->reservaModel->getConsumptions($id);
            
            // Obtener productos y servicios disponibles para agregar nuevos consumos
            $productos = $this->servicioModel->getServiciosParaReservas();
            
            $data = [
                'title' => 'Consumos de la Reserva',
                'reserva' => $reserva,
                'consumos' => $consumos,
                'productos_disponibles' => $productos,
                'isAdminArea' => false
            ];
            
            return $this->render('public/reservas/consumos', $data, 'main');
            
        } catch (\Exception $e) {
            error_log('Error en gestionarConsumos: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Registrar un nuevo consumo (solo si no está confirmado ni abonado)
     */
    public function registrarConsumo($reservaId)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('/reservas/' . $reservaId . '/consumos', 'Método no permitido', 'error');
            return;
        }
        
        try {
            // Verificar que la reserva existe y pertenece al usuario
            $reserva = $this->reservaModel->find($reservaId);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            $usuarioId = $_SESSION['usuario_id'];
            if (!$this->reservaModel->isReservaOwner($reservaId, $usuarioId)) {
                $this->redirect('/reservas', 'No tiene permisos para modificar esta reserva', 'error');
                return;
            }
            
            // Obtener datos del formulario
            $servicioId = $this->post('servicio_id');
            $cantidad = $this->post('cantidad', 1);
            
            if (!$servicioId || $cantidad < 1) {
                $this->redirect('/reservas/' . $reservaId . '/consumos', 'Datos inválidos', 'error');
                return;
            }
            
            // Obtener información del servicio
            $servicio = $this->servicioModel->find($servicioId);
            if (!$servicio) {
                $this->redirect('/reservas/' . $reservaId . '/consumos', 'Servicio no encontrado', 'error');
                return;
            }
            
            // Crear el consumo
            $consumoData = [
                'rela_reserva' => $reservaId,
                'rela_servicio' => $servicioId,
                'consumo_descripcion' => 'Servicio: ' . $servicio['servicio_nombre'],
                'consumo_cantidad' => $cantidad,
                'consumo_total' => $servicio['servicio_precio'] * $cantidad,
                'rela_estadoconsumo' => 1 // PENDIENTE (Solicitud pendiente)
            ];
            
            $consumoId = $this->consumoModel->create($consumoData);
            
            if ($consumoId) {
                $this->redirect('/reservas/' . $reservaId . '/consumos', 'Consumo registrado correctamente', 'exito');
            } else {
                $this->redirect('/reservas/' . $reservaId . '/consumos', 'Error al registrar el consumo', 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Error en registrarConsumo: ' . $e->getMessage());
            $this->redirect('/reservas/' . $reservaId . '/consumos', 'Error: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Ver y gestionar comentarios de una reserva
     */
    public function gestionarComentarios($id)
    {
        $this->requireAuth();
        
        try {
            // Verificar que la reserva existe y pertenece al usuario
            $reserva = $this->reservaModel->find($id);
            if (!$reserva) {
                $this->redirect('/reservas', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            $usuarioId = $_SESSION['usuario_id'];
            if (!$this->reservaModel->isReservaOwner($id, $usuarioId)) {
                $this->redirect('/reservas', 'No tiene permisos para ver esta información', 'error');
                return;
            }
            
            // Obtener comentarios de la reserva
            $comentarioModel = new \App\Models\Comentario();
            $comentarios = $comentarioModel->getComentariosByReserva($id);
            
            // Verificar si ya existe un comentario para esta reserva
            $yaComentado = !empty($comentarios);
            
            $data = [
                'title' => 'Comentarios de la Reserva',
                'reserva' => $reserva,
                'comentarios' => $comentarios,
                'ya_comentado' => $yaComentado,
                'isAdminArea' => false
            ];
            
            return $this->render('public/reservas/comentarios', $data, 'main');
            
        } catch (\Exception $e) {
            error_log('Error en gestionarComentarios: ' . $e->getMessage());
            $this->redirect('/reservas', 'Error: ' . $e->getMessage(), 'error');
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
            if ($result === false || !is_array($result)) {
                return true; // Si no hay resultados, no hay conflictos
            }
            
            return isset($result['conflictos']) && $result['conflictos'] == 0;
            
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

            // 2. Obtener servicios seleccionados de la sesión si existen
            $servicios = [];
            if (isset($datosReserva['servicios']) && !empty($datosReserva['servicios'])) {
                $servicios = $datosReserva['servicios'];
            }
            
            // 3. Crear reserva con servicios en una sola transacción atómica
            // TRANSACCIÓN CRÍTICA: Reserva + Servicios como consumos en una sola operación
            $reservaId = $this->reservaModel->createReservationWithServices($reservaData, $servicios);
            
            if (!$reservaId) {
                throw new \Exception("Error al crear la reserva con servicios - ID nulo");
            }
            
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
        $totalCabanias = $db->query("SELECT COUNT(*) as total FROM cabania WHERE rela_estadocabania IN (1, 2)")->fetch_assoc()['total'];
        $ocupadas = $db->query("SELECT COUNT(*) as ocupadas FROM cabania WHERE rela_estadocabania = 2")->fetch_assoc()['ocupadas'];
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

    /**
     * Verificar y notificar pagos pendientes al usuario
     */
    private function checkAndNotifyPagosPendientes($usuarioId)
    {
        try {
            $reservasPendientes = $this->reservaModel->getReservasPagoPendienteUsuario($usuarioId);
            
            if (!empty($reservasPendientes)) {
                foreach ($reservasPendientes as $reserva) {
                    $this->notificationService->notifyPagoPendiente(
                        $reserva,
                        $reserva['monto_pendiente'],
                        $usuarioId
                    );
                    error_log("Notificación de pago pendiente enviada - Usuario: $usuarioId, Reserva: {$reserva['id_reserva']}, Monto: {$reserva['monto_pendiente']}");
                }
            }
        } catch (\Exception $e) {
            error_log("Error verificando pagos pendientes: " . $e->getMessage());
        }
    }

    /**
     * Exportar a Excel (.xlsx)
     */
    public function exportar()
    {
        $this->requirePermission('reservas');

        $filters = [
            'estado' => $this->get('estado'),
            'cabania' => $this->get('cabania'),
            'fecha_inicio' => $this->get('fecha_inicio'),
            'fecha_fin' => $this->get('fecha_fin'),
            'persona' => $this->get('persona')
        ];

        $result = $this->reservaModel->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/reservas', 'No hay datos para exportar', 'error');
            return;
        }

        require_once 'vendor/autoload.php';

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $headers = ['N° Reserva', 'Fecha Hora', 'Cabaña', 'Periodo', 'Inicio', 'Fin', 'Estado', 'Online', 'Persona'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getStyle($col . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $col++;
            }

            // Datos
            $row = 2;
            foreach ($datos as $reserva) {
                $sheet->setCellValue('A' . $row, $reserva['reserva_nro']);
                $sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($reserva['reserva_fechahora'])));
                $sheet->setCellValue('C' . $row, $reserva['cabania_nombre'] ?? 'Sin cabaña');
                $sheet->setCellValue('D' . $row, $reserva['periodo_descripcion'] ?? 'Sin periodo');
                $sheet->setCellValue('E' . $row, date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])));
                $sheet->setCellValue('F' . $row, date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])));
                $sheet->setCellValue('G' . $row, $reserva['estadoreserva_descripcion'] ?? 'Sin estado');
                $sheet->setCellValue('H' . $row, $reserva['reserva_online'] == 1 ? 'Sí' : 'No');
                $sheet->setCellValue('I' . $row, ($reserva['persona_nombre'] ?? '') . ' ' . ($reserva['persona_apellido'] ?? ''));
                $row++;
            }

            // Ajustar columnas
            foreach (range('A', 'I') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Descargar archivo
            $filename = 'reservas_' . date('Ymd_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error exportando a Excel: " . $e->getMessage());
            $this->redirect('/reservas', 'Error al exportar a Excel', 'error');
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('reservas');

        $filters = [
            'estado' => $this->get('estado'),
            'cabania' => $this->get('cabania'),
            'fecha_inicio' => $this->get('fecha_inicio'),
            'fecha_fin' => $this->get('fecha_fin'),
            'persona' => $this->get('persona')
        ];

        $result = $this->reservaModel->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/reservas', 'No hay datos para exportar', 'error');
            return;
        }

        require_once 'vendor/autoload.php';

        try {
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            
            $pdf->SetCreator('Sistema de Gestión de Cabañas');
            $pdf->SetAuthor('Sistema de Gestión');
            $pdf->SetTitle('Listado de Reservas');
            $pdf->SetSubject('Reservas');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Listado de Reservas', 0, 1, 'C');
            $pdf->Ln(3);

            // Info de exportación
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, 'Fecha de exportación: ' . date('d/m/Y H:i'), 0, 1, 'R');
            $pdf->Cell(0, 5, 'Total de registros: ' . $result['total'], 0, 1, 'R');
            $pdf->Ln(5);

            // Tabla
            $pdf->SetFont('helvetica', 'B', 8);
            $html = '<table border="1" cellpadding="4" cellspacing="0">
                <thead>
                    <tr style="background-color:#E0E0E0;">
                        <th width="10%"><b>N° Reserva</b></th>
                        <th width="15%"><b>Fecha</b></th>
                        <th width="15%"><b>Cabaña</b></th>
                        <th width="15%"><b>Inicio</b></th>
                        <th width="15%"><b>Fin</b></th>
                        <th width="15%"><b>Estado</b></th>
                        <th width="15%"><b>Persona</b></th>
                    </tr>
                </thead>
                <tbody>';

            $pdf->SetFont('helvetica', '', 7);
            foreach ($datos as $reserva) {
                $html .= '<tr>
                    <td width="10%">' . htmlspecialchars($reserva['reserva_nro']) . '</td>
                    <td width="15%">' . date('d/m/Y', strtotime($reserva['reserva_fechahora'])) . '</td>
                    <td width="15%">' . htmlspecialchars($reserva['cabania_nombre'] ?? 'Sin cabaña') . '</td>
                    <td width="15%">' . date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) . '</td>
                    <td width="15%">' . date('d/m/Y', strtotime($reserva['reserva_fhfin'])) . '</td>
                    <td width="15%">' . htmlspecialchars($reserva['estadoreserva_descripcion'] ?? 'Sin estado') . '</td>
                    <td width="15%">' . htmlspecialchars(($reserva['persona_nombre'] ?? '') . ' ' . ($reserva['persona_apellido'] ?? '')) . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');

            // Descargar
            $filename = 'reservas_' . date('Ymd_His') . '.pdf';
            $pdf->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error exportando a PDF: " . $e->getMessage());
            $this->redirect('/reservas', 'Error al exportar a PDF', 'error');
        }
    }

    /**
     * Eliminar (baja lógica) - Cambiar estado a Cancelada
     */
    public function delete($id)
    {
        $this->requirePermission('reservas');

        $reserva = $this->reservaModel->find($id);
        if (!$reserva) {
            $this->redirect('/reservas', 'Reserva no encontrada', 'error');
            return;
        }

        if ($this->isPost()) {
            try {
                // Cambiar estado a Anulada (ID 6)
                $this->reservaModel->update($id, ['rela_estadoreserva' => 6]);
                $this->redirect('/reservas', 'Reserva anulada exitosamente', 'success');
            } catch (\Exception $e) {
                $this->redirect('/reservas', 'Error al anular reserva: ' . $e->getMessage(), 'error');
            }
            return;
        }

        // Vista de confirmación (opcional)
        $this->redirect('/reservas/' . $id, 'Use el botón de cancelar para proceder', 'info');
    }

    /**
     * Restaurar reserva cancelada
     */
    public function restore($id)
    {
        $this->requirePermission('reservas');

        $reserva = $this->reservaModel->find($id);
        if (!$reserva) {
            $this->redirect('/reservas', 'Reserva no encontrada', 'error');
            return;
        }

        if ($this->isPost()) {
            try {
                // Cambiar estado a Confirmada (asumiendo ID 2 es estado confirmada)
                $this->reservaModel->update($id, ['rela_estadoreserva' => 2]);
                $this->redirect('/reservas', 'Reserva restaurada exitosamente', 'success');
            } catch (\Exception $e) {
                $this->redirect('/reservas', 'Error al restaurar reserva: ' . $e->getMessage(), 'error');
            }
            return;
        }

        $this->redirect('/reservas/' . $id, 'Reserva restaurada', 'success');
    }

    /**
     * Cambiar estado mediante AJAX
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('reservas');
        header('Content-Type: application/json');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $reserva = $this->reservaModel->find($id);
        if (!$reserva) {
            echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
            return;
        }

        $nuevoEstadoId = (int) $this->post('estado');
        
        try {
            $this->reservaModel->update($id, ['rela_estadoreserva' => $nuevoEstadoId]);
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()]);
        }
    }

}

