<?php

namespace App\Models;

use App\Core\Model;

class Pago extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'id_pago';

    /**
     * Crear un registro de pago completo
     * IMPORTANTE: Requiere que la factura ya exista (rela_factura es NOT NULL)
     */
    public function createPago($reservaId, $datosPago)
    {
        // Validar que se proporcione el ID de factura (requerido por BD)
        if (!isset($datosPago['factura_id']) || !$datosPago['factura_id']) {
            throw new \Exception('El ID de factura es obligatorio para registrar el pago');
        }

        $pagoData = [
            'pago_fechahora' => date('Y-m-d H:i:s'),
            'pago_total' => $datosPago['total'],
            'rela_factura' => $datosPago['factura_id'], // CRÍTICO: Requerido por constraint FK
            'rela_metododepago' => $datosPago['metodo_pago_id'],
            'pago_estado' => 1 // Activo/Confirmado
        ];

        return $this->create($pagoData);
    }



    /**
     * Obtener pagos por reserva
     */
    public function getPagosByReserva($reservaId)
    {
        $sql = "SELECT p.*, mp.metododepago_descripcion
                FROM pago p
                LEFT JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
                WHERE p.rela_reserva = ? AND p.pago_estado = 1
                ORDER BY p.pago_fechahora DESC";

        $result = $this->query($sql, [$reservaId]);
        
        $pagos = [];
        while ($row = $result->fetch_assoc()) {
            $pagos[] = $row;
        }

        return $pagos;
    }

    /**
     * Obtener total pagado para una reserva
     */
    public function getTotalPagadoReserva($reservaId)
    {
        $sql = "SELECT COALESCE(SUM(pago_total), 0) as total_pagado 
                FROM pago 
                WHERE rela_reserva = ? AND pago_estado = 1";

        $result = $this->query($sql, [$reservaId]);
        $row = $result->fetch_assoc();

        return (float)($row['total_pagado'] ?? 0);
    }

    /**
     * Verificar si una reserva está completamente pagada
     */
    public function isReservaCompletelyPaid($reservaId, $montoTotal)
    {
        $totalPagado = $this->getTotalPagadoReserva($reservaId);
        return $totalPagado >= $montoTotal;
    }


}