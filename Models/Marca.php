<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Marca
 */
class Marca extends Model
{
    protected $table = 'marca';
    protected $primaryKey = 'id_marca';

    /**
     * Obtener marcas activas
     */
    public function getActive()
    {
        return $this->findAll("marca_estado = 1", "marca_descripcion");
    }

    /**
     * Obtener marcas con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros (usar alias 'm' para la tabla marca)
        if (!empty($filters['marca_descripcion'])) {
            $where .= " AND m.marca_descripcion LIKE ?";
            $params[] = '%' . $filters['marca_descripcion'] . '%';
        }
        
        if (isset($filters['marca_estado']) && $filters['marca_estado'] !== '') {
            $where .= " AND m.marca_estado = ?";
            $params[] = (int) $filters['marca_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "m.marca_descripcion ASC", $params);
    }

    /**
     * Obtener todas las marcas con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['marca_descripcion'])) {
            $where .= " AND m.marca_descripcion LIKE ?";
            $params[] = '%' . $filters['marca_descripcion'] . '%';
        }
        
        if (isset($filters['marca_estado']) && $filters['marca_estado'] !== '') {
            $where .= " AND m.marca_estado = ?";
            $params[] = (int) $filters['marca_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} m WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros CON conteo de productos
        $dataSql = "SELECT m.*, 
                    COALESCE(COUNT(p.id_producto), 0) as productos_count
                    FROM {$this->table} m
                    LEFT JOIN producto p ON p.rela_marca = m.id_marca
                    WHERE $where
                    GROUP BY m.id_marca
                    ORDER BY m.marca_descripcion ASC";
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
     * Obtener marcas con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} m WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros CON conteo de productos
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT m.*, 
                    COALESCE(COUNT(p.id_producto), 0) as productos_count
                    FROM {$this->table} m
                    LEFT JOIN producto p ON p.rela_marca = m.id_marca
                    WHERE $where
                    GROUP BY m.id_marca
                    $orderClause 
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
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \Exception("Error preparando consulta: " . $this->db->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        return $stmt->get_result();
    }

    /**
     * Verificar si la marca está siendo utilizada por productos
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as total FROM producto WHERE rela_marca = ?";
        $result = $this->query($sql, [$id]);
        
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'] > 0;
        }
        
        return false;
    }

    /**
     * Obtener productos asociados a una marca
     */
    public function getProductos($id, $limit = 10)
    {
        $sql = "SELECT p.*, c.categoria_descripcion, ep.estadoproducto_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                WHERE p.rela_marca = ?
                ORDER BY p.producto_descripcion ASC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Obtener cantidad de productos de una marca
     */
    public function getProductosCount($id)
    {
        $sql = "SELECT COUNT(*) as total FROM producto WHERE rela_marca = ?";
        $result = $this->query($sql, [$id]);
        
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        
        return 0;
    }

    /**
     * Obtener estadísticas de una marca específica
     */
    public function getStatistics($id)
    {
        $stats = [
            'productos_totales' => $this->getProductosTotales($id),
            'productos_activos' => $this->getProductosActivos($id),
            'productos_sin_stock' => $this->getProductosSinStock($id),
            'valor_inventario' => $this->getValorInventario($id)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de productos de la marca
     */
    private function getProductosTotales($id)
    {
        $sql = "SELECT COUNT(*) as total FROM producto WHERE rela_marca = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número de productos activos de la marca
     */
    private function getProductosActivos($id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM producto p
                JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                WHERE p.rela_marca = ? AND ep.estadoproducto_estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número de productos sin stock de la marca
     */
    private function getProductosSinStock($id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM producto 
                WHERE rela_marca = ? AND producto_stock = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Calcular valor total del inventario de la marca
     */
    private function getValorInventario($id)
    {
        $sql = "SELECT SUM(producto_precio * producto_stock) as valor_total
                FROM producto 
                WHERE rela_marca = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (float)($row['valor_total'] ?? 0);
    }
}