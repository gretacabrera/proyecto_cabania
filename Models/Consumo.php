<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de consumos
 */
class Consumo extends Model
{
    protected $table = 'consumo';
    protected $primaryKey = 'id_consumo';

    /**
     * Obtener consumos con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['huesped'])) {
            $where .= " AND (p.persona_nombre LIKE ? OR p.persona_apellido LIKE ?)";
            $searchTerm = '%' . $filters['huesped'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['reserva'])) {
            $where .= " AND c.rela_reserva = ?";
            $params[] = (int) $filters['reserva'];
        }
        
        if (!empty($filters['producto'])) {
            $where .= " AND prod.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto'] . '%';
        }
        
        if (!empty($filters['servicio'])) {
            $where .= " AND serv.servicio_nombre LIKE ?";
            $params[] = '%' . $filters['servicio'] . '%';
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND c.consumo_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        // Query personalizada con JOINs
        $baseSql = "FROM {$this->table} c
                    LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                    LEFT JOIN producto prod ON c.rela_producto = prod.id_producto
                    LEFT JOIN servicio serv ON c.rela_servicio = serv.id_servicio
                    LEFT JOIN categoria cat ON prod.rela_categoria = cat.id_categoria
                    LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                    LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    WHERE {$where}";
        
        return $this->paginateWithCustomQuery($baseSql, $page, $perPage, $params);
    }

    /**
     * Paginación con query personalizada
     */
    private function paginateWithCustomQuery($baseSql, $page = 1, $perPage = 10, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total " . $baseSql;
        $totalResult = $this->query($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $dataSql = "SELECT c.*, 
                           r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                           prod.producto_nombre,
                           serv.servicio_nombre,
                           cat.categoria_descripcion,
                           p.persona_nombre as huesped_nombre, 
                           p.persona_apellido as huesped_apellido
                    " . $baseSql . "
                    ORDER BY c.id_consumo DESC
                    LIMIT $limit OFFSET $offset";
        
        $dataResult = $this->query($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Obtener todos los consumos con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['huesped'])) {
            $where .= " AND (p.persona_nombre LIKE ? OR p.persona_apellido LIKE ?)";
            $searchTerm = '%' . $filters['huesped'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['reserva'])) {
            $where .= " AND c.rela_reserva = ?";
            $params[] = (int) $filters['reserva'];
        }
        
        if (!empty($filters['producto'])) {
            $where .= " AND prod.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto'] . '%';
        }
        
        if (!empty($filters['servicio'])) {
            $where .= " AND serv.servicio_nombre LIKE ?";
            $params[] = '%' . $filters['servicio'] . '%';
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND c.consumo_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        $sql = "SELECT c.*, 
                       r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                       prod.producto_nombre,
                       serv.servicio_nombre,
                       cat.categoria_descripcion,
                       p.persona_nombre as huesped_nombre, 
                       p.persona_apellido as huesped_apellido
                FROM {$this->table} c
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN producto prod ON c.rela_producto = prod.id_producto
                LEFT JOIN servicio serv ON c.rela_servicio = serv.id_servicio
                LEFT JOIN categoria cat ON prod.rela_categoria = cat.id_categoria
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                WHERE {$where}
                ORDER BY c.id_consumo DESC";
        
        $result = $this->query($sql, $params);
        
        $data = [];
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            $total++;
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Buscar consumos con filtros
     */
    public function search($filters, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $where = "c.consumo_estado = 1";
        
        if (!empty($filters['reserva'])) {
            $where .= " AND c.rela_reserva = " . intval($filters['reserva']);
        }
        
        if (!empty($filters['producto'])) {
            $where .= " AND c.rela_producto = " . intval($filters['producto']);
        }
        
        $sql = "SELECT c.*, r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin, p.producto_nombre, pr.persona_nombre, pr.persona_apellido
                FROM {$this->table} c
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona pr ON h.rela_persona = pr.id_persona
                WHERE {$where}
                ORDER BY c.id_consumo DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $result = $this->db->query($sql);
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = "consumo_estado = 1";
        
        if (!empty($filters['reserva'])) {
            $where .= " AND rela_reserva = " . intval($filters['reserva']);
        }
        
        if (!empty($filters['producto'])) {
            $where .= " AND rela_producto = " . intval($filters['producto']);
        }
        
        $total = $this->count($where);
        return ceil($total / $perPage);
    }

    /**
     * Obtener consumo con relaciones
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT c.*, 
                       r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                       p.producto_nombre, 
                       s.servicio_nombre,
                       pr.persona_nombre AS huesped_nombre, 
                       pr.persona_apellido AS huesped_apellido,
                       cab.cabania_nombre
                FROM {$this->table} c
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona pr ON h.rela_persona = pr.id_persona
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE c.{$this->primaryKey} = {$id}
                LIMIT 1";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener reservas activas
     */
    public function getReservasActivas()
    {
        $sql = "SELECT r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                FROM reserva r
                INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                INNER JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                WHERE er.estadoreserva_estado = 1
                ORDER BY r.reserva_fhinicio DESC";
        
        $result = $this->db->query($sql);
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Obtener productos activos
     */
    public function getProductosActivos()
    {
        $sql = "SELECT * FROM producto WHERE rela_estadoproducto = 1 ORDER BY producto_nombre";
        $result = $this->db->query($sql);
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Obtener servicios activos para filtros
     */
    public function getServiciosActivos()
    {
        $sql = "SELECT * FROM servicio WHERE servicio_estado = 1 ORDER BY servicio_nombre";
        $result = $this->db->query($sql);
        
        $servicios = [];
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
        
        return $servicios;
    }

    /**
     * Obtener consumos por reserva
     */
    public function getByReserva($reservaId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, p.producto_nombre
                FROM {$this->table} c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                WHERE c.rela_reserva = {$reservaId} AND c.consumo_estado = 1
                ORDER BY c.id_consumo DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $result = $this->db->query($sql);
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener información de reserva
     */
    public function getReservaInfo($reservaId)
    {
        $sql = "SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                WHERE r.id_reserva = {$reservaId}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener consumos pendientes de facturación por reserva
     */
    public function getPendingByReserva($reservaId)
    {
        $sql = "SELECT c.*, p.producto_nombre
                FROM {$this->table} c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                WHERE c.rela_reserva = {$reservaId} 
                AND c.consumo_estado = 1 
                AND (c.consumo_facturado IS NULL OR c.consumo_facturado = 0)
                ORDER BY c.id_consumo DESC";
        
        $result = $this->db->query($sql);
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Marcar consumos como facturados
     */
    public function marcarComoFacturados($consumosIds)
    {
        if (empty($consumosIds)) {
            return false;
        }
        
        $ids = implode(',', array_map('intval', $consumosIds));
        $sql = "UPDATE {$this->table} SET consumo_facturado = 1 
                WHERE id_consumo IN ({$ids})";
        
        return $this->db->query($sql);
    }

    /**
     * Obtener información de producto
     */
    public function getProducto($productoId)
    {
        $sql = "SELECT * FROM producto WHERE id_producto = {$productoId}";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener resumen de consumos
     */
    public function getResumenConsumos()
    {
        $sql = "SELECT 
                    COUNT(*) as total_consumos,
                    SUM(consumo_total) as total_monto,
                    AVG(consumo_total) as promedio_consumo
                FROM {$this->table}
                WHERE consumo_estado = 1";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener detalle de consumos
     */
    public function getDetalleConsumos()
    {
        $sql = "SELECT c.*, p.producto_nombre, r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin
                FROM {$this->table} c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                WHERE c.consumo_estado = 1
                ORDER BY c.id_consumo DESC";
        
        $result = $this->db->query($sql);
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener consumos por producto
     */
    public function getConsumosPorProducto()
    {
        $sql = "SELECT p.producto_nombre, 
                       SUM(c.consumo_cantidad) as cantidad_total,
                       SUM(c.consumo_total) as monto_total,
                       COUNT(c.id_consumo) as veces_pedido
                FROM {$this->table} c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                WHERE c.consumo_estado = 1
                GROUP BY c.rela_producto
                ORDER BY monto_total DESC";
        
        $result = $this->db->query($sql);
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Crear múltiples consumos en una transacción
     * @param array $consumosData Array de consumos a crear
     * @return array Resultado con success y datos creados
     */
    public function createMultiple($consumosData)
    {
        try {
            $this->db->beginTransaction();
            
            $consumosCreados = [];
            
            foreach ($consumosData as $consumo) {
                // Validar datos requeridos
                if (empty($consumo['rela_reserva']) || (empty($consumo['rela_producto']) && empty($consumo['rela_servicio']))) {
                    throw new \Exception("Datos incompletos para crear consumo");
                }
                
                // Calcular subtotal
                $cantidad = floatval($consumo['consumo_cantidad'] ?? 1);
                $precioUnitario = floatval($consumo['consumo_precio_unitario'] ?? 0);
                $subtotal = $cantidad * $precioUnitario;
                
                $data = [
                    'rela_reserva' => $consumo['rela_reserva'],
                    'consumo_descripcion' => $consumo['consumo_descripcion'] ?? '',
                    'consumo_cantidad' => $cantidad,
                    'consumo_total' => $subtotal,
                    'consumo_estado' => 1
                ];
                
                // Añadir producto o servicio según corresponda
                if (!empty($consumo['rela_producto'])) {
                    $data['rela_producto'] = $consumo['rela_producto'];
                }
                if (!empty($consumo['rela_servicio'])) {
                    $data['rela_servicio'] = $consumo['rela_servicio'];
                }
                
                $consumoId = $this->create($data);
                
                if (!$consumoId) {
                    throw new \Exception("Error al crear consumo");
                }
                
                $consumosCreados[] = $consumoId;
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => count($consumosCreados) . ' consumo(s) registrado(s) exitosamente',
                'ids' => $consumosCreados
            ];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            
            return [
                'success' => false,
                'message' => 'Error al registrar consumos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener consumos por reserva con detalles completos
     */
    public function getConsumosByReservaWithDetails($reservaId)
    {
        $sql = "SELECT c.*, 
                       p.producto_nombre, p.producto_foto, p.producto_precio,
                       s.servicio_descripcion, s.servicio_precio,
                       COALESCE(p.producto_nombre, s.servicio_descripcion) as item_nombre,
                       COALESCE(p.producto_precio, s.servicio_precio) as item_precio
                FROM {$this->table} c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                WHERE c.rela_reserva = ?
                AND c.consumo_estado = 1
                ORDER BY c.id_consumo DESC";
        
        $result = $this->query($sql, [$reservaId]);
        
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener reserva activa por código de cabaña
     */
    public function getReservaActivaByCabania($cabaniaId)
    {
        $sql = "SELECT r.*, c.cabania_nombre, c.cabania_codigo,
                       p.persona_nombre, p.persona_apellido
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                WHERE r.rela_cabania = ?
                AND r.rela_estadoreserva IN (2, 3)
                AND r.reserva_fhinicio <= NOW()
                AND r.reserva_fhfin >= NOW()
                ORDER BY r.reserva_fhinicio DESC
                LIMIT 1";
        
        $result = $this->query($sql, [$cabaniaId]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener productos activos con stock para consumo
     */
    public function getProductosDisponibles()
    {
        $sql = "SELECT p.*, c.categoria_descripcion, m.marca_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                WHERE p.producto_estado = 1
                AND p.producto_stock > 0
                ORDER BY p.producto_nombre";
        
        $result = $this->db->query($sql);
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Obtener servicios activos disponibles
     */
    public function getServiciosDisponibles()
    {
        $sql = "SELECT s.*, ts.tiposervicio_descripcion
                FROM servicio s
                LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.servicio_estado = 1
                ORDER BY s.servicio_descripcion";
        
        $result = $this->db->query($sql);
        
        $servicios = [];
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
        
        return $servicios;
    }

    /**
     * Obtener reservas del usuario autenticado (para módulo público)
     */
    public function getReservasUsuario($userId)
    {
        $sql = "SELECT r.*, c.cabania_nombre, c.cabania_codigo,
                       er.estadoreserva_descripcion
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN usuario u ON p.id_persona = u.rela_persona
                WHERE u.id_usuario = ?
                AND r.rela_estadoreserva IN (1, 2, 3)
                ORDER BY r.reserva_fhinicio DESC";
        
        $result = $this->query($sql, [$userId]);
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Actualizar consumo existente (para edición)
     */
    public function updateConsumo($consumoId, $data)
    {
        // Recalcular subtotal si se cambia cantidad o precio
        if (isset($data['consumo_cantidad']) || isset($data['consumo_precio_unitario'])) {
            $consumo = $this->find($consumoId);
            $cantidad = $data['consumo_cantidad'] ?? $consumo['consumo_cantidad'];
            $precio = $data['consumo_precio_unitario'] ?? $consumo['consumo_precio_unitario'];
            $data['consumo_total'] = floatval($cantidad) * floatval($precio);
        }
        
        return $this->update($consumoId, $data);
    }

    /**
     * Eliminar consumo (baja lógica)
     */
    public function deleteConsumo($consumoId)
    {
        return $this->update($consumoId, ['consumo_estado' => 0]);
    }

    /**
     * Obtener cabaña por código
     */
    public function getCabaniaByCodigo($codigo)
    {
        $sql = "SELECT * FROM cabania WHERE cabania_codigo = ? AND cabania_baja = 0 LIMIT 1";
        $result = $this->query($sql, [$codigo]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener producto por ID
     */
    public function getProductoById($productoId)
    {
        $sql = "SELECT * FROM producto WHERE id_producto = " . intval($productoId) . " LIMIT 1";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener servicio por ID
     */
    public function getServicioById($servicioId)
    {
        $sql = "SELECT * FROM servicio WHERE id_servicio = " . intval($servicioId) . " LIMIT 1";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }
}