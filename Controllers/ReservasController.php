<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reserva;
use App\Models\Cabania;
use App\Models\Persona;
use App\Models\Servicio;
use App\Models\Consumo;

class ReservasController extends Controller
{
    protected $reservaModel;
    protected $cabaniaModel;
    protected $personaModel;
    protected $servicioModel;
    protected $consumoModel;

    public function __construct()
    {
        parent::__construct();
        $this->reservaModel = new Reserva();
        $this->cabaniaModel = new Cabania();
        $this->personaModel = new Persona();
        $this->servicioModel = new Servicio();
        $this->consumoModel = new Consumo();
    }

    public function index()
    {
        $this->requirePermission('reservas');
        $page = (int) $this->get('page', 1);
        $filters = [
            'estado' => $this->get('estado'),
            'cabania' => $this->get('cabania'),
            'fecha_inicio' => $this->get('fecha_inicio'),
            'fecha_fin' => $this->get('fecha_fin'),
            'persona' => $this->get('persona')
        ];
        $result = $this->reservaModel->getWithDetails($page, 10, $filters);
        $cabanias = $this->cabaniaModel->getActive();
        $data = [
            'title' => 'Gestión de Reservas',
            'reservas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'cabanias' => $cabanias,
            'isAdminArea' => true
        ];
        return $this->render('admin/operaciones/reservas/listado', $data);
    }

    public function create()
    {
        $this->requirePermission('reservas');
        if ($this->isPost()) {
            return $this->store();
        }
        $cabanias = $this->cabaniaModel->getActive();
        $data = [
            'title' => 'Nueva Reserva',
            'cabanias' => $cabanias
        ];
        return $this->render('admin/operaciones/reservas/formulario', $data);
    }

    public function store()
    {
        $this->requirePermission('reservas');
        $data = [
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
        $this->requireAuth();
        if ($this->isPost()) {
            return $this->storeOnline();
        }
        $cabanias = $this->cabaniaModel->getActive();
        $data = [
            'title' => 'Reserva Online',
            'cabanias' => $cabanias,
            'readonly' => false
        ];
        return $this->render('admin/operaciones/reservas/reserva_online', $data);
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
            
            // Completar datos de la reserva
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
            $_SESSION['reserva_temporal'] = [
                'cabania_id' => $reservaData['cabania_id'],
                'fecha_ingreso' => $reservaData['fecha_ingreso'],
                'fecha_salida' => $reservaData['fecha_salida'],
                'cantidad_personas' => $reservaData['cantidad_personas'],
                'id_persona' => $reservaData['id_persona'],
                'subtotal_alojamiento' => $subtotalAlojamiento,
                'servicios' => $serviciosDetalle,
                'total_servicios' => $totalServicios,
                'total_general' => $totalGeneral
            ];

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
        if (!isset($_SESSION['reserva_temporal'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        $reservaTemporal = $_SESSION['reserva_temporal'];
        
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
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
            return;
        }
        
        // Verificar que existan datos temporales de la reserva
        if (!isset($_SESSION['reserva_temporal'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        $reservaTemporal = $_SESSION['reserva_temporal'];
        
        try {
            // PRIMERA TRANSACCIÓN: Crear reserva en estado pendiente y consumos
            
            // 1. Crear la reserva en estado PENDIENTE
            $reservaDataDB = [
                'rela_cabania' => $reservaTemporal['cabania_id'],
                'rela_persona' => $reservaTemporal['id_persona'],
                'reserva_fechainicio' => $reservaTemporal['fecha_ingreso'],
                'reserva_fechafin' => $reservaTemporal['fecha_salida'],
                'reserva_cantidadpersonas' => $reservaTemporal['cantidad_personas'],
                'reserva_observaciones' => '',
                'rela_metodopago' => 1, // Por defecto efectivo
                'rela_estadoreserva' => 1 // Estado PENDIENTE
            ];

            $reservaId = $this->reservaModel->createReservation($reservaDataDB);
            
            if (!$reservaId) {
                throw new \Exception('Error al crear la reserva');
            }
            
            // 2. Crear consumos para servicios seleccionados
            if (!empty($reservaTemporal['servicios'])) {
                foreach ($reservaTemporal['servicios'] as $servicio) {
                    $consumoData = [
                        'rela_reserva' => $reservaId,
                        'rela_servicio' => $servicio['id'],
                        'consumo_fecha' => $reservaTemporal['fecha_ingreso'],
                        'consumo_cantidad' => 1,
                        'consumo_precio' => $servicio['precio'],
                        'consumo_observaciones' => 'Servicio seleccionado durante la reserva',
                        'consumo_estado' => 1
                    ];
                    
                    $consumoId = $this->consumoModel->create($consumoData);
                    if (!$consumoId) {
                        error_log("Error al crear consumo para servicio: " . $servicio['nombre']);
                    }
                }
            }
            
            // 3. Guardar ID de reserva en sesión para el pago
            $_SESSION['reserva_temporal']['reserva_id'] = $reservaId;
            
            // 4. Redirigir a la vista de pago
            $this->redirect('/reservas/pago', '', 'info');

        } catch (\Exception $e) {
            $this->redirect('/reservas/resumen', 'Error al procesar: ' . $e->getMessage(), 'error');
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
        if (!isset($_SESSION['reserva_temporal'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        $reservaTemporal = $_SESSION['reserva_temporal'];
        
        // Obtener información de la cabaña
        $cabania = $this->cabaniaModel->find($reservaTemporal['cabania_id']);
        
        if (!$cabania) {
            $this->redirect('/catalogo', 'Error: cabaña no encontrada', 'error');
            return;
        }
        
        // Obtener métodos de pago disponibles
        $metodoPagoModel = new \App\Models\MetodoPago();
        $metodosPago = $metodoPagoModel->findAll();
        
        // Si no hay métodos de pago en la BD, usar lista básica
        if (empty($metodosPago)) {
            $metodosPago = [
                ['id' => 1, 'metodopago_nombre' => 'Efectivo', 'metodopago_descripcion' => 'Pago en efectivo al momento del check-in'],
                ['id' => 2, 'metodopago_nombre' => 'Tarjeta de Crédito', 'metodopago_descripcion' => 'Visa, Mastercard, American Express'],
                ['id' => 3, 'metodopago_nombre' => 'Transferencia Bancaria', 'metodopago_descripcion' => 'Transferencia directa a cuenta bancaria']
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

    public function confirmarPago()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->redirect('/catalogo', 'Acceso no válido', 'error');
            return;
        }
        
        // Verificar que existan datos temporales de la reserva
        if (!isset($_SESSION['reserva_temporal']) || !isset($_SESSION['reserva_temporal']['reserva_id'])) {
            $this->redirect('/catalogo', 'No hay datos de reserva disponibles', 'error');
            return;
        }
        
        $reservaTemporal = $_SESSION['reserva_temporal'];
        $reservaId = $reservaTemporal['reserva_id'];
        $metodoPago = $this->post('metodo_pago');
        
        if (!$metodoPago) {
            $this->redirect('/reservas/pago', 'Seleccione un método de pago', 'error');
            return;
        }
        
        try {
            // SEGUNDA TRANSACCIÓN: Registrar pago, confirmar reserva y ocupar cabaña
            
            // 1. Registrar el pago (si existe tabla de pagos)
            // Por ahora lo omitimos o creamos un registro simple
            
            // 2. Cambiar estado de la reserva a CONFIRMADA (asumiendo estado 2)
            $updateReserva = $this->reservaModel->update($reservaId, [
                'rela_estadoreserva' => 2, // Estado CONFIRMADA
                'rela_metodopago' => $metodoPago
            ]);
            
            if (!$updateReserva) {
                throw new \Exception('Error al confirmar la reserva');
            }
            
            // 3. Cambiar estado de la cabaña a OCUPADA (estado 2)
            $updateCabania = $this->cabaniaModel->update($reservaTemporal['cabania_id'], [
                'cabania_estado' => 2 // Estado OCUPADA
            ]);
            
            if (!$updateCabania) {
                error_log("Error al cambiar estado de cabaña a ocupada");
                // No lanzamos excepción aquí para no interrumpir el proceso
            }
            
            // 4. Guardar información final para la vista de éxito
            $_SESSION['reserva_exitosa'] = [
                'reserva_id' => $reservaId,
                'cabania_nombre' => $reservaTemporal['cabania_id'],
                'total_pagado' => $reservaTemporal['total_general'],
                'fecha_confirmacion' => date('Y-m-d H:i:s')
            ];
            
            // 5. Limpiar datos temporales
            unset($_SESSION['reserva_temporal']);
            
            // 6. Redirigir a la vista de éxito
            $this->redirect('/reservas/exito', '', 'success');

        } catch (\Exception $e) {
            $this->redirect('/reservas/pago', 'Error al confirmar pago: ' . $e->getMessage(), 'error');
        }
    }

    public function exito()
    {
        $this->requireAuth();
        
        // Verificar que existan datos de reserva exitosa
        if (!isset($_SESSION['reserva_exitosa'])) {
            $this->redirect('/catalogo', 'No hay información de reserva disponible', 'error');
            return;
        }
        
        $reservaExitosa = $_SESSION['reserva_exitosa'];
        
        // Obtener información completa de la reserva
        $reserva = $this->reservaModel->find($reservaExitosa['reserva_id']);
        $cabania = null;
        
        if ($reserva) {
            $cabania = $this->cabaniaModel->find($reserva['rela_cabania']);
        }
        
        $data = [
            'title' => 'Reserva Confirmada',
            'reserva_exitosa' => $reservaExitosa,
            'reserva' => $reserva,
            'cabania' => $cabania,
            'isAdminArea' => false
        ];
        
        // Limpiar datos de éxito después de mostrar la vista
        unset($_SESSION['reserva_exitosa']);
        
        return $this->render('public/reservas/exito', $data, 'main');
    }

    protected function storeOnline()
    {
        $userId = \App\Core\Auth::id();
        $userModel = new \App\Models\Usuario();
        $user = $userModel->findWithProfile($userId);
        $data = [
            'rela_cabania' => $this->post('rela_cabania'),
            'rela_persona' => $user['rela_persona'],
            'reserva_fechainicio' => $this->post('reserva_fechainicio'),
            'reserva_fechafin' => $this->post('reserva_fechafin'),
            'reserva_cantidadpersonas' => $this->post('reserva_cantidadpersonas'),
            'rela_metodopago' => $this->post('rela_metodopago'),
            'reserva_observaciones' => $this->post('reserva_observaciones', ''),
            'rela_estadoreserva' => 1
        ];
        try {
            $id = $this->reservaModel->createReservation($data);
            if ($id) {
                $this->redirect('/', 'Reserva enviada correctamente. Nos pondremos en contacto para confirmar.', 'exito');
            } else {
                $this->redirect('/admin/operaciones/reservas/reserva_online', 'Error al crear la reserva', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/admin/operaciones/reservas/reserva_online', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    public function confirm($id)
    {
        $this->requirePermission('reservas');
        $result = $this->reservaModel->confirm($id);
        $message = $result['success'] ? $result['message'] : $result['message'];
        $type = $result['success'] ? 'exito' : 'error';
        $this->redirect('/reservas', $message, $type);
    }

    public function cancel($id)
    {
        $this->requirePermission('reservas');
        $result = $this->reservaModel->cancel($id);
        $message = $result['success'] ? $result['message'] : $result['message'];
        $type = $result['success'] ? 'exito' : 'error';
        $this->redirect('/reservas', $message, $type);
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
}