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
     * Obtener productos activos con información completa
     */
    public function getActiveWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "p.producto_estado = 1";
        
        if (!empty($filters['categoria'])) {
            $where .= " AND p.rela_categoria = " . (int)$filters['categoria'];
        }
        
        if (!empty($filters['marca'])) {
            $where .= " AND p.rela_marca = " . (int)$filters['marca'];
        }
        
        if (!empty($filters['busqueda'])) {
            $busqueda = $this->db->escape($filters['busqueda']);
            $where .= " AND p.producto_nombre LIKE '%$busqueda%'";
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
                ORDER BY p.producto_nombre";
        
        $countSql = "SELECT COUNT(*) as total 
                     FROM producto p
                     LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                     LEFT JOIN marca m ON p.rela_marca = m.id_marca
                     WHERE $where";
        
        return $this->paginateCustom($sql, $countSql, $page, $perPage);
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
        $filters = ['busqueda' => $term];
        return $this->getActiveWithDetails($page, $perPage, $filters);
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
}