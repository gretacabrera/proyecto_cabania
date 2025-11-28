<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Caja
 */
class Caja extends Model
{
    protected $table = 'caja';
    protected $primaryKey = 'id_caja';

    /**
     * Obtener cajas activas
     */
    public function getActive()
    {
        return $this->findAll("caja_estado = 1", "caja_descripcion");
    }

    /**
     * Obtener todas las cajas activas
     */
    public function getAllActive()
    {
        return $this->findAll("caja_estado = 1", "caja_descripcion ASC");
    }

    /**
     * Obtener caja con detalles (incluye usuario)
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT c.*, u.usuario_nombre
                FROM {$this->table} c
                LEFT JOIN usuario u ON c.rela_usuario = u.id_usuario
                WHERE c.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener cajas con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['caja_descripcion'])) {
            $where .= " AND c.caja_descripcion LIKE ?";
            $params[] = '%' . $filters['caja_descripcion'] . '%';
        }
        
        if (!empty($filters['rela_usuario'])) {
            $where .= " AND c.rela_usuario = ?";
            $params[] = (int) $filters['rela_usuario'];
        }
        
        if (isset($filters['caja_estado']) && $filters['caja_estado'] !== '') {
            $where .= " AND c.caja_estado = ?";
            $params[] = (int) $filters['caja_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "c.caja_descripcion ASC", $params);
    }

    /**
     * Obtener todas las cajas con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['caja_descripcion'])) {
            $where .= " AND c.caja_descripcion LIKE ?";
            $params[] = '%' . $filters['caja_descripcion'] . '%';
        }
        
        if (!empty($filters['rela_usuario'])) {
            $where .= " AND c.rela_usuario = ?";
            $params[] = (int) $filters['rela_usuario'];
        }
        
        if (isset($filters['caja_estado']) && $filters['caja_estado'] !== '') {
            $where .= " AND c.caja_estado = ?";
            $params[] = (int) $filters['caja_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} c
                     LEFT JOIN usuario u ON c.rela_usuario = u.id_usuario
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT c.*, u.usuario_nombre 
                    FROM {$this->table} c
                    LEFT JOIN usuario u ON c.rela_usuario = u.id_usuario
                    WHERE $where ORDER BY c.caja_descripcion ASC";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Obtener cajas con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} c
                     LEFT JOIN usuario u ON c.rela_usuario = u.id_usuario
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT c.*, u.usuario_nombre 
                    FROM {$this->table} c
                    LEFT JOIN usuario u ON c.rela_usuario = u.id_usuario
                    WHERE $where $orderClause LIMIT $limit OFFSET $offset";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
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
     * Ejecutar query con parámetros preparados
     */
    private function queryWithParams($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    /**
     * Obtener estadísticas de una caja específica
     */
    public function getStatistics($cajaId)
    {
        $stats = [
            'turnos_totales' => $this->getTurnosTotales($cajaId),
            'turnos_abiertos' => $this->getTurnosAbiertos($cajaId),
            'movimientos_totales' => $this->getMovimientosTotales($cajaId),
            'monto_total_movimientos' => $this->getMontoTotalMovimientos($cajaId),
            'ultimo_turno' => $this->getUltimoTurno($cajaId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de turnos
     */
    private function getTurnosTotales($cajaId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM cajaturno 
                WHERE rela_caja = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $cajaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número de turnos abiertos (sin cerrar)
     */
    private function getTurnosAbiertos($cajaId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM cajaturno 
                WHERE rela_caja = ? 
                AND cajaturno_fhcierre IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $cajaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número total de movimientos
     */
    private function getMovimientosTotales($cajaId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM cajamovimiento cm
                INNER JOIN cajaturno ct ON cm.rela_cajaturno = ct.id_cajaturno
                WHERE ct.rela_caja = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $cajaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener monto total de movimientos
     */
    private function getMontoTotalMovimientos($cajaId)
    {
        $sql = "SELECT 
                    SUM(CASE WHEN cm.cajamovimiento_tipo = 'I' THEN cm.cajamovimiento_monto ELSE 0 END) as ingresos,
                    SUM(CASE WHEN cm.cajamovimiento_tipo = 'E' THEN cm.cajamovimiento_monto ELSE 0 END) as egresos
                FROM cajamovimiento cm
                INNER JOIN cajaturno ct ON cm.rela_cajaturno = ct.id_cajaturno
                WHERE ct.rela_caja = ? AND cm.cajamovimiento_estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $cajaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $ingresos = (float)($row['ingresos'] ?? 0);
        $egresos = (float)($row['egresos'] ?? 0);
        
        return $ingresos - $egresos;
    }

    /**
     * Obtener información del último turno
     */
    private function getUltimoTurno($cajaId)
    {
        $sql = "SELECT cajaturno_fhapertura, cajaturno_fhcierre
                FROM cajaturno 
                WHERE rela_caja = ?
                ORDER BY cajaturno_fhapertura DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $cajaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            return [
                'apertura' => $row['cajaturno_fhapertura'],
                'cierre' => $row['cajaturno_fhcierre']
            ];
        }
        
        return null;
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit()
    {
        $this->db->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback()
    {
        $this->db->rollback();
    }
}
