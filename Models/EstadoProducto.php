<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad EstadoProducto
 */
class EstadoProducto extends Model
{
    protected $table = 'estadoproducto';
    protected $primaryKey = 'id_estadoproducto';

    /**
     * Obtener estados activos
     */
    public function getActive()
    {
        return $this->findAll("estadoproducto_estado = 1", "estadoproducto_descripcion");
    }

    /**
     * Buscar estados de productos
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["estadoproducto_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoproducto_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoproducto_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "estadoproducto_descripcion", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoproducto_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoproducto_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        
        $result = $this->query($sql, $params);
        $total = $result->fetch_assoc()['total'];
        
        return ceil($total / $perPage);
    }

    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * Verificar si el estado está en uso
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as count FROM producto WHERE rela_estadoproducto = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $estadoProducto = $this->find($id);
        if (!$estadoProducto) {
            return false;
        }

        $newStatus = $estadoProducto['estadoproducto_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['estadoproducto_estado' => $newStatus]);
    }

    /**
     * Obtener estados con conteo de productos
     */
    public function getWithProductCount()
    {
        $sql = "SELECT ep.*, 
                       COUNT(p.id_producto) as productos_count
                FROM {$this->table} ep
                LEFT JOIN producto p ON ep.{$this->primaryKey} = p.rela_estadoproducto
                GROUP BY ep.{$this->primaryKey}
                ORDER BY ep.estadoproducto_descripcion";
        
        $result = $this->db->query($sql);
        
        $estados = [];
        while ($row = $result->fetch_assoc()) {
            $estados[] = $row;
        }
        
        return $estados;
    }
}