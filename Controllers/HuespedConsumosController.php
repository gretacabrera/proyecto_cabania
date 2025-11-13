<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Consumo;
use App\Models\Reserva;

/**
 * Controlador para la gestión de consumos del huésped
 * Módulo público para que los huéspedes soliciten y visualicen sus consumos
 */
class HuespedConsumosController extends Controller
{
    protected $consumoModel;
    protected $reservaModel;

    public function __construct()
    {
        parent::__construct();
        $this->consumoModel = new Consumo();
        $this->reservaModel = new Reserva();
    }

    /**
     * Listar consumos del huésped autenticado
     */
    public function index()
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para ver sus consumos', 'error');
            return;
        }
        
        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para ver sus consumos', 'error');
            return;
        }
        
        // Obtener IDs de reservas del usuario
        $reservasIds = $this->consumoModel->getReservasUsuario($userId);
        
        // Obtener reservas completas con detalles
        $reservas = [];
        if (!empty($reservasIds)) {
            foreach ($reservasIds as $id) {
                $reserva = $this->reservaModel->find($id);
                if ($reserva) {
                    $reservas[] = $reserva;
                }
            }
        }
        
        // Obtener ID de reserva seleccionada o la más reciente
        $reservaId = $this->get('reserva_id');
        if (!$reservaId && !empty($reservasIds)) {
            $reservaId = $reservasIds[0];
        }
        
        // Obtener consumos de la reserva
        $consumos = [];
        $totalConsumos = 0;
        if ($reservaId) {
            $consumos = $this->consumoModel->getConsumosByReservaWithDetails($reservaId);
            foreach ($consumos as $consumo) {
                $subtotal = floatval($consumo['consumo_cantidad']) * floatval($consumo['consumo_precio']);
                $totalConsumos += $subtotal;
            }
        }
        
        $data = [
            'title' => 'Mis Consumos',
            'reservas' => $reservas,
            'consumos' => $consumos,
            'reservaId' => $reservaId,
            'totalConsumos' => $totalConsumos,
            'isPublicArea' => true
        ];

        return $this->render('public/consumos/listado', $data, 'main');
    }

    /**
     * Solicitar nuevos consumos (múltiples)
     */
    public function solicitar()
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para solicitar consumos', 'error');
            return;
        }

        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para solicitar consumos', 'error');
            return;
        }

        if ($this->isPost()) {
            $reservaId = $this->post('reserva_id');
            $productosIds = $this->post('productos', []);
            $cantidades = $this->post('cantidades', []);
            
            // Verificar que la reserva pertenece al usuario
            $reservasUsuario = $this->consumoModel->getReservasUsuario($userId);
            if (!in_array($reservaId, $reservasUsuario)) {
                $this->redirect('/huesped/consumos', 'No tiene permiso para solicitar consumos en esta reserva', 'error');
                return;
            }
            
            // Validar datos básicos
            if (empty($reservaId) || empty($productosIds)) {
                $this->redirect('/huesped/consumos/solicitar', 'Debe seleccionar una reserva y al menos un producto', 'error');
                return;
            }
            
            // Preparar array de consumos
            $consumosData = [];
            $db = \App\Core\Database::getInstance();
            
            foreach ($productosIds as $index => $productoId) {
                if (empty($productoId)) continue;
                
                $cantidad = floatval($cantidades[$index] ?? 1);
                if ($cantidad <= 0) continue;
                
                // Obtener precio del producto
                $stmt = $db->prepare("SELECT * FROM producto WHERE id_producto = ?");
                $stmt->bind_param("i", $productoId);
                $stmt->execute();
                $producto = $stmt->get_result()->fetch_assoc();
                if (!$producto) continue;
                
                $consumosData[] = [
                    'rela_reserva' => $reservaId,
                    'rela_producto' => $productoId,
                    'consumo_descripcion' => 'Producto: ' . $producto['producto_nombre'],
                    'consumo_cantidad' => $cantidad,
                    'consumo_precio' => $producto['producto_precio']
                ];
            }
            
            if (empty($consumosData)) {
                $this->redirect('/huesped/consumos/solicitar', 'No hay consumos válidos para registrar', 'error');
                return;
            }
            
            // Crear consumos en transacción
            try {
                $idsCreados = $this->consumoModel->createMultiple($consumosData);
                $this->redirect('/huesped/consumos', 'Consumos registrados exitosamente', 'success');
            } catch (\Exception $e) {
                $this->redirect('/huesped/consumos/solicitar', 'Error al registrar consumos: ' . $e->getMessage(), 'error');
            }
            
            return;
        }

        // Obtener IDs de reservas del usuario y luego los datos completos
        $reservasIds = $this->consumoModel->getReservasUsuario($userId);
        $reservas = [];
        if (!empty($reservasIds)) {
            foreach ($reservasIds as $id) {
                $reserva = $this->reservaModel->find($id);
                if ($reserva) {
                    $reservas[] = $reserva;
                }
            }
        }
        $productos = $this->consumoModel->getProductosDisponibles();
        $servicios = $this->consumoModel->getServiciosDisponibles();

        $data = [
            'title' => 'Solicitar Consumos',
            'reservas' => $reservas,
            'productos' => $productos,
            'servicios' => $servicios,
            'isPublicArea' => true
        ];

        return $this->render('public/consumos/solicitar', $data, 'main');
    }

    /**
     * Editar consumo propio
     */
    public function edit($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }
        
        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }

        $consumo = $this->consumoModel->find($id);
        if (!$consumo) {
            $this->redirect('/huesped/consumos', 'Consumo no encontrado', 'error');
            return;
        }
        
        // Verificar que el consumo pertenece a una reserva del usuario
        $reservasUsuario = $this->consumoModel->getReservasUsuario($userId);
        if (!in_array($consumo['rela_reserva'], $reservasUsuario)) {
            $this->redirect('/huesped/consumos', 'No tiene permiso para editar este consumo', 'error');
            return;
        }

        if ($this->isPost()) {
            $cantidad = floatval($this->post('cantidad', 1));
            
            if ($cantidad <= 0) {
                $this->redirect("/huesped/consumos/{$id}/edit", 'La cantidad debe ser mayor a 0', 'error');
                return;
            }
            
            $data = [
                'consumo_cantidad' => $cantidad
                // Precio unitario se mantiene igual (no se modifica)
            ];
            
            if ($this->consumoModel->updateConsumo($id, $data)) {
                $this->redirect('/huesped/consumos', 'Consumo actualizado exitosamente', 'success');
            } else {
                $this->redirect("/huesped/consumos/{$id}/edit", 'Error al actualizar el consumo', 'error');
            }
            
            return;
        }

        $data = [
            'title' => 'Editar Consumo',
            'consumo' => $consumo,
            'isPublicArea' => true
        ];

        return $this->render('public/consumos/editar', $data, 'main');
    }

    /**
     * Eliminar consumo propio (baja lógica)
     */
    public function delete($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            return $this->json(['success' => false, 'message' => 'Debe iniciar sesión'], 401);
        }
        
        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            return $this->json(['success' => false, 'message' => 'Debe iniciar sesión'], 401);
        }

        $consumo = $this->consumoModel->find($id);
        if (!$consumo) {
            return $this->json(['success' => false, 'message' => 'Consumo no encontrado'], 404);
        }
        
        // Verificar que el consumo pertenece a una reserva del usuario
        $reservasUsuario = $this->consumoModel->getReservasUsuario($userId);
        if (!in_array($consumo['rela_reserva'], $reservasUsuario)) {
            return $this->json(['success' => false, 'message' => 'No tiene permiso para eliminar este consumo'], 403);
        }
        
        if ($this->consumoModel->deleteConsumo($id)) {
            return $this->json(['success' => true, 'message' => 'Consumo eliminado exitosamente']);
        } else {
            return $this->json(['success' => false, 'message' => 'Error al eliminar el consumo'], 500);
        }
    }

    /**
     * Ver detalle de consumo
     */
    public function show($id)
    {
        // Verificar autenticación
        if (!\App\Core\Auth::check()) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }
        
        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión', 'error');
            return;
        }

        // Obtener consumo con detalles
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT c.*, 
                   COALESCE(p.producto_nombre, s.servicio_nombre) as item_nombre,
                   COALESCE(p.producto_foto, 'default.jpg') as item_foto,
                   r.reserva_fechainicio, r.reserva_fechafin
            FROM consumo c
            LEFT JOIN producto p ON c.rela_producto = p.id_producto
            LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
            LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
            WHERE c.id_consumo = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $consumo = $stmt->get_result()->fetch_assoc();
        
        if (!$consumo) {
            $this->redirect('/huesped/consumos', 'Consumo no encontrado', 'error');
            return;
        }
        
        // Verificar que el consumo pertenece a una reserva del usuario
        $reservasUsuario = $this->consumoModel->getReservasUsuario($userId);
        if (!in_array($consumo['rela_reserva'], $reservasUsuario)) {
            $this->redirect('/huesped/consumos', 'No tiene permiso para ver este consumo', 'error');
            return;
        }

        $data = [
            'title' => 'Detalle del Consumo',
            'consumo' => $consumo,
            'isPublicArea' => true
        ];

        return $this->render('public/consumos/detalle', $data, 'main');
    }
}
