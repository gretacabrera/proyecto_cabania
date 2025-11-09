<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Categoria
 */
class Categoria extends Model
{
    protected $table = 'categoria';
    protected $primaryKey = 'id_categoria';

    /**
     * Obtener categorías activas
     */
    public function getActive()
    {
        return $this->findAll("categoria_estado = 1", "categoria_descripcion");
    }

    /**
     * Obtener categorías con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        
        // Aplicar filtros
        if (!empty($filters['categoria_descripcion'])) {
            $escaped = $this->db->escape($filters['categoria_descripcion']);
            $where .= " AND categoria_descripcion LIKE '%{$escaped}%'";
        }
        
        if (isset($filters['categoria_estado']) && $filters['categoria_estado'] !== '') {
            $estado = (int) $filters['categoria_estado'];
            $where .= " AND categoria_estado = {$estado}";
        }
        
        return $this->paginate($page, $perPage, $where, "categoria_descripcion ASC");
    }

    /**
     * Obtener todos los registros para exportación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['categoria_descripcion'])) {
            $escaped = $this->db->escape($filters['categoria_descripcion']);
            $where .= " AND categoria_descripcion LIKE '%{$escaped}%'";
        }
        
        if (isset($filters['categoria_estado']) && $filters['categoria_estado'] !== '') {
            $estado = (int) $filters['categoria_estado'];
            $where .= " AND categoria_estado = {$estado}";
        }
        
        // Contar total
        $total = $this->count($where);
        
        // Obtener TODOS los registros (sin LIMIT)
        $data = $this->findAll($where, "categoria_descripcion ASC");
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Obtener estadísticas completas de una categoría específica
     */
    public function getStatistics($id)
    {
        $id = (int) $id;
        
        // Estadísticas básicas de productos
        $sql = "SELECT 
                    COUNT(p.id_producto) as total_productos,
                    COUNT(CASE WHEN p.rela_estadoproducto IN (1, 2, 3) THEN 1 END) as productos_activos,
                    COUNT(CASE WHEN p.rela_estadoproducto = 4 THEN 1 END) as productos_inactivos,
                    COALESCE(SUM(CASE WHEN p.rela_estadoproducto IN (1, 2, 3) THEN p.producto_stock END), 0) as stock_total
                FROM categoria c
                LEFT JOIN producto p ON p.rela_categoria = c.id_categoria
                WHERE c.id_categoria = {$id}";
        
        $result = $this->db->query($sql);
        $stats = $result->fetch_assoc();
        
        // Obtener total de productos en el sistema para calcular porcentaje
        $sqlTotal = "SELECT COUNT(*) as total_productos_sistema FROM producto WHERE rela_estadoproducto IN (1, 2, 3)";
        $resultTotal = $this->db->query($sqlTotal);
        $totalSistema = $resultTotal->fetch_assoc();
        
        // Calcular porcentaje
        $porcentaje = 0;
        if ($totalSistema['total_productos_sistema'] > 0) {
            $porcentaje = round(($stats['productos_activos'] / $totalSistema['total_productos_sistema']) * 100, 2);
        }
        
        // Obtener ingresos por ventas (basado en facturadetalle)
        // Nota: Asumimos que facturadetalle_descripcion contiene el nombre del producto
        $sqlIngresos = "SELECT 
                            COALESCE(SUM(fd.facturadetalle_total), 0) as ingresos_totales,
                            COALESCE(SUM(fd.facturadetalle_cantidad), 0) as productos_vendidos
                        FROM facturadetalle fd
                        INNER JOIN producto p ON FIND_IN_SET(p.producto_nombre, fd.facturadetalle_descripcion) > 0
                        WHERE p.rela_categoria = {$id}";
        
        $resultIngresos = $this->db->query($sqlIngresos);
        $ingresos = $resultIngresos->fetch_assoc();
        
        return [
            'total_productos' => (int) $stats['total_productos'],
            'productos_activos' => (int) $stats['productos_activos'],
            'productos_inactivos' => (int) $stats['productos_inactivos'],
            'stock_total' => (int) $stats['stock_total'],
            'productos_vendidos' => (int) $ingresos['productos_vendidos'],
            'porcentaje_categoria' => $porcentaje,
            'ingresos_ventas' => (float) $ingresos['ingresos_totales']
        ];
    }
}