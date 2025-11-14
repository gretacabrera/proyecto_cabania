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
        
        // Obtener reservas completas del usuario con todos los detalles
        $reservas = $this->consumoModel->getReservasUsuario($userId);
        
        // Obtener ID de reserva seleccionada o la más reciente
        $reservaId = $this->get('reserva_id');
        if (!$reservaId && !empty($reservas)) {
            $reservaId = $reservas[0]['id_reserva'];
        }
        
        // Obtener consumos de la reserva
        $consumos = [];
        $totalConsumos = 0;
        if ($reservaId) {
            $consumos = $this->consumoModel->getConsumosByReservaWithDetails($reservaId);
            foreach ($consumos as $consumo) {
                // El total ya viene calculado en consumo_total
                $totalConsumos += floatval($consumo['consumo_total']);
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

        // Obtener reserva actual del usuario
        $reservaActual = $this->consumoModel->getReservaActualUsuario($userId);
        if (!$reservaActual) {
            $this->redirect('/huesped/consumos', 'No tiene una reserva confirmada disponible para solicitar consumos', 'warning');
            return;
        }

        if ($this->isPost()) {
            // Recibir carrito de consumos
            $carrito = json_decode($this->post('carrito', '[]'), true);
            
            if (empty($carrito)) {
                $this->redirect('/huesped/consumos/solicitar', 'El carrito está vacío', 'error');
                return;
            }
            
            // Preparar array de consumos desde el carrito
            $consumosData = [];
            
            foreach ($carrito as $item) {
                if (empty($item['id']) || empty($item['tipo']) || empty($item['cantidad'])) continue;
                
                $cantidad = floatval($item['cantidad']);
                if ($cantidad <= 0) continue;
                
                $consumoData = [
                    'rela_reserva' => $reservaActual['id_reserva'],
                    'consumo_cantidad' => $cantidad
                ];
                
                if ($item['tipo'] === 'producto') {
                    $consumoData['rela_producto'] = $item['id'];
                    $consumoData['rela_servicio'] = null;
                    $consumoData['consumo_descripcion'] = $item['nombre'];
                } else {
                    $consumoData['rela_servicio'] = $item['id'];
                    $consumoData['rela_producto'] = null;
                    $consumoData['consumo_descripcion'] = $item['nombre'];
                }
                
                $consumosData[] = $consumoData;
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

        $data = [
            'title' => 'Solicitar Consumos',
            'reserva' => $reservaActual,
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
        $reservaIds = array_column($reservasUsuario, 'id_reserva');
        
        if (!in_array($consumo['rela_reserva'], $reservaIds)) {
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
        $reservaIds = array_column($reservasUsuario, 'id_reserva');
        
        if (!in_array($consumo['rela_reserva'], $reservaIds)) {
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

        // Obtener consumo con detalles completos
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT c.*, 
                   p.producto_nombre, p.producto_foto, p.producto_precio,
                   s.servicio_descripcion, s.servicio_precio,
                   COALESCE(p.producto_nombre, s.servicio_descripcion) as item_nombre,
                   COALESCE(p.producto_precio, s.servicio_precio) as item_precio,
                   COALESCE(p.producto_foto, NULL) as producto_foto,
                   r.reserva_fhinicio, r.reserva_fhfin, r.id_reserva,
                   cab.cabania_nombre, cab.cabania_codigo
            FROM consumo c
            LEFT JOIN producto p ON c.rela_producto = p.id_producto
            LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
            LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
            LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
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
        $reservaIds = array_column($reservasUsuario, 'id_reserva');
        
        if (!in_array($consumo['rela_reserva'], $reservaIds)) {
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

    /**
     * API: Obtener categorías de productos
     */
    public function getCategorias()
    {
        header('Content-Type: application/json');
        
        if (!\App\Core\Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $categorias = $this->consumoModel->getCategorias();
        echo json_encode(['success' => true, 'data' => $categorias]);
    }

    /**
     * API: Obtener tipos de servicio
     */
    public function getTiposServicio()
    {
        header('Content-Type: application/json');
        
        if (!\App\Core\Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $tipos = $this->consumoModel->getTiposServicio();
        echo json_encode(['success' => true, 'data' => $tipos]);
    }

    /**
     * API: Obtener productos por categoría
     */
    public function getProductosPorCategoria($categoriaId)
    {
        header('Content-Type: application/json');
        
        if (!\App\Core\Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $productos = $this->consumoModel->getProductosPorCategoria($categoriaId);
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    /**
     * API: Obtener servicios por tipo
     */
    public function getServiciosPorTipo($tipoId)
    {
        header('Content-Type: application/json');
        
        if (!\App\Core\Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        
        $servicios = $this->consumoModel->getServiciosPorTipo($tipoId);
        echo json_encode(['success' => true, 'data' => $servicios]);
    }
}
