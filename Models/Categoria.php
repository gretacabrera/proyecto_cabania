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
     * Buscar categorías
     */
    public function search($term, $page = 1, $perPage = 10)
    {
        $term = $this->db->escape($term);
        $where = "categoria_estado = 1 AND categoria_descripcion LIKE '%$term%'";
        return $this->paginate($page, $perPage, $where, "categoria_descripcion");
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($search = '', $perPage = 10)
    {
        if (!empty($search)) {
            $search = $this->db->escape($search);
            $where = "categoria_estado = 1 AND categoria_descripcion LIKE '%$search%'";
        } else {
            $where = "categoria_estado = 1";
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql);
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
}