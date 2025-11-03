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
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(c.consumo_fecha) >= '" . $filters['fecha_desde'] . "'";
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(c.consumo_fecha) <= '" . $filters['fecha_hasta'] . "'";
        }
        
        $sql = "SELECT c.*, r.reserva_codigo, p.producto_nombre, pr.persona_nombre, pr.persona_apellido
                FROM {$this->table} c
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona pr ON h.rela_persona = pr.id_persona
                WHERE {$where}
                ORDER BY c.consumo_fecha DESC
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
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(consumo_fecha) >= '" . $filters['fecha_desde'] . "'";
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(consumo_fecha) <= '" . $filters['fecha_hasta'] . "'";
        }
        
        $total = $this->count($where);
        return ceil($total / $perPage);
    }

    /**
     * Obtener consumo con relaciones
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT c.*, r.reserva_codigo, p.producto_nombre, pr.persona_nombre, pr.persona_apellido,
                       cab.cabania_nombre
                FROM {$this->table} c
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona pr ON h.rela_persona = pr.id_persona
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE c.{$this->primaryKey} = {$id}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener reservas activas
     */
    public function getReservasActivas()
    {
        $sql = "SELECT r.id_reserva, r.reserva_codigo, c.cabania_nombre, p.persona_nombre, p.persona_apellido
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
        $sql = "SELECT * FROM productos WHERE producto_estado = 1 ORDER BY producto_nombre";
        $result = $this->db->query($sql);
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Obtener consumos por reserva
     */
    public function getByReserva($reservaId, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, p.producto_nombre
                FROM {$this->table} c
                LEFT JOIN productos p ON c.rela_producto = p.id_producto
                WHERE c.rela_reserva = {$reservaId} AND c.consumo_estado = 1
                ORDER BY c.consumo_fecha DESC
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
                LEFT JOIN productos p ON c.rela_producto = p.id_producto
                WHERE c.rela_reserva = {$reservaId} 
                AND c.consumo_estado = 1 
                AND (c.consumo_facturado IS NULL OR c.consumo_facturado = 0)
                ORDER BY c.consumo_fecha DESC";
        
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
        $sql = "UPDATE {$this->table} SET consumo_facturado = 1, consumo_fecha_facturacion = NOW() 
                WHERE id_consumo IN ({$ids})";
        
        return $this->db->query($sql);
    }

    /**
     * Obtener información de producto
     */
    public function getProducto($productoId)
    {
        $sql = "SELECT * FROM productos WHERE id_producto = {$productoId}";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener resumen de consumos por período
     */
    public function getResumenConsumos($fechaDesde, $fechaHasta)
    {
        $sql = "SELECT 
                    COUNT(*) as total_consumos,
                    SUM(consumo_subtotal) as total_monto,
                    AVG(consumo_subtotal) as promedio_consumo
                FROM {$this->table}
                WHERE consumo_estado = 1
                AND DATE(consumo_fecha) BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener detalle de consumos por período
     */
    public function getDetalleConsumos($fechaDesde, $fechaHasta)
    {
        $sql = "SELECT c.*, p.producto_nombre, r.reserva_codigo
                FROM {$this->table} c
                LEFT JOIN productos p ON c.rela_producto = p.id_producto
                LEFT JOIN reservas r ON c.rela_reserva = r.id_reserva
                WHERE c.consumo_estado = 1
                AND DATE(c.consumo_fecha) BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'
                ORDER BY c.consumo_fecha DESC";
        
        $result = $this->db->query($sql);
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener consumos por producto en período
     */
    public function getConsumosPorProducto($fechaDesde, $fechaHasta)
    {
        $sql = "SELECT p.producto_nombre, 
                       SUM(c.consumo_cantidad) as cantidad_total,
                       SUM(c.consumo_subtotal) as monto_total,
                       COUNT(c.id_consumo) as veces_pedido
                FROM {$this->table} c
                LEFT JOIN productos p ON c.rela_producto = p.id_producto
                WHERE c.consumo_estado = 1
                AND DATE(c.consumo_fecha) BETWEEN '{$fechaDesde}' AND '{$fechaHasta}'
                GROUP BY c.rela_producto
                ORDER BY monto_total DESC";
        
        $result = $this->db->query($sql);
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }
}