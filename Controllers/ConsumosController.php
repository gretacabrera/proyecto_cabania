<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\NotificationService;
use App\Models\Consumo;
use App\Models\EstadoConsumo;

/**
 * Controlador para la gestión de consumos
 */
class ConsumosController extends Controller
{
    protected $consumoModel;
    protected $estadoConsumoModel;
    protected $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->consumoModel = new Consumo();
        $this->estadoConsumoModel = new EstadoConsumo();
        $this->notificationService = new NotificationService();
    }

    /**
     * Listar todos los consumos
     */
    public function index()
    {
        $this->requirePermission('consumos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $filters = [
            'huesped' => $this->get('huesped'),
            'reserva' => $this->get('reserva'),
            'producto' => $this->get('producto'),
            'servicio' => $this->get('servicio'),
            'estado' => $this->get('estado')
        ];
        
        // Si es encargado bar, filtrar solo consumos de hoy
        $userProfile = \App\Core\Auth::getUserProfile();
        if ($userProfile === 'encargado bar') {
            $filters['fecha_hoy'] = true;
        }

        $result = $this->consumoModel->getWithDetails($page, $perPage, $filters);
        
        // Cargar estados de consumo para el filtro
        $estadosConsumo = $this->estadoConsumoModel->getActivos();

        $data = [
            'title' => 'Gestión de Consumos',
            'consumos' => $result['data'],
            'pagination' => $result,
            'estadosConsumo' => $estadosConsumo,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo consumo (múltiple)
     */
    public function create()
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            // Obtener ID de reserva
            $rela_reserva = $this->post('rela_reserva');
            
            // Validar reserva
            if (empty($rela_reserva)) {
                $this->redirect('/consumos/create', 'Debe seleccionar una reserva', 'error');
                return;
            }

            // Verificar si es creación múltiple o simple
            $items = $this->post('items');
            $cantidades = $this->post('cantidades');
            
            if (is_array($items) && is_array($cantidades)) {
                // MODO MÚLTIPLE - procesar array de items
                $registrosExitosos = 0;
                $errores = [];

                for ($i = 0; $i < count($items); $i++) {
                    if (empty($items[$i])) continue;

                    // Parsear item (formato: p_123 o s_456)
                    $itemParts = explode('_', $items[$i]);
                    if (count($itemParts) != 2) continue;

                    $tipo = $itemParts[0]; // 'p' para producto, 's' para servicio
                    $itemId = $itemParts[1];
                    $cantidad = floatval($cantidades[$i] ?? 1);

                    // Obtener datos del item
                    $itemData = null;
                    $descripcion = '';
                    $precioUnitario = 0;

                    if ($tipo == 'p') {
                        // Es producto
                        $itemData = $this->consumoModel->getProductoById($itemId);
                        if ($itemData) {
                            $descripcion = "Producto: " . $itemData['producto_nombre'];
                            $precioUnitario = floatval($itemData['producto_precio']);
                        }
                        $data = [
                            'rela_reserva' => $rela_reserva,
                            'rela_producto' => $itemId,
                            'rela_servicio' => null,
                            'consumo_descripcion' => $descripcion,
                            'consumo_cantidad' => $cantidad,
                            'consumo_total' => $precioUnitario * $cantidad,
                            'rela_estadoconsumo' => 1
                        ];
                    } else if ($tipo == 's') {
                        // Es servicio
                        $itemData = $this->consumoModel->getServicioById($itemId);
                        if ($itemData) {
                            $descripcion = "Servicio: " . $itemData['servicio_nombre'];
                            $precioUnitario = floatval($itemData['servicio_precio']);
                        }
                        $data = [
                            'rela_reserva' => $rela_reserva,
                            'rela_producto' => null,
                            'rela_servicio' => $itemId,
                            'consumo_descripcion' => $descripcion,
                            'consumo_cantidad' => $cantidad,
                            'consumo_total' => $precioUnitario * $cantidad,
                            'rela_estadoconsumo' => 1
                        ];
                    }

                    // Crear registro
                    if (isset($data) && $this->consumoModel->create($data)) {
                        $registrosExitosos++;
                    } else {
                        $errores[] = "Error al registrar: " . $descripcion;
                    }
                }

                if ($registrosExitosos > 0) {
                    // Enviar notificación de nuevo pedido en cabaña
                    try {
                        $reservaModel = new \App\Models\Reserva();
                        $reserva = $reservaModel->find($rela_reserva);
                        if ($reserva) {
                            // Calcular monto total correctamente
                            $montoTotal = 0;
                            
                            foreach ($items as $index => $item) {
                                if (empty($item)) continue;
                                $itemParts = explode('_', $item);
                                if (count($itemParts) != 2) continue;
                                $tipo = $itemParts[0];
                                $itemId = $itemParts[1];
                                $cantidad = floatval($cantidades[$index] ?? 1);
                                
                                if ($tipo == 'p') {
                                    $itemData = $this->consumoModel->getProductoById($itemId);
                                    if ($itemData) {
                                        $precio = (float)$itemData['producto_precio'];
                                        $subtotal = $precio * $cantidad;
                                        $montoTotal += $subtotal;
                                    }
                                } else if ($tipo == 's') {
                                    $itemData = $this->consumoModel->getServicioById($itemId);
                                    if ($itemData) {
                                        $precio = (float)$itemData['servicio_precio'];
                                        $subtotal = $precio * $cantidad;
                                        $montoTotal += $subtotal;
                                    }
                                }
                            }
                            
                            $consumoData = [
                                'items' => $items,
                                'consumo_monto_total' => $montoTotal,
                                'consumo_fecha' => date('Y-m-d H:i:s')
                            ];
                            
                            // Obtener usuario_id del huésped de la reserva
                            $usuarioId = $reservaModel->getUsuarioIdFromReserva($rela_reserva);
                            
                            if ($usuarioId) {
                                $this->notificationService->notifyPedidoCabania($consumoData, $reserva, $usuarioId);
                            }
                        }
                    } catch (\Exception $notifError) {
                        error_log('WARNING: Error enviando notificación de pedido: ' . $notifError->getMessage());
                    }
                    
                    $mensaje = "{$registrosExitosos} consumo(s) registrado(s) exitosamente";
                    if (count($errores) > 0) {
                        $mensaje .= " (con " . count($errores) . " error(es))";
                    }
                    $this->redirect('/consumos', $mensaje, 'success');
                } else {
                    $this->redirect('/consumos/create', 'No se pudo registrar ningún consumo', 'error');
                }
            } else {
                // MODO SIMPLE (no debería llegar aquí en creación, pero por compatibilidad)
                $this->redirect('/consumos/create', 'Datos de formulario inválidos', 'error');
            }
            
            return;
        }

        // Obtener reservas activas, productos y servicios
        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();
        $servicios = $this->consumoModel->getServiciosActivos();

        $data = [
            'title' => 'Registrar Consumos',
            'reservas' => $reservas,
            'productos' => $productos,
            'servicios' => $servicios,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/formulario', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $consumo = $this->consumoModel->findWithRelations($id);
        if (!$consumo) {
            $this->redirect('/consumos', 'Consumo no encontrado', 'error');
        }

        if ($this->isPost()) {
            // Obtener datos del formulario
            $data = [
                'rela_reserva' => $this->post('rela_reserva'),
                'rela_producto' => $this->post('rela_producto') ?: null,
                'rela_servicio' => $this->post('rela_servicio') ?: null,
                'consumo_descripcion' => $this->post('consumo_descripcion'),
                'consumo_cantidad' => floatval($this->post('consumo_cantidad', 1)),
                'consumo_total' => floatval($this->post('consumo_total', 0)),
                'rela_estadoconsumo' => (int) $this->post('rela_estadoconsumo', 1)
            ];
            
            // Validar datos básicos
            if (empty($data['rela_producto']) && empty($data['rela_servicio'])) {
                $this->redirect("/consumos/{$id}/edit", 'Debe seleccionar un producto o un servicio', 'error');
                return;
            }
            
            if (empty($data['consumo_descripcion'])) {
                $this->redirect("/consumos/{$id}/edit", 'La descripción es obligatoria', 'error');
                return;
            }

            if ($this->consumoModel->update($id, $data)) {
                $this->redirect('/consumos', 'Consumo actualizado exitosamente', 'success');
            } else {
                $this->redirect("/consumos/{$id}/edit", 'Error al actualizar el consumo', 'error');
            }
            
            return;
        }

        $reservas = $this->consumoModel->getReservasActivas();
        $productos = $this->consumoModel->getProductosActivos();
        $servicios = $this->consumoModel->getServiciosActivos();
        $estadosConsumo = $this->estadoConsumoModel->getActivos();

        $data = [
            'title' => 'Editar Consumo',
            'consumo' => $consumo,
            'reservas' => $reservas,
            'productos' => $productos,
            'servicios' => $servicios,
            'estadosConsumo' => $estadosConsumo,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/formulario', $data, 'main');
    }

    /**
     * Ver detalle del consumo
     */
    public function show($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $consumo = $this->consumoModel->findWithRelations($id);
        if (!$consumo) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Detalle del Consumo',
            'consumo' => $consumo,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/consumos/detalle', $data, 'main');
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->consumoModel->softDelete($id)) {
            $this->redirect('/consumos', 'Consumo eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/consumos', 'Error al eliminar el consumo', 'error');
        }
    }

    /**
     * Restaurar consumo
     */
    public function restore($id)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        if ($this->consumoModel->restore($id)) {
            $this->redirect('/consumos', 'Consumo restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/consumos', 'Error al restaurar el consumo', 'error');
        }
    }

    /**
     * Cambiar estado del consumo (AJAX)
     */
    /**
     * Cambiar estado de un consumo
     * POST con parámetro 'nuevo_estado' (ID de estadoconsumo)
     */
    public function cambiarEstado($id)
    {
        // Verificar permisos: puede ser admin con permiso 'consumos' o perfil 'encargado bar'
        $hasPermission = $this->hasPermission('consumos');
        $isEncargadoBar = \App\Core\Auth::getUserProfile() === 'encargado bar';
        
        if (!$hasPermission && !$isEncargadoBar) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        $consumo = $this->consumoModel->findWithRelations($id);
        if (!$consumo) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Consumo no encontrado']);
            return;
        }

        // Parsear JSON si viene como application/json
        $jsonData = json_decode(file_get_contents('php://input'), true);
        
        // Obtener nuevo estado desde POST o JSON
        $nuevoEstadoId = $jsonData['nuevo_estado'] ?? $this->post('nuevo_estado');
        
        if (!$nuevoEstadoId) {
            // Comportamiento legacy: si no se especifica, alternar entre pendiente(1) y entregado(3)
            $nuevoEstadoId = $consumo['rela_estadoconsumo'] == 1 ? 3 : 1;
        }
        
        // Obtener nueva cantidad si se envió (para encargado bar)
        $nuevaCantidad = $jsonData['cantidad'] ?? $this->post('cantidad');
        
        $dataUpdate = ['rela_estadoconsumo' => $nuevoEstadoId];
        
        if ($nuevaCantidad && $nuevaCantidad > 0) {
            $dataUpdate['consumo_cantidad'] = (int) $nuevaCantidad;
            // Recalcular total si se cambió la cantidad
            if (isset($consumo['consumo_total']) && isset($consumo['consumo_cantidad']) && $consumo['consumo_cantidad'] > 0) {
                $precioUnitario = $consumo['consumo_total'] / $consumo['consumo_cantidad'];
                $dataUpdate['consumo_total'] = $precioUnitario * (int) $nuevaCantidad;
            }
        }
        
        $estadoAnterior = $consumo['rela_estadoconsumo'];
        $cantidadParaStock = isset($dataUpdate['consumo_cantidad']) ? (int) $dataUpdate['consumo_cantidad'] : (int) $consumo['consumo_cantidad'];
        
        // Iniciar transacción para asegurar atomicidad
        $db = \App\Core\Database::getInstance();
        $db->beginTransaction();
        
        try {
            // 1. Actualizar estado y cantidad del consumo
            if (!$this->consumoModel->update($id, $dataUpdate)) {
                throw new \Exception('Error al actualizar el consumo');
            }
            
            // 2. GESTIÓN DE STOCK según cambio de estado
            // Si cambia a estado "entregado" (3) y es un producto, descontar stock
            if ($nuevoEstadoId == 3 && $estadoAnterior != 3 && !empty($consumo['rela_producto'])) {
                $productoModel = new \App\Models\Producto();
                $productoMovimientoModel = new \App\Models\ProductoMovimiento();
                
                // SIEMPRE descontar stock al entregar (incluso en reactivaciones)
                $productoModel->updateStock($consumo['rela_producto'], $cantidadParaStock, 'subtract');
                
                // Verificar si viene de reactivación para descripción apropiada
                $tipoReact = $productoMovimientoModel->verificarReactivacion($id);
                
                $producto = $productoModel->find($consumo['rela_producto']);
                
                if ($tipoReact === 'error') {
                    $descripcion = "Consumo entregado (post-corrección) - Reserva #{$consumo['rela_reserva']} - {$producto['producto_nombre']}";
                } elseif ($tipoReact === 'reintento') {
                    $descripcion = "Consumo entregado (reintento con nuevo producto) - Reserva #{$consumo['rela_reserva']} - {$producto['producto_nombre']}";
                } else {
                    $descripcion = "Consumo entregado - Reserva #{$consumo['rela_reserva']} - {$producto['producto_nombre']}";
                }
                
                $productoMovimientoModel->registrarMovimiento(
                    $consumo['rela_producto'],
                    'S', // Salida (siempre descuenta)
                    $cantidadParaStock,
                    $descripcion
                );
            }
            
            // Si cambia de "entregado" (3) a "anulado por inconveniente" (5) y es un producto, devolver stock
            if ($estadoAnterior == 3 && $nuevoEstadoId == 5 && !empty($consumo['rela_producto'])) {
                $productoModel = new \App\Models\Producto();
                $productoMovimientoModel = new \App\Models\ProductoMovimiento();
                
                // Devolver stock con la cantidad ORIGINAL (antes de cualquier cambio)
                $cantidadOriginal = (int) $consumo['consumo_cantidad'];
                $productoModel->updateStock($consumo['rela_producto'], $cantidadOriginal, 'add');
                
                // Registrar movimiento de entrada (devolución)
                $producto = $productoModel->find($consumo['rela_producto']);
                $descripcion = "Devolución por anulación - Consumo #{$id} - Reserva #{$consumo['rela_reserva']} - {$producto['producto_nombre']}";
                $productoMovimientoModel->registrarMovimiento(
                    $consumo['rela_producto'],
                    'E', // Entrada
                    $cantidadOriginal,
                    $descripcion
                );
            }
            
            // REACTIVACIÓN: Estado 2 desde Estado 7 (Pérdida)
            if ($estadoAnterior == 7 && $nuevoEstadoId == 2 && !empty($consumo['rela_producto'])) {
                $productoModel = new \App\Models\Producto();
                $productoMovimientoModel = new \App\Models\ProductoMovimiento();
                
                // Obtener tipo de reactivación desde el request
                $tipoReactivacion = isset($jsonData['tipo_reactivacion']) ? $jsonData['tipo_reactivacion'] : null;
                
                if (!$tipoReactivacion || !in_array($tipoReactivacion, ['error', 'reintento'])) {
                    throw new \Exception('Debe especificar el tipo de reactivación');
                }
                
                if ($tipoReactivacion == 'error') {
                    // ESCENARIO 1: Error administrativo - DEVOLVER stock
                    $productoModel->updateStock($consumo['rela_producto'], $cantidadParaStock, 'add');
                    
                    $descripcion = "Corrección de error - Stock devuelto - Consumo #{$id} - Reserva #{$consumo['rela_reserva']}";
                    $productoMovimientoModel->registrarMovimiento(
                        $consumo['rela_producto'],
                        'C', // Corrección (devuelve stock)
                        $cantidadParaStock,
                        $descripcion
                    );
                    
                } else {
                    // ESCENARIO 2: Reintento real - NO TOCAR STOCK (ya descontado)
                    // NO se toca el stock porque el producto ya se perdió
                    $descripcion = "Reintento - Sin descuento de stock - Consumo #{$id} - Reserva #{$consumo['rela_reserva']} (Producto anterior ya perdido)";
                    $productoMovimientoModel->registrarMovimiento(
                        $consumo['rela_producto'],
                        'A', // Ajuste informativo (sin impacto en stock)
                        0,
                        $descripcion
                    );
                }
            }
            
            // Estado 7: Anulado por pérdida (sin reintegro de stock)
            if ($nuevoEstadoId == 7 && !empty($consumo['rela_producto'])) {
                $productoModel = new \App\Models\Producto();
                $productoMovimientoModel = new \App\Models\ProductoMovimiento();
                $producto = $productoModel->find($consumo['rela_producto']);
                $cantidadPerdida = (int) $consumo['consumo_cantidad'];
                
                // CASO 1: Pérdida desde "En proceso" (estado 2)
                if ($estadoAnterior == 2) {
                    // Descontar stock porque el producto se perdió físicamente durante la preparación
                    $productoModel->updateStock($consumo['rela_producto'], $cantidadPerdida, 'subtract');
                    
                    // Registrar como pérdida/merma
                    $descripcion = "Pérdida de producto - Consumo #{$id} - Reserva #{$consumo['rela_reserva']} - {$producto['producto_nombre']} (Dañado en preparación)";
                    $productoMovimientoModel->registrarMovimiento(
                        $consumo['rela_producto'],
                        'S', // Salida por pérdida
                        $cantidadPerdida,
                        $descripcion
                    );
                
                // CASO 2: Pérdida desde "Entregado" (estado 3)
                } elseif ($estadoAnterior == 3) {
                    // Stock ya fue descontado al entregar, NO se toca
                    // Solo registrar ajuste para reclasificar de venta a pérdida
                    $descripcion = "Reclasificación a pérdida - Consumo #{$id} - Reserva #{$consumo['rela_reserva']} - {$producto['producto_nombre']} (Originalmente entregado como venta)";
                    $productoMovimientoModel->registrarMovimiento(
                        $consumo['rela_producto'],
                        'A', // Ajuste (reclasificación sin afectar stock)
                        0,   // Cantidad 0 porque no afecta stock físico
                        $descripcion
                    );
                    
                } else {
                    throw new \Exception('Solo se puede registrar pérdida desde estados "En proceso" o "Entregado"');
                }
            }
            
            // Si todo salió bien, confirmar la transacción
            $db->commit();
            
        } catch (\Exception $e) {
            // Si hubo algún error, revertir todos los cambios
            $db->rollback();
            
            error_log("Error en transacción de cambio de estado: " . $e->getMessage());
            header('Content-Type: application/json');
            
            // Mensaje más descriptivo para el usuario
            $mensajeUsuario = $e->getMessage();
            if (strpos($mensajeUsuario, 'Stock insuficiente') !== false) {
                $mensajeUsuario = 'No hay suficiente stock disponible para entregar este producto. Por favor, verifique el inventario.';
            }
            
            echo json_encode(['success' => false, 'message' => $mensajeUsuario]);
            return;
        }
        
        // Enviar notificaciones según el cambio de estado (fuera de la transacción)
        // NO enviar notificación si es una reactivación (estado 7 -> 2) para que sea transparente al huésped
        if ($estadoAnterior != $nuevoEstadoId && !($estadoAnterior == 7 && $nuevoEstadoId == 2)) {
                try {
                    $reservaId = $consumo['rela_reserva'] ?? null;
                    $usuarioId = null;
                    $reserva = null;
                    
                    if ($reservaId) {
                        $reservaModel = new \App\Models\Reserva();
                        $usuarioId = $reservaModel->getUsuarioIdFromReserva($reservaId);
                        $reserva = $reservaModel->find($reservaId);
                    }
                    
                    if ($usuarioId && $reserva) {
                        // Estado 2: Confirmado (en proceso)
                        if ($nuevoEstadoId == 2) {
                            $this->notificationService->notifyPedidoConfirmado(
                                $consumo,
                                $reserva,
                                $usuarioId
                            );
                            error_log("Notificación de pedido confirmado enviada - Consumo: $id, Usuario: $usuarioId");
                        }
                        // Estado 3: Entregado
                        elseif ($nuevoEstadoId == 3) {
                            $this->notificationService->notifyPedidoEntregado(
                                $consumo,
                                $reserva,
                                $usuarioId
                            );
                            error_log("Notificación de pedido entregado enviada - Consumo: $id, Usuario: $usuarioId");
                        }
                        // Estado 4: Anulado por falta de stock
                        // Estado 5: Anulado por inconveniente
                        elseif ($nuevoEstadoId == 4 || $nuevoEstadoId == 5) {
                            $tipoInconveniente = $nuevoEstadoId == 4 
                                ? 'Producto sin stock' 
                                : 'Inconveniente con el pedido';
                            
                            $descripcion = $nuevoEstadoId == 4
                                ? 'Lo sentimos, el producto solicitado no está disponible en este momento'
                                : 'Ha surgido un inconveniente con tu pedido. Por favor contacta con recepción';
                            
                            $this->notificationService->notifyInconvenientePedido(
                                $consumo,
                                $tipoInconveniente,
                                $descripcion,
                                $usuarioId
                            );
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Error enviando notificación de estado: " . $e->getMessage());
                }
            }
            
            // Mensajes según el estado
            $mensajes = [
                1 => 'Consumo marcado como pendiente',
                2 => 'Consumo en proceso',
                3 => 'Consumo entregado',
                4 => 'Consumo anulado por falta de stock',
                5 => 'Consumo anulado por inconveniente',
                6 => 'Consumo cancelado',
                7 => 'Consumo anulado por pérdida (sin reintegro de stock)'
            ];
            
            $mensaje = $mensajes[$nuevoEstadoId] ?? 'Estado actualizado';
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => $mensaje, 
                'nuevoEstado' => $nuevoEstadoId
            ]);
    }

    /**
     * Reportar inconveniente con un pedido/consumo (AJAX)
     */
    public function reportarInconveniente($id)
    {
        if (!$this->hasPermission('consumos')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        $consumo = $this->consumoModel->findWithRelations($id);
        if (!$consumo) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Consumo no encontrado']);
            return;
        }

        $tipoInconveniente = $this->post('tipo_inconveniente');
        $descripcion = $this->post('descripcion', '');
        
        if (empty($tipoInconveniente)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe especificar el tipo de inconveniente']);
            return;
        }

        // Enviar notificación de inconveniente
        try {
            // Obtener usuario_id del huésped de la reserva
            $reservaId = $consumo['rela_reserva'] ?? null;
            $usuarioId = null;
            
            if ($reservaId) {
                $reservaModel = new \App\Models\Reserva();
                $usuarioId = $reservaModel->getUsuarioIdFromReserva($reservaId);
            }
            
            if ($usuarioId) {
                $this->notificationService->notifyInconvenientePedido($consumo, $tipoInconveniente, $descripcion, $usuarioId);
            }
            
            // Opcionalmente, agregar observación al consumo
            $observacionActual = $consumo['consumo_descripcion'] ?? '';
            $nuevaObservacion = $observacionActual . " [INCONVENIENTE: {$tipoInconveniente} - {$descripcion}]";
            $this->consumoModel->update($id, ['consumo_descripcion' => $nuevaObservacion]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Inconveniente reportado exitosamente']);
        } catch (\Exception $e) {
            error_log('ERROR reportando inconveniente: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al reportar inconveniente']);
        }
    }

    /**
     * Ver consumos por reserva
     */
    public function byReserva($reservaId)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $consumos = $this->consumoModel->getByReserva($reservaId, $page);
        $reserva = $this->consumoModel->getReservaInfo($reservaId);

        if (!$reserva) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Consumos de la Reserva',
            'consumos' => $consumos,
            'reserva' => $reserva,
            'currentPage' => $page
        ];

        return $this->render('admin/operaciones/consumos/por_reserva', $data);
    }

    /**
     * Facturar consumos
     */
    public function facturar($reservaId)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $consumos = $this->consumoModel->getPendingByReserva($reservaId);
        $reserva = $this->consumoModel->getReservaInfo($reservaId);

        if (!$reserva) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            $consumosIds = $this->post('consumos', []);
            
            if (empty($consumosIds)) {
                $this->redirect("/admin/operaciones/consumos/facturar/{$reservaId}", 'Debe seleccionar al menos un consumo', 'error');
            }

            if ($this->consumoModel->marcarComoFacturados($consumosIds)) {
                $this->redirect('/consumos', 'Consumos facturados exitosamente', 'exito');
            } else {
                $this->redirect("/admin/operaciones/consumos/facturar/{$reservaId}", 'Error al facturar consumos', 'error');
            }
        }

        $data = [
            'title' => 'Facturar Consumos',
            'consumos' => $consumos,
            'reserva' => $reserva
        ];

        return $this->render('admin/operaciones/consumos/facturar', $data);
    }

    /**
     * Obtener precio actual del producto (AJAX)
     */
    public function getPrecioProducto($productoId)
    {
        if (!$this->hasPermission('consumos')) {
            return $this->json(['error' => 'Sin permisos'], 403);
        }

        $producto = $this->consumoModel->getProducto($productoId);
        
        if ($producto) {
            return $this->json(['precio' => $producto['producto_precio']]);
        } else {
            return $this->json(['error' => 'Producto no encontrado'], 404);
        }
    }

    /**
     * Reporte de consumos
     */
    public function reporte()
    {
        if (!$this->hasPermission('consumos')) {
            return $this->view->error(403);
        }

        $fechaDesde = $this->get('fecha_desde');
        $fechaHasta = $this->get('fecha_hasta');
        $tipoReporte = $this->get('tipo', 'resumen');

        $reporteData = [];
        
        if ($fechaDesde && $fechaHasta) {
            switch ($tipoReporte) {
                case 'resumen':
                    $reporteData = $this->consumoModel->getResumenConsumos($fechaDesde, $fechaHasta);
                    break;
                case 'detallado':
                    $reporteData = $this->consumoModel->getDetalleConsumos($fechaDesde, $fechaHasta);
                    break;
                case 'productos':
                    $reporteData = $this->consumoModel->getConsumosPorProducto($fechaDesde, $fechaHasta);
                    break;
            }
        }

        $data = [
            'title' => 'Reporte de Consumos',
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'tipoReporte' => $tipoReporte,
            'reporteData' => $reporteData
        ];

        return $this->render('admin/operaciones/consumos/reporte', $data);
    }

    /**
     * Exportar consumos a Excel
     */
    public function exportar()
    {
        $this->requirePermission('consumos');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'huesped' => $this->get('huesped'),
                'reserva' => $this->get('reserva'),
                'producto' => $this->get('producto'),
                'servicio' => $this->get('servicio'),
                'estado' => $this->get('estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->consumoModel->getAllWithDetailsForExport($filters);
            $consumos = $result['data'];

            if (empty($consumos)) {
                $this->redirect('/consumos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Consumos');

            // Definir encabezados
            $headers = [
                'A1' => 'ID',
                'B1' => 'Reserva',
                'C1' => 'Huésped',
                'D1' => 'Descripción',
                'E1' => 'Cantidad',
                'F1' => 'Precio Unit.',
                'G1' => 'Total',
                'H1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:H1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:H1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($consumos as $consumo) {
                $estadoTexto = $consumo['estadoconsumo_descripcion'] ?? 'Desconocido';
                $huesped = trim(($consumo['huesped_nombre'] ?? '') . ' ' . ($consumo['huesped_apellido'] ?? ''));
                $precioUnitario = $consumo['consumo_cantidad'] > 0 ? $consumo['consumo_total'] / $consumo['consumo_cantidad'] : 0;

                $worksheet->setCellValue('A' . $row, $consumo['id_consumo']);
                $worksheet->setCellValue('B' . $row, '#' . $consumo['rela_reserva']);
                $worksheet->setCellValue('C' . $row, $huesped ?: 'N/A');
                $worksheet->setCellValue('D' . $row, $consumo['consumo_descripcion']);
                $worksheet->setCellValue('E' . $row, $consumo['consumo_cantidad']);
                $worksheet->setCellValue('F' . $row, number_format($precioUnitario, 2));
                $worksheet->setCellValue('G' . $row, number_format($consumo['consumo_total'], 2));
                $worksheet->setCellValue('H' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(8);
            $worksheet->getColumnDimension('B')->setWidth(12);
            $worksheet->getColumnDimension('C')->setWidth(25);
            $worksheet->getColumnDimension('D')->setWidth(40);
            $worksheet->getColumnDimension('E')->setWidth(12);
            $worksheet->getColumnDimension('F')->setWidth(15);
            $worksheet->getColumnDimension('G')->setWidth(15);
            $worksheet->getColumnDimension('H')->setWidth(12);

            // Aplicar formato a las columnas de precio
            $worksheet->getStyle('F2:G' . ($row - 1))->getNumberFormat()->setFormatCode('$#,##0.00');

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "consumos_{$fecha}.xlsx";

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
            error_log("Error al exportar consumos: " . $e->getMessage());
            $this->redirect('/consumos', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar consumos a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('consumos');

        try {
            // Obtener todos los filtros de la URL
            $filters = [
                'huesped' => $this->get('huesped'),
                'reserva' => $this->get('reserva'),
                'producto' => $this->get('producto'),
                'servicio' => $this->get('servicio'),
                'estado' => $this->get('estado')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->consumoModel->getAllWithDetailsForExport($filters);
            $consumos = $result['data'];

            if (empty($consumos)) {
                $this->redirect('/consumos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configurar documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Reporte de Consumos');
            $pdf->SetSubject('Listado de Consumos');

            // Remover header/footer por defecto
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Configurar márgenes
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Agregar página
            $pdf->AddPage();

            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Reporte de Consumos', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados
            $pdf->SetFont('helvetica', '', 9);
            $filtrosAplicados = [];
            if (!empty($filters['huesped'])) $filtrosAplicados[] = "Huésped: {$filters['huesped']}";
            if (!empty($filters['reserva'])) $filtrosAplicados[] = "Reserva: #{$filters['reserva']}";
            if (!empty($filters['producto'])) $filtrosAplicados[] = "Producto: {$filters['producto']}";
            if (!empty($filters['servicio'])) $filtrosAplicados[] = "Servicio: {$filters['servicio']}";
            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $filtrosAplicados[] = "Estado: " . ($filters['estado'] == 1 ? 'Activo' : 'Inactivo');
            }

            if (!empty($filtrosAplicados)) {
                $pdf->Cell(0, 5, 'Filtros aplicados: ' . implode(' | ', $filtrosAplicados), 0, 1);
            }
            $pdf->Cell(0, 5, 'Total de registros: ' . count($consumos), 0, 1);
            $pdf->Cell(0, 5, 'Fecha de generación: ' . date('d/m/Y H:i:s'), 0, 1);
            $pdf->Ln(5);

            // Crear tabla HTML
            $html = '<table border="1" cellpadding="4" cellspacing="0" style="font-size: 8px;">
                <thead>
                    <tr style="background-color: #E3F2FD; font-weight: bold;">
                        <th width="8%">Reserva</th>
                        <th width="20%">Huésped</th>
                        <th width="35%">Descripción</th>
                        <th width="8%">Cant.</th>
                        <th width="12%">P. Unit.</th>
                        <th width="12%">Total</th>
                        <th width="10%">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($consumos as $consumo) {
                $estadoTexto = $consumo['estadoconsumo_descripcion'] ?? 'Desconocido';
                $huesped = trim(($consumo['huesped_nombre'] ?? '') . ' ' . ($consumo['huesped_apellido'] ?? ''));
                $precioUnitario = $consumo['consumo_cantidad'] > 0 ? $consumo['consumo_total'] / $consumo['consumo_cantidad'] : 0;
                $descripcion = substr($consumo['consumo_descripcion'], 0, 60) . (strlen($consumo['consumo_descripcion']) > 60 ? '...' : '');

                $html .= '<tr>
                    <td width="8%">#' . $consumo['rela_reserva'] . '</td>
                    <td width="20%">' . htmlspecialchars($huesped ?: 'N/A') . '</td>
                    <td width="35%">' . htmlspecialchars($descripcion) . '</td>
                    <td width="8%" align="center">' . $consumo['consumo_cantidad'] . '</td>
                    <td width="12%" align="right">$' . number_format($precioUnitario, 2) . '</td>
                    <td width="12%" align="right">$' . number_format($consumo['consumo_total'], 2) . '</td>
                    <td width="10%" align="center">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir tabla
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo
            $fecha = date('Y-m-d');
            $nombreArchivo = "consumos_{$fecha}.pdf";

            // Salida del PDF
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar PDF de consumos: " . $e->getMessage());
            $this->redirect('/consumos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}
