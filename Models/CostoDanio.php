<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad CostoDanio
 */
class CostoDanio extends Model
{
    protected $table = 'costodanio';
    protected $primaryKey = 'id_costodanio';

    /**
     * Obtener costos activos
     */
    public function getActive()
    {
        return $this->findAll("costodanio_estado = 1", "costodanio_importe ASC");
    }

    /**
     * Obtener costos por daño con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['inventario'])) {
            $where .= " AND i.inventario_descripcion LIKE ?";
            $params[] = '%' . $filters['inventario'] . '%';
        }
        
        if (!empty($filters['niveldanio'])) {
            $where .= " AND n.niveldanio_descripcion LIKE ?";
            $params[] = '%' . $filters['niveldanio'] . '%';
        }
        
        if (!empty($filters['importe_min'])) {
            $where .= " AND cd.costodanio_importe >= ?";
            $params[] = (float) $filters['importe_min'];
        }
        
        if (!empty($filters['importe_max'])) {
            $where .= " AND cd.costodanio_importe <= ?";
            $params[] = (float) $filters['importe_max'];
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND cd.costodanio_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        return $this->paginateWithJoins($page, $perPage, $where, "cd.costodanio_importe ASC", $params);
    }

    /**
     * Obtener todos los costos con detalles para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['inventario'])) {
            $where .= " AND i.inventario_descripcion LIKE ?";
            $params[] = '%' . $filters['inventario'] . '%';
        }
        
        if (!empty($filters['niveldanio'])) {
            $where .= " AND n.niveldanio_descripcion LIKE ?";
            $params[] = '%' . $filters['niveldanio'] . '%';
        }
        
        if (!empty($filters['importe_min'])) {
            $where .= " AND cd.costodanio_importe >= ?";
            $params[] = (float) $filters['importe_min'];
        }
        
        if (!empty($filters['importe_max'])) {
            $where .= " AND cd.costodanio_importe <= ?";
            $params[] = (float) $filters['importe_max'];
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND cd.costodanio_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM costodanio cd
                     LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                     LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros con JOINs
        $dataSql = "SELECT cd.*, 
                           i.inventario_descripcion,
                           n.niveldanio_descripcion
                    FROM costodanio cd
                    LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                    LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                    WHERE $where
                    ORDER BY cd.costodanio_importe ASC";
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
     * Buscar costo con detalles de relaciones
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT cd.*, 
                       i.inventario_descripcion,
                       i.inventario_stock,
                       n.niveldanio_descripcion
                FROM costodanio cd
                LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                WHERE cd.id_costodanio = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener costos con paginación usando JOINs
     */
    private function paginateWithJoins($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM costodanio cd
                     LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                     LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros con JOINs
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT cd.*, 
                           i.inventario_descripcion,
                           n.niveldanio_descripcion
                    FROM costodanio cd
                    LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                    LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                    WHERE $where $orderClause
                    LIMIT $limit OFFSET $offset";
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
     * Obtener estadísticas de un costo por daño específico
     */
    public function getStatistics($costoId)
    {
        $costo = $this->findWithDetails($costoId);
        
        if (!$costo) {
            return null;
        }
        
        $stats = [
            'aplicaciones_mes' => $this->getAplicacionesMes($costoId),
            'facturado_mes' => $this->getFacturadoMes($costoId),
            'aplicaciones_anio' => $this->getAplicacionesAnio($costoId),
            'facturado_anio' => $this->getFacturadoAnio($costoId),
            'inventario_stock' => $costo['inventario_stock'] ?? 0
        ];
        
        return $stats;
    }

    /**
     * Obtener número de aplicaciones en el mes actual
     */
    private function getAplicacionesMes($costoId)
    {
        // Obtener datos del costo
        $costo = $this->findWithDetails($costoId);
        if (!$costo) {
            return 0;
        }
        
        // Buscar en facturadetalle las aplicaciones del mes actual
        // La descripción contiene: inventario_descripcion + niveldanio_descripcion
        $mesActual = date('Y-m');
        
        $sql = "SELECT COUNT(DISTINCT fd.id_facturadetalle) as total
                FROM facturadetalle fd
                INNER JOIN factura f ON fd.rela_factura = f.id_factura
                WHERE fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND DATE_FORMAT(f.factura_fechahora, '%Y-%m') = ?";
        
        $result = $this->query($sql, [
            $costo['inventario_descripcion'],
            $costo['niveldanio_descripcion'],
            $mesActual
        ]);
        
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener monto facturado en el mes actual
     */
    private function getFacturadoMes($costoId)
    {
        // Obtener datos del costo
        $costo = $this->findWithDetails($costoId);
        if (!$costo) {
            return 0;
        }
        
        // Buscar en facturadetalle los montos del mes actual
        $mesActual = date('Y-m');
        
        $sql = "SELECT COALESCE(SUM(fd.facturadetalle_total), 0) as total
                FROM facturadetalle fd
                INNER JOIN factura f ON fd.rela_factura = f.id_factura
                WHERE fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND DATE_FORMAT(f.factura_fechahora, '%Y-%m') = ?";
        
        $result = $this->query($sql, [
            $costo['inventario_descripcion'],
            $costo['niveldanio_descripcion'],
            $mesActual
        ]);
        
        $row = $result->fetch_assoc();
        return (float) ($row['total'] ?? 0);
    }

    /**
     * Obtener número de aplicaciones en el año actual
     */
    private function getAplicacionesAnio($costoId)
    {
        // Obtener datos del costo
        $costo = $this->findWithDetails($costoId);
        if (!$costo) {
            return 0;
        }
        
        // Buscar en facturadetalle las aplicaciones del año actual
        $anioActual = date('Y');
        
        $sql = "SELECT COUNT(DISTINCT fd.id_facturadetalle) as total
                FROM facturadetalle fd
                INNER JOIN factura f ON fd.rela_factura = f.id_factura
                WHERE fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND YEAR(f.factura_fechahora) = ?";
        
        $result = $this->query($sql, [
            $costo['inventario_descripcion'],
            $costo['niveldanio_descripcion'],
            $anioActual
        ]);
        
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener monto facturado en el año actual
     */
    private function getFacturadoAnio($costoId)
    {
        // Obtener datos del costo
        $costo = $this->findWithDetails($costoId);
        if (!$costo) {
            return 0;
        }
        
        // Buscar en facturadetalle los montos del año actual
        $anioActual = date('Y');
        
        $sql = "SELECT COALESCE(SUM(fd.facturadetalle_total), 0) as total
                FROM facturadetalle fd
                INNER JOIN factura f ON fd.rela_factura = f.id_factura
                WHERE fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND fd.facturadetalle_descripcion LIKE CONCAT('%', ?, '%')
                AND YEAR(f.factura_fechahora) = ?";
        
        $result = $this->query($sql, [
            $costo['inventario_descripcion'],
            $costo['niveldanio_descripcion'],
            $anioActual
        ]);
        
        $row = $result->fetch_assoc();
        return (float) ($row['total'] ?? 0);
    }

    /**
     * Buscar costos por inventario
     */
    public function findByInventario($inventarioId)
    {
        $sql = "SELECT cd.*, 
                       i.inventario_descripcion,
                       n.niveldanio_descripcion
                FROM costodanio cd
                LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                WHERE cd.rela_inventario = ? 
                AND cd.costodanio_estado = 1
                ORDER BY cd.costodanio_importe ASC";
        
        $result = $this->query($sql, [$inventarioId]);
        
        $costos = [];
        while ($row = $result->fetch_assoc()) {
            $costos[] = $row;
        }
        
        return $costos;
    }

    /**
     * Buscar costos por nivel de daño
     */
    public function findByNivelDanio($nivelDanioId)
    {
        $sql = "SELECT cd.*, 
                       i.inventario_descripcion,
                       n.niveldanio_descripcion
                FROM costodanio cd
                LEFT JOIN inventario i ON cd.rela_inventario = i.id_inventario
                LEFT JOIN niveldanio n ON cd.rela_niveldanio = n.id_niveldanio
                WHERE cd.rela_niveldanio = ? 
                AND cd.costodanio_estado = 1
                ORDER BY cd.costodanio_importe ASC";
        
        $result = $this->query($sql, [$nivelDanioId]);
        
        $costos = [];
        while ($row = $result->fetch_assoc()) {
            $costos[] = $row;
        }
        
        return $costos;
    }
}
