<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Consumo;

/**
 * Controlador para el módulo Tótem de consumos
 * Sin autenticación, funciona como terminal de pedidos en cabañas
 * Se parametriza con código de cabaña
 */
class TotemConsumosController extends Controller
{
    protected $consumoModel;

    public function __construct()
    {
        parent::__construct();
        $this->consumoModel = new Consumo();
    }

    /**
     * Página principal del tótem (configuración inicial)
     */
    public function index()
    {
        // Verificar si ya hay una cabaña configurada en sesión
        $cabaniaCodigo = $_SESSION['totem_cabania_codigo'] ?? null;
        $cabaniaId = $_SESSION['totem_cabania_id'] ?? null;
        
        if ($cabaniaCodigo && $cabaniaId) {
            // Redirigir al menú de pedidos
            $this->redirect('/totem/menu');
            return;
        }
        
        $data = [
            'title' => 'Configuración Tótem - Cabañas',
            'isTotemArea' => true
        ];

        return $this->render('totem/consumos/config', $data, 'totem');
    }

    /**
     * Configurar cabaña para el tótem
     */
    public function configurar()
    {
        if ($this->isPost()) {
            $codigo = strtoupper(trim($this->post('cabania_codigo')));
            
            if (empty($codigo)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Debe ingresar un código de cabaña'
                ]);
            }
            
            // Buscar cabaña por código
            $cabania = $this->consumoModel->getCabaniaByCodigo($codigo);
            
            if (!$cabania) {
                return $this->json([
                    'success' => false,
                    'message' => 'Código de cabaña no válido'
                ]);
            }
            
            // Verificar que hay una reserva activa para esta cabaña
            $reserva = $this->consumoModel->getReservaActivaByCabania($cabania['id_cabania']);
            
            if (!$reserva) {
                return $this->json([
                    'success' => false,
                    'message' => 'No hay reserva activa para esta cabaña'
                ]);
            }
            
            // Guardar en sesión
            $_SESSION['totem_cabania_codigo'] = $codigo;
            $_SESSION['totem_cabania_id'] = $cabania['id_cabania'];
            $_SESSION['totem_cabania_nombre'] = $cabania['cabania_nombre'];
            $_SESSION['totem_reserva_id'] = $reserva['id_reserva'];
            $_SESSION['totem_reserva_huesped'] = $reserva['persona_nombre'] . ' ' . $reserva['persona_apellido'];
            
            return $this->json([
                'success' => true,
                'message' => 'Tótem configurado correctamente',
                'cabania' => $cabania['cabania_nombre'],
                'redirect' => url('/totem/menu')
            ]);
        }
        
        $this->redirect('/totem');
    }

    /**
     * Menú de productos/servicios para pedidos
     */
    public function menu()
    {
        // Verificar configuración
        if (!isset($_SESSION['totem_cabania_id'])) {
            $this->redirect('/totem', 'Debe configurar el tótem primero', 'error');
            return;
        }
        
        $productos = $this->consumoModel->getProductosDisponibles();
        $servicios = $this->consumoModel->getServiciosDisponibles();
        
        $data = [
            'title' => 'Menú de Pedidos - ' . $_SESSION['totem_cabania_nombre'],
            'productos' => $productos,
            'servicios' => $servicios,
            'cabaniaNombre' => $_SESSION['totem_cabania_nombre'],
            'huespedNombre' => $_SESSION['totem_reserva_huesped'],
            'isTotemArea' => true
        ];

        return $this->render('totem/consumos/menu', $data, 'totem');
    }

    /**
     * Vista para solicitar consumos (versión slider)
     */
    public function solicitar()
    {
        // Verificar configuración
        if (!isset($_SESSION['totem_cabania_id'])) {
            $this->redirect('/totem', 'Debe configurar el tótem primero', 'error');
            return;
        }
        
        $data = [
            'title' => 'Solicitar Consumos - ' . $_SESSION['totem_cabania_nombre'],
            'cabaniaNombre' => $_SESSION['totem_cabania_nombre'],
            'huespedNombre' => $_SESSION['totem_reserva_huesped'],
            'isTotemArea' => true
        ];

        return $this->render('totem/consumos/solicitar-totem', $data, 'totem');
    }

    /**
     * Registrar pedido desde el tótem
     */
    public function pedido()
    {
        if (!$this->isPost()) {
            return $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        // Verificar configuración
        if (!isset($_SESSION['totem_reserva_id'])) {
            return $this->json([
                'success' => false,
                'message' => 'Sesión expirada. Debe reconfigurar el tótem'
            ], 401);
        }
        
        // Leer JSON del body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $reservaId = $_SESSION['totem_reserva_id'];
        $items = $data['items'] ?? [];
        
        if (empty($items)) {
            return $this->json([
                'success' => false,
                'message' => 'Debe agregar al menos un producto al pedido'
            ]);
        }
        
        // Preparar array de consumos
        $consumosData = [];
        
        foreach ($items as $item) {
            $cantidad = floatval($item['cantidad'] ?? 1);
            if ($cantidad <= 0) continue;
            
            $tipo = $item['tipo']; // 'producto' o 'servicio'
            $itemId = intval($item['id']);
            
            if ($tipo === 'producto') {
                $producto = $this->consumoModel->getProducto($itemId);
                if (!$producto) continue;
                
                $consumosData[] = [
                    'rela_reserva' => $reservaId,
                    'rela_producto' => $itemId,
                    'rela_servicio' => null,
                    'consumo_descripcion' => 'Producto: ' . $producto['producto_nombre'],
                    'consumo_cantidad' => $cantidad,
                    'consumo_precio_unitario' => $producto['producto_precio']
                ];
            } elseif ($tipo === 'servicio') {
                $servicio = $this->consumoModel->getServicio($itemId);
                if (!$servicio) continue;
                
                $consumosData[] = [
                    'rela_reserva' => $reservaId,
                    'rela_producto' => null,
                    'rela_servicio' => $itemId,
                    'consumo_descripcion' => 'Servicio: ' . $servicio['servicio_descripcion'],
                    'consumo_cantidad' => $cantidad,
                    'consumo_precio_unitario' => $servicio['servicio_precio']
                ];
            }
        }
        
        if (empty($consumosData)) {
            return $this->json([
                'success' => false,
                'message' => 'No hay items válidos en el pedido'
            ]);
        }
        
        // Crear consumos en transacción
        $result = $this->consumoModel->createMultiple($consumosData);
        
        return $this->json($result);
    }

    /**
     * Ver historial de pedidos de la cabaña
     */
    public function historial()
    {
        // Verificar configuración
        if (!isset($_SESSION['totem_reserva_id'])) {
            $this->redirect('/totem', 'Debe configurar el tótem primero', 'error');
            return;
        }
        
        $reservaId = $_SESSION['totem_reserva_id'];
        $consumos = $this->consumoModel->getConsumosByReservaWithDetails($reservaId);
        
        $totalConsumos = 0;
        foreach ($consumos as $consumo) {
            $totalConsumos += floatval($consumo['consumo_total']);
        }
        
        $data = [
            'title' => 'Historial de Pedidos',
            'consumos' => $consumos,
            'totalConsumos' => $totalConsumos,
            'cabaniaNombre' => $_SESSION['totem_cabania_nombre'],
            'huespedNombre' => $_SESSION['totem_reserva_huesped'],
            'isTotemArea' => true
        ];

        return $this->render('totem/consumos/historial', $data, 'totem');
    }

    /**
     * Limpiar configuración del tótem
     */
    public function reset()
    {
        unset($_SESSION['totem_cabania_codigo']);
        unset($_SESSION['totem_cabania_id']);
        unset($_SESSION['totem_cabania_nombre']);
        unset($_SESSION['totem_reserva_id']);
        unset($_SESSION['totem_reserva_huesped']);
        
        $this->redirect('/totem', 'Configuración del tótem reiniciada', 'success');
    }

    /**
     * API: Obtener categorías disponibles (AJAX)
     */
    public function getCategorias()
    {
        $categorias = $this->consumoModel->getCategoriasActivas();
        
        return $this->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    /**
     * API: Obtener tipos de servicio disponibles (AJAX)
     */
    public function getTiposServicio()
    {
        $tipos = $this->consumoModel->getTiposServicioActivos();
        
        return $this->json([
            'success' => true,
            'data' => $tipos
        ]);
    }

    /**
     * API: Obtener productos por categoría (AJAX)
     */
    public function getProductosPorCategoria($categoriaId)
    {
        $productos = $this->consumoModel->getProductosByCategoria($categoriaId);
        
        return $this->json([
            'success' => true,
            'data' => $productos
        ]);
    }

    /**
     * API: Obtener servicios por tipo (AJAX)
     */
    public function getServiciosPorTipo($tipoId)
    {
        $servicios = $this->consumoModel->getServiciosByTipo($tipoId);
        
        return $this->json([
            'success' => true,
            'data' => $servicios
        ]);
    }

    /**
     * API: Obtener precio de producto (AJAX)
     */
    public function getPrecioProducto($id)
    {
        $producto = $this->consumoModel->getProducto($id);
        
        if ($producto) {
            return $this->json([
                'success' => true,
                'precio' => $producto['producto_precio'],
                'nombre' => $producto['producto_nombre'],
                'stock' => $producto['producto_stock']
            ]);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }
}
