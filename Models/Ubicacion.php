<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de ubicaciones
 */
class Ubicacion extends Model
{
    protected $table = 'ubicacion';
    protected $primaryKey = 'id_ubicacion';

    /**
     * Obtener todas las ubicaciones activas
     */
    public function getAllActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE ubicacion_estado = 1 ORDER BY ubicacion_descripcion ASC";
        $result = $this->query($sql);
        
        $ubicaciones = [];
        while ($row = $result->fetch_assoc()) {
            $ubicaciones[] = $row;
        }
        
        return $ubicaciones;
    }
}
