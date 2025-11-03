<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Producto
 */
class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';

    /**
     * Obtener productos con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['producto_nombre'])) {
            $where .= " AND p.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto_nombre'] . '%';
        }
        
        if (!empty($filters['rela_categoria'])) {
            $where .= " AND p.rela_categoria = ?";
            $params[] = (int) $filters['rela_categoria'];
        }
        
        if (!empty($filters['rela_marca'])) {
            $where .= " AND p.rela_marca = ?";
            $params[] = (int) $filters['rela_marca'];
        }
        
        if (isset($filters['rela_estadoproducto']) && $filters['rela_estadoproducto'] !== '') {
            $where .= " AND p.rela_estadoproducto = ?";
            $params[] = (int) $filters['rela_estadoproducto'];
        }
        
        if (!empty($filters['precio_min'])) {
            $where .= " AND p.producto_precio >= ?";
            $params[] = (float) $filters['precio_min'];
        }
        
        if (!empty($filters['precio_max'])) {
            $where .= " AND p.producto_precio <= ?";
            $params[] = (float) $filters['precio_max'];
        }
        
        if (!empty($filters['stock_min'])) {
            $where .= " AND p.producto_stock >= ?";
            $params[] = (int) $filters['stock_min'];
        }
        
        $sql = "SELECT p.*, 
                       c.categoria_descripcion,
                       m.marca_descripcion,
                       ep.estadoproducto_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                WHERE $where
                ORDER BY p.producto_nombre ASC";
        
        return $this->paginateCustomQuery($sql, $where, $params, $page, $perPage);
    }

    /**
     * Paginación personalizada con query complejo
     */
    protected function paginateCustomQuery($sql, $where, $params, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM producto p
                     LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                     LEFT JOIN marca m ON p.rela_marca = m.id_marca
                     LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros paginados
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($paginatedSql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Obtener todos los productos para exportación sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['producto_nombre'])) {
            $where .= " AND p.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto_nombre'] . '%';
        }
        
        if (!empty($filters['rela_categoria'])) {
            $where .= " AND p.rela_categoria = ?";
            $params[] = (int) $filters['rela_categoria'];
        }
        
        if (!empty($filters['rela_marca'])) {
            $where .= " AND p.rela_marca = ?";
            $params[] = (int) $filters['rela_marca'];
        }
        
        if (isset($filters['rela_estadoproducto']) && $filters['rela_estadoproducto'] !== '') {
            $where .= " AND p.rela_estadoproducto = ?";
            $params[] = (int) $filters['rela_estadoproducto'];
        }
        
        if (!empty($filters['precio_min'])) {
            $where .= " AND p.producto_precio >= ?";
            $params[] = (float) $filters['precio_min'];
        }
        
        if (!empty($filters['precio_max'])) {
            $where .= " AND p.producto_precio <= ?";
            $params[] = (float) $filters['precio_max'];
        }
        
        $sql = "SELECT p.*, 
                       c.categoria_descripcion,
                       m.marca_descripcion,
                       ep.estadoproducto_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                WHERE $where
                ORDER BY p.producto_nombre ASC";
        
        // Contar total para estadísticas
        $countSql = "SELECT COUNT(*) as total 
                     FROM producto p
                     LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                     LEFT JOIN marca m ON p.rela_marca = m.id_marca
                     LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Obtener registros sin paginación
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return [
            'data' => $productos,
            'total' => $total
        ];
    }

    /**
     * Obtener productos disponibles (con stock)
     */
    public function getAvailable()
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
     * Actualizar stock
     */
    public function updateStock($productId, $quantity, $operation = 'subtract')
    {
        $producto = $this->find($productId);
        if (!$producto) {
            throw new \Exception("Producto no encontrado");
        }
        
        $newStock = $operation === 'add' ? 
            $producto['producto_stock'] + $quantity : 
            $producto['producto_stock'] - $quantity;
        
        if ($newStock < 0) {
            throw new \Exception("Stock insuficiente");
        }
        
        return $this->update($productId, ['producto_stock' => $newStock]);
    }

    /**
     * Productos con stock bajo
     */
    public function getLowStock($threshold = 10)
    {
        $sql = "SELECT p.*, c.categoria_descripcion, m.marca_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                WHERE p.producto_estado = 1 
                AND p.producto_stock <= ?
                ORDER BY p.producto_stock, p.producto_nombre";
        
        $result = $this->query($sql, [$threshold]);
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Paginación personalizada
     */
    protected function paginateCustom($sql, $countSql, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countResult = $this->db->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $result = $this->db->query($paginatedSql);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage)
        ];
    }

    /**
     * Buscar productos
     */
    public function search($term, $page = 1, $perPage = 10)
    {
        $filters = ['producto_nombre' => $term];
        return $this->getWithDetails($page, $perPage, $filters);
    }

    /**
     * Buscar producto con relaciones
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT p.*, 
                       c.categoria_descripcion,
                       m.marca_descripcion,
                       ep.estadoproducto_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                WHERE p.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener estadísticas del producto
     */
    public function getProductStatistics($productId)
    {
        $stats = [];
        
        // Total de ventas/consumos
        $sql = "SELECT COUNT(*) as total_consumos, 
                       SUM(consumo_cantidad) as cantidad_vendida,
                       SUM(consumo_total) as ingresos_total
                FROM consumo 
                WHERE rela_producto = ? AND consumo_estado = 1";
        
        $result = $this->query($sql, [$productId]);
        $consumos = $result->fetch_assoc();
        $stats['consumos'] = $consumos;
        
        return $stats;
    }

    /**
     * Validar stock disponible
     */
    public function hasStock($productId, $quantity = 1)
    {
        $product = $this->find($productId);
        return $product && $product['producto_stock'] >= $quantity;
    }

    /**
     * Cambiar estado del producto (baja lógica)
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['rela_estadoproducto' => $status]);
    }

    /**
     * Obtener productos activos solamente
     */
    public function getActiveProducts()
    {
        $sql = "SELECT p.*, 
                       c.categoria_descripcion,
                       m.marca_descripcion,
                       ep.estadoproducto_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                LEFT JOIN estadoproducto ep ON p.rela_estadoproducto = ep.id_estadoproducto
                WHERE p.rela_estadoproducto = 1
                ORDER BY p.producto_nombre ASC";
        
        $result = $this->db->query($sql);
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }
}