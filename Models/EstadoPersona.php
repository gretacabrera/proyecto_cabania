<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de estados de personas
 */
class EstadoPersona extends Model
{
    protected $table = 'estados_personas';
    protected $primaryKey = 'id_estadopersona';

    /**
     * Buscar estados
     */
    public function search($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->findAll(
            "estadopersona_estado = 1 AND (estadopersona_nombre LIKE '%{$query}%' OR estadopersona_descripcion LIKE '%{$query}%')",
            "estadopersona_nombre ASC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($query = null, $perPage = 10)
    {
        if ($query) {
            $total = $this->count("estadopersona_estado = 1 AND (estadopersona_nombre LIKE '%{$query}%' OR estadopersona_descripcion LIKE '%{$query}%')");
        } else {
            $total = $this->count("estadopersona_estado = 1");
        }
        
        return ceil($total / $perPage);
    }

    /**
     * Obtener estados activos
     */
    public function getActive()
    {
        return $this->findAll("estadopersona_estado = 1", "estadopersona_nombre ASC");
    }

    /**
     * Verificar si está en uso
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as total FROM personas WHERE rela_estadopersona = {$id}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'] > 0;
    }
}