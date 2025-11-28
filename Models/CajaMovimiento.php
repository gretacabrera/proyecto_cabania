<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad CajaMovimiento
 */
class CajaMovimiento extends Model
{
    protected $table = 'cajamovimiento';
    protected $primaryKey = 'id_cajamovimiento';

    /**
     * Obtener movimientos con detalles del turno y caja
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "cm.cajamovimiento_estado = 1";
        $params = [];
        
        // Filtro por fecha
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(cm.cajamovimiento_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(cm.cajamovimiento_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        // Filtro por tipo
        if (!empty($filters['tipo'])) {
            $where .= " AND cm.cajamovimiento_tipo = ?";
            $params[] = $filters['tipo'];
        }
        
        // Filtro por caja
        if (!empty($filters['caja'])) {
            $where .= " AND c.id_caja = ?";
            $params[] = (int)$filters['caja'];
        }
        
        // Filtro por turno
        if (!empty($filters['turno'])) {
            $where .= " AND ct.id_cajaturno = ?";
            $params[] = (int)$filters['turno'];
        }
        
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total
                     FROM cajamovimiento cm
                     INNER JOIN cajaturno ct ON cm.rela_cajaturno = ct.id_cajaturno
                     INNER JOIN caja c ON ct.rela_caja = c.id_caja
                     WHERE {$where}";
        
        $countResult = $this->query($countSql, $params);
        $totalRow = $countResult->fetch_assoc();
        $total = (int)$totalRow['total'];
        
        // Obtener registros
        $sql = "SELECT cm.*, 
                ct.cajaturno_fhapertura,
                ct.cajaturno_contadoinicial,
                ct.cajaturno_fhcierre,
                c.caja_descripcion,
                u.usuario_nombre
                FROM cajamovimiento cm
                INNER JOIN cajaturno ct ON cm.rela_cajaturno = ct.id_cajaturno
                INNER JOIN caja c ON ct.rela_caja = c.id_caja
                LEFT JOIN usuario u ON ct.rela_usuarioapertura = u.id_usuario
                WHERE {$where}
                ORDER BY cm.cajamovimiento_fechahora DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $result = $this->query($sql, $params);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Obtener todos los movimientos para exportación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "cm.cajamovimiento_estado = 1";
        $params = [];
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(cm.cajamovimiento_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(cm.cajamovimiento_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (!empty($filters['tipo'])) {
            $where .= " AND cm.cajamovimiento_tipo = ?";
            $params[] = $filters['tipo'];
        }
        
        if (!empty($filters['caja'])) {
            $where .= " AND c.id_caja = ?";
            $params[] = (int)$filters['caja'];
        }
        
        if (!empty($filters['turno'])) {
            $where .= " AND ct.id_cajaturno = ?";
            $params[] = (int)$filters['turno'];
        }
        
        $sql = "SELECT cm.*, 
                ct.cajaturno_fhapertura,
                ct.cajaturno_contadoinicial,
                ct.cajaturno_fhcierre,
                c.caja_descripcion,
                u.usuario_nombre
                FROM cajamovimiento cm
                INNER JOIN cajaturno ct ON cm.rela_cajaturno = ct.id_cajaturno
                INNER JOIN caja c ON ct.rela_caja = c.id_caja
                LEFT JOIN usuario u ON ct.rela_usuarioapertura = u.id_usuario
                WHERE {$where}
                ORDER BY cm.cajamovimiento_fechahora DESC";
        
        $result = $this->query($sql, $params);
        
        $movimientos = [];
        while ($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
        }
        
        return [
            'data' => $movimientos,
            'total' => count($movimientos)
        ];
    }

    /**
     * Registrar nuevo movimiento
     */
    public function registrarMovimiento($turnoId, $descripcion, $tipo, $monto)
    {
        $sql = "INSERT INTO cajamovimiento 
                (rela_cajaturno, cajamovimiento_fechahora, cajamovimiento_descripcion, cajamovimiento_tipo, cajamovimiento_monto, cajamovimiento_estado)
                VALUES (?, NOW(), ?, ?, ?, 1)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('issd', $turnoId, $descripcion, $tipo, $monto);
        
        if ($stmt->execute()) {
            $movimientoId = $stmt->insert_id;
            $stmt->close();
            return $movimientoId;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Obtener movimientos de un turno específico
     */
    public function getMovimientosByTurno($turnoId, $limit = null)
    {
        $sql = "SELECT * FROM cajamovimiento 
                WHERE rela_cajaturno = ? AND cajamovimiento_estado = 1
                ORDER BY cajamovimiento_fechahora DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $result = $this->query($sql, [$turnoId]);
        
        $movimientos = [];
        while ($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
        }
        
        return $movimientos;
    }

    /**
     * Obtener estadísticas de movimientos
     */
    public function getEstadisticasByFecha($fechaDesde, $fechaHasta)
    {
        $sql = "SELECT 
                COUNT(*) as total_movimientos,
                SUM(CASE WHEN cajamovimiento_tipo = 'I' THEN cajamovimiento_monto ELSE 0 END) as total_ingresos,
                SUM(CASE WHEN cajamovimiento_tipo = 'E' THEN cajamovimiento_monto ELSE 0 END) as total_egresos,
                SUM(CASE WHEN cajamovimiento_tipo = 'I' THEN 1 ELSE 0 END) as cantidad_ingresos,
                SUM(CASE WHEN cajamovimiento_tipo = 'E' THEN 1 ELSE 0 END) as cantidad_egresos
                FROM cajamovimiento
                WHERE DATE(cajamovimiento_fechahora) BETWEEN ? AND ?
                AND cajamovimiento_estado = 1";
        
        $result = $this->query($sql, [$fechaDesde, $fechaHasta]);
        return $result->fetch_assoc();
    }
}
