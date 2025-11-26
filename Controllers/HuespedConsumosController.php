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
        
        // Obtener ID de reserva seleccionada (parámetro GET)
        $reservaId = $this->get('reserva_id');
        
        // Obtener consumos
        $consumos = [];
        $totalConsumos = 0;
        $reservaSeleccionada = null;
        $fechaFactura = null;
        
        if ($reservaId) {
            // Vista de una reserva específica
            $consumos = $this->consumoModel->getConsumosByReservaWithDetails($reservaId);
            
            // Buscar la reserva seleccionada para obtener su estado y tipo
            foreach ($reservas as $reserva) {
                if ($reserva['id_reserva'] == $reservaId) {
                    $reservaSeleccionada = $reserva;
                    break;
                }
            }
            
            // Obtener fecha de factura si la reserva es online y está confirmada
            if ($reservaSeleccionada && $reservaSeleccionada['reserva_online'] == 1) {
                $database = \App\Core\Database::getInstance();
                $sqlFactura = "SELECT factura_fechahora FROM factura WHERE rela_reserva = ? ORDER BY factura_fechahora ASC LIMIT 1";
                $stmtFactura = $database->prepare($sqlFactura);
                $stmtFactura->bind_param("i", $reservaId);
                $stmtFactura->execute();
                $resultFactura = $stmtFactura->get_result()->fetch_assoc();
                $stmtFactura->close();
                if ($resultFactura) {
                    $fechaFactura = $resultFactura['factura_fechahora'];
                }
            }
        } else {
            // Vista de todos los consumos del huésped
            foreach ($reservas as $reserva) {
                $consumosReserva = $this->consumoModel->getConsumosByReservaWithDetails($reserva['id_reserva']);
                $consumos = array_merge($consumos, $consumosReserva);
            }
        }
        
        // Calcular total de consumos
        foreach ($consumos as $consumo) {
            $totalConsumos += floatval($consumo['consumo_total']);
        }
        
        $data = [
            'title' => 'Mis Consumos',
            'reservas' => $reservas,
            'consumos' => $consumos,
            'reservaId' => $reservaId,
            'reservaSeleccionada' => $reservaSeleccionada,
            'fechaFactura' => $fechaFactura,
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
            // Volver a la vista anterior (de donde vino)
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/huesped/consumos');
            $this->redirect($referer, 'No tiene una reserva confirmada disponible para solicitar consumos', 'warning');
            return;
        }
        
        // Validar que la reserva esté en estado permitido para agregar consumos
        // Estados: 1=Pendiente, 2=Confirmada, 3=En Curso, 4=Pendiente de Pago, 8=Pendiente de Revisión
        // No permitidos: 5=Finalizada, 6=Anulada
        $estadosPermitidos = [1, 2, 3, 4, 8];
        if (!in_array($reservaActual['rela_estadoreserva'], $estadosPermitidos)) {
            // Volver a la vista anterior (de donde vino)
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/huesped/consumos');
            $this->redirect($referer, 'No se pueden agregar consumos en el estado actual de la reserva: ' . $reservaActual['estadoreserva_descripcion'], 'error');
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
                $precio = floatval($item['precio'] ?? 0);
                
                if ($cantidad <= 0 || $precio <= 0) continue;
                
                $consumoData = [
                    'rela_reserva' => $reservaActual['id_reserva'],
                    'consumo_cantidad' => $cantidad,
                    'consumo_precio_unitario' => $precio
                ];
                
                if ($item['tipo'] === 'producto') {
                    $consumoData['rela_producto'] = $item['id'];
                    $consumoData['rela_servicio'] = null;
                    $consumoData['consumo_descripcion'] = 'Producto: ' . $item['nombre'];
                } else {
                    $consumoData['rela_servicio'] = $item['id'];
                    $consumoData['rela_producto'] = null;
                    $consumoData['consumo_descripcion'] = 'Servicio: ' . $item['nombre'];
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

        // Detectar si es dispositivo móvil
        $isMobile = $this->isMobileDevice();
        $vista = $isMobile ? 'public/consumos/solicitar-mobile' : 'public/consumos/solicitar';

        return $this->render($vista, $data, 'main');
    }

    /**
     * Detectar si es dispositivo móvil por User Agent
     */
    private function isMobileDevice()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Patrones comunes de dispositivos móviles
        $mobilePatterns = [
            '/android/i',
            '/webos/i',
            '/iphone/i',
            '/ipad/i',
            '/ipod/i',
            '/blackberry/i',
            '/windows phone/i',
            '/mobile/i'
        ];
        
        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
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

        $consumo = $this->consumoModel->findWithRelations($id);
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
        
        // Validar que la reserva esté en estado Confirmada (2) o En Curso (3)
        $reservaDelConsumo = null;
        foreach ($reservasUsuario as $reserva) {
            if ($reserva['id_reserva'] == $consumo['rela_reserva']) {
                $reservaDelConsumo = $reserva;
                break;
            }
        }
        
        $estadosPermitidos = [1, 2, 3, 4, 8];
        if ($reservaDelConsumo && !in_array($reservaDelConsumo['rela_estadoreserva'], $estadosPermitidos)) {
            $this->redirect('/huesped/consumos?reserva_id=' . $consumo['rela_reserva'], 
                'No se puede editar el consumo porque la reserva está en estado: ' . $reservaDelConsumo['estadoreserva_descripcion'], 'error');
            return;
        }
        
        // Solo se pueden editar consumos en estado "solicitud pendiente" (1)
        if ($consumo['rela_estadoconsumo'] != 1) {
            $this->redirect('/huesped/consumos?reserva_id=' . $consumo['rela_reserva'], 
                'Solo se pueden editar consumos en estado "Solicitud Pendiente"', 'error');
            return;
        }
        
        // Para reservas online, verificar si el consumo ya fue facturado
        if ($reservaDelConsumo && $reservaDelConsumo['reserva_online'] == 1) {
            $database = \App\Core\Database::getInstance();
            $sqlFactura = "SELECT factura_fechahora FROM factura WHERE rela_reserva = ? ORDER BY factura_fechahora ASC LIMIT 1";
            $stmtFactura = $database->prepare($sqlFactura);
            $stmtFactura->bind_param("i", $consumo['rela_reserva']);
            $stmtFactura->execute();
            $resultFactura = $stmtFactura->get_result()->fetch_assoc();
            $stmtFactura->close();
            
            if ($resultFactura && $consumo['consumo_fechahora'] <= $resultFactura['factura_fechahora']) {
                $this->redirect('/huesped/consumos?reserva_id=' . $consumo['rela_reserva'], 
                    'No se puede editar este consumo porque ya fue facturado y pagado', 'error');
                return;
            }
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
        
        // Validar que la reserva esté en estado Confirmada (2) o En Curso (3)
        $reservaDelConsumo = null;
        foreach ($reservasUsuario as $reserva) {
            if ($reserva['id_reserva'] == $consumo['rela_reserva']) {
                $reservaDelConsumo = $reserva;
                break;
            }
        }
        
        $estadosPermitidos = [1, 2, 3, 4, 8];
        if ($reservaDelConsumo && !in_array($reservaDelConsumo['rela_estadoreserva'], $estadosPermitidos)) {
            return $this->json([
                'success' => false, 
                'message' => 'No se puede cancelar el consumo porque la reserva está en estado: ' . $reservaDelConsumo['estadoreserva_descripcion']
            ], 403);
        }
        
        // Solo se pueden cancelar consumos en estado "solicitud pendiente" (1)
        if ($consumo['rela_estadoconsumo'] != 1) {
            return $this->json([
                'success' => false, 
                'message' => 'Solo se pueden cancelar consumos en estado "Solicitud Pendiente"'
            ], 403);
        }
        
        // Para reservas online, verificar si el consumo ya fue facturado
        if ($reservaDelConsumo && $reservaDelConsumo['reserva_online'] == 1) {
            $database = \App\Core\Database::getInstance();
            $sqlFactura = "SELECT factura_fechahora FROM factura WHERE rela_reserva = ? ORDER BY factura_fechahora ASC LIMIT 1";
            $stmtFactura = $database->prepare($sqlFactura);
            $stmtFactura->bind_param("i", $consumo['rela_reserva']);
            $stmtFactura->execute();
            $resultFactura = $stmtFactura->get_result()->fetch_assoc();
            $stmtFactura->close();
            
            if ($resultFactura && $consumo['consumo_fechahora'] <= $resultFactura['factura_fechahora']) {
                return $this->json([
                    'success' => false, 
                    'message' => 'No se puede cancelar este consumo porque ya fue facturado y pagado'
                ], 403);
            }
        }
        
        // Cambiar estado a "cancelado por usuario" (6) en lugar de eliminar
        if ($this->consumoModel->update($id, ['rela_estadoconsumo' => 6])) {
            return $this->json(['success' => true, 'message' => 'Consumo cancelado exitosamente']);
        } else {
            return $this->json(['success' => false, 'message' => 'Error al cancelar el consumo'], 500);
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
