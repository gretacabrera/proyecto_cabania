<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Inventario
 */
class Inventario extends Model
{
    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';

    /**
     * Obtener inventarios activos
     */
    public function getActive()
    {
        return $this->findAll("inventario_estado = 1", "inventario_descripcion");
    }

    /**
     * Obtener inventarios con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['inventario_descripcion'])) {
            $where .= " AND inventario_descripcion LIKE ?";
            $params[] = '%' . $filters['inventario_descripcion'] . '%';
        }
        
        if (!empty($filters['inventario_stock_min'])) {
            $where .= " AND inventario_stock >= ?";
            $params[] = (int) $filters['inventario_stock_min'];
        }
        
        if (isset($filters['inventario_estado']) && $filters['inventario_estado'] !== '') {
            $where .= " AND inventario_estado = ?";
            $params[] = (int) $filters['inventario_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "inventario_descripcion ASC", $params);
    }

    /**
     * Obtener todos los inventarios con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['inventario_descripcion'])) {
            $where .= " AND inventario_descripcion LIKE ?";
            $params[] = '%' . $filters['inventario_descripcion'] . '%';
        }
        
        if (!empty($filters['inventario_stock_min'])) {
            $where .= " AND inventario_stock >= ?";
            $params[] = (int) $filters['inventario_stock_min'];
        }
        
        if (isset($filters['inventario_estado']) && $filters['inventario_estado'] !== '') {
            $where .= " AND inventario_estado = ?";
            $params[] = (int) $filters['inventario_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY inventario_descripcion ASC";
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
     * Obtener inventarios con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT * FROM {$this->table} WHERE $where $orderClause LIMIT $limit OFFSET $offset";
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
     * Este método es un alias del método query() heredado del modelo base
     */
    private function queryWithParams($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    /**
     * Obtener estadísticas de un inventario específico
     */
    public function getStatistics($inventarioId)
    {
        $stats = [
            'usos_totales' => $this->getUsosTotales($inventarioId),
            'cabanias_asignadas' => $this->getCabaniasAsignadas($inventarioId),
            'stock_actual' => $this->getStockActual($inventarioId),
            'ultimo_movimiento' => $this->getUltimoMovimiento($inventarioId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de usos del inventario
     */
    private function getUsosTotales($inventarioId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM inventario_cabania 
                WHERE rela_inventario = ? 
                AND inventariocabania_estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $inventarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número de cabañas asignadas a este inventario
     */
    private function getCabaniasAsignadas($inventarioId)
    {
        $sql = "SELECT COUNT(DISTINCT ic.rela_cabania) as total 
                FROM inventario_cabania ic
                INNER JOIN cabania c ON ic.rela_cabania = c.id_cabania
                WHERE ic.rela_inventario = ? 
                AND ic.inventariocabania_estado = 1
                AND c.cabania_estado IN (1, 2)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $inventarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener stock actual
     */
    private function getStockActual($inventarioId)
    {
        $inventario = $this->find($inventarioId);
        return $inventario ? (int)$inventario['inventario_stock'] : 0;
    }

    /**
     * Obtener fecha del último movimiento (simulado)
     */
    private function getUltimoMovimiento($inventarioId)
    {
        // Como no hay tabla de movimientos, retornamos información de última asignación
        $sql = "SELECT MAX(ic.id_inventariocabania) as ultimo_id
                FROM inventario_cabania ic
                WHERE ic.rela_inventario = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $inventarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['ultimo_id'] ? 'Reciente' : 'Sin movimientos';
    }

    /**
     * Buscar inventarios por stock bajo
     */
    public function findLowStock($threshold = 10)
    {
        return $this->findAll("inventario_stock <= $threshold AND inventario_estado = 1", "inventario_stock ASC");
    }

    /**
     * Verificar si un inventario tiene stock suficiente
     */
    public function hasStock($inventarioId, $cantidad = 1)
    {
        $inventario = $this->find($inventarioId);
        return $inventario && (int)$inventario['inventario_stock'] >= $cantidad;
    }
}
