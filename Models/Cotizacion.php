<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Cotizacion
 */
class Cotizacion extends Model
{
    protected $table = 'cotizacion';
    protected $primaryKey = 'id_cotizacion';

    /**
     * Obtener cotizaciones con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['proveedor_nombre'])) {
            $where .= " AND p.persona_denominacion LIKE ?";
            $params[] = '%' . $filters['proveedor_nombre'] . '%';
        }
        
        if (!empty($filters['producto_nombre'])) {
            $where .= " AND pr.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto_nombre'] . '%';
        }
        
        if (!empty($filters['rela_proveedor'])) {
            $where .= " AND c.rela_proveedor = ?";
            $params[] = (int) $filters['rela_proveedor'];
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND c.cotizacion_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "c.cotizacion_fechahora DESC", $params);
    }

    /**
     * Obtener todas las cotizaciones con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['proveedor_nombre'])) {
            $where .= " AND p.persona_denominacion LIKE ?";
            $params[] = '%' . $filters['proveedor_nombre'] . '%';
        }
        
        if (!empty($filters['producto_nombre'])) {
            $where .= " AND pr.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto_nombre'] . '%';
        }
        
        if (!empty($filters['rela_proveedor'])) {
            $where .= " AND c.rela_proveedor = ?";
            $params[] = (int) $filters['rela_proveedor'];
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND c.cotizacion_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} c
                     INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                     INNER JOIN persona p ON pv.rela_persona = p.id_persona
                     INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros
        $dataSql = "SELECT c.*, 
                           p.persona_denominacion as proveedor_nombre,
                           pr.producto_nombre,
                           pr.producto_descripcion
                    FROM {$this->table} c
                    INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                    INNER JOIN persona p ON pv.rela_persona = p.id_persona
                    INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                    WHERE $where 
                    ORDER BY c.cotizacion_fechahora DESC";
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
     * Obtener últimas cotizaciones por cada par Producto-Proveedor
     */
    public function getLastQuotesByProductProvider($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['proveedor_nombre'])) {
            $where .= " AND p.persona_denominacion LIKE ?";
            $params[] = '%' . $filters['proveedor_nombre'] . '%';
        }
        
        if (!empty($filters['producto_nombre'])) {
            $where .= " AND pr.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto_nombre'] . '%';
        }
        
        if (!empty($filters['rela_proveedor'])) {
            $where .= " AND c.rela_proveedor = ?";
            $params[] = (int) $filters['rela_proveedor'];
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND c.cotizacion_estado = ?";
            $params[] = (int) $filters['estado'];
        }
        
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total de combinaciones únicas
        $countSql = "SELECT COUNT(*) as total FROM (
                        SELECT c.rela_producto, c.rela_proveedor
                        FROM {$this->table} c
                        INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                        INNER JOIN persona p ON pv.rela_persona = p.id_persona
                        INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                        WHERE $where
                        GROUP BY c.rela_producto, c.rela_proveedor
                     ) as subquery";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener las últimas cotizaciones por producto-proveedor
        $dataSql = "SELECT c.*, 
                           p.persona_denominacion as proveedor_nombre,
                           pr.producto_nombre,
                           pr.producto_descripcion
                    FROM {$this->table} c
                    INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                    INNER JOIN persona p ON pv.rela_persona = p.id_persona
                    INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                    INNER JOIN (
                        SELECT rela_producto, rela_proveedor, MAX(cotizacion_fechahora) as max_fecha
                        FROM {$this->table}
                        WHERE cotizacion_estado = 1
                        GROUP BY rela_producto, rela_proveedor
                    ) latest ON c.rela_producto = latest.rela_producto 
                              AND c.rela_proveedor = latest.rela_proveedor
                              AND c.cotizacion_fechahora = latest.max_fecha
                    WHERE $where
                    ORDER BY c.cotizacion_fechahora DESC
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
     * Obtener historial de cotizaciones para un par producto-proveedor
     */
    public function getHistoryByProductProvider($productoId, $proveedorId)
    {
        $sql = "SELECT c.*, 
                       p.persona_denominacion as proveedor_nombre,
                       pr.producto_nombre,
                       pr.producto_descripcion
                FROM {$this->table} c
                INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                INNER JOIN persona p ON pv.rela_persona = p.id_persona
                INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                WHERE c.rela_producto = ? AND c.rela_proveedor = ?
                ORDER BY c.cotizacion_fechahora DESC";
        
        $result = $this->query($sql, [$productoId, $proveedorId]);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Obtener historial paginado con filtros
     */
    public function getHistoryByProductProviderPaginated($productoId, $proveedorId, $page = 1, $perPage = 10, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        $where = "c.rela_producto = ? AND c.rela_proveedor = ?";
        $params = [$productoId, $proveedorId];
        
        // Aplicar filtros
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(c.cotizacion_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(c.cotizacion_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (isset($filters['monto_mayor']) && $filters['monto_mayor'] !== '') {
            $where .= " AND c.cotizacion_monto > ?";
            $params[] = (float) $filters['monto_mayor'];
        }
        
        if (isset($filters['monto_menor']) && $filters['monto_menor'] !== '') {
            $where .= " AND c.cotizacion_monto < ?";
            $params[] = (float) $filters['monto_menor'];
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} c
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Obtener datos
        $dataSql = "SELECT c.*, 
                           p.persona_denominacion as proveedor_nombre,
                           pr.producto_nombre,
                           pr.producto_descripcion
                    FROM {$this->table} c
                    INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                    INNER JOIN persona p ON pv.rela_persona = p.id_persona
                    INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                    WHERE $where
                    ORDER BY c.cotizacion_fechahora DESC
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
     * Obtener historial completo para exportación
     */
    public function getHistoryByProductProviderForExport($productoId, $proveedorId, $filters = [])
    {
        $where = "c.rela_producto = ? AND c.rela_proveedor = ?";
        $params = [$productoId, $proveedorId];
        
        // Aplicar filtros
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(c.cotizacion_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(c.cotizacion_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (isset($filters['monto_mayor']) && $filters['monto_mayor'] !== '') {
            $where .= " AND c.cotizacion_monto > ?";
            $params[] = (float) $filters['monto_mayor'];
        }
        
        if (isset($filters['monto_menor']) && $filters['monto_menor'] !== '') {
            $where .= " AND c.cotizacion_monto < ?";
            $params[] = (float) $filters['monto_menor'];
        }
        
        $sql = "SELECT c.*, 
                       p.persona_denominacion as proveedor_nombre,
                       pr.producto_nombre,
                       pr.producto_descripcion
                FROM {$this->table} c
                INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                INNER JOIN persona p ON pv.rela_persona = p.id_persona
                INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                WHERE $where
                ORDER BY c.cotizacion_fechahora DESC";
        
        $result = $this->queryWithParams($sql, $params);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => count($data)
        ];
    }

    /**
     * Obtener cotizaciones con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} c
                     INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                     INNER JOIN persona p ON pv.rela_persona = p.id_persona
                     INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT c.*, 
                           p.persona_denominacion as proveedor_nombre,
                           pr.producto_nombre,
                           pr.producto_descripcion
                    FROM {$this->table} c
                    INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                    INNER JOIN persona p ON pv.rela_persona = p.id_persona
                    INNER JOIN producto pr ON c.rela_producto = pr.id_producto
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
     * Obtener estadísticas de una cotización específica
     */
    public function getStatistics($cotizacionId)
    {
        $cotizacion = $this->find($cotizacionId);
        if (!$cotizacion) {
            return [];
        }

        // Obtener número de cotizaciones del mismo par producto-proveedor
        $sql = "SELECT COUNT(*) as total_cotizaciones,
                       MIN(cotizacion_monto) as precio_minimo,
                       MAX(cotizacion_monto) as precio_maximo,
                       AVG(cotizacion_monto) as precio_promedio
                FROM {$this->table}
                WHERE rela_producto = ? AND rela_proveedor = ?
                AND cotizacion_estado = 1";
        
        $result = $this->query($sql, [$cotizacion['rela_producto'], $cotizacion['rela_proveedor']]);
        $stats = $result->fetch_assoc();
        
        return [
            'total_cotizaciones' => (int)$stats['total_cotizaciones'],
            'precio_minimo' => (float)$stats['precio_minimo'],
            'precio_maximo' => (float)$stats['precio_maximo'],
            'precio_promedio' => (float)$stats['precio_promedio']
        ];
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
