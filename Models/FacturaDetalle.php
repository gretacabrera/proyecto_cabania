<?php

namespace App\Models;

use App\Core\Model;

class FacturaDetalle extends Model
{
    protected $table = 'facturadetalle';
    protected $primaryKey = 'id_facturadetalle';

    /**
     * Obtener detalles por factura
     */
    public function getByFactura($facturaId)
    {
        $sql = "SELECT * FROM facturadetalle WHERE rela_factura = ? ORDER BY id_facturadetalle";
        $result = $this->query($sql, [$facturaId]);
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }

        return $detalles;
    }
}