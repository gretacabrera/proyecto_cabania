<?php

namespace App\Models;

use App\Core\Model;

class Factura extends Model
{
    protected $table = 'factura';
    protected $primaryKey = 'id_factura';

    /**
     * Crear una factura completa con sus detalles en una transacción
     */
    public function createFacturaCompleta($reservaId, $datosFactura, $detalles = [])
    {
        return $this->db->transaction(function() use ($reservaId, $datosFactura, $detalles) {
            try {
                $tipoComprobante = $datosFactura['tipo_comprobante'] ?? 1; // 1 = Factura por defecto
                
                // Generar número de factura único para este tipo de comprobante
                $numeroFactura = $this->generateNumeroFactura($tipoComprobante);
                
                // 1. Crear la factura principal
                $facturaData = [
                    'rela_reserva' => $reservaId,
                    'rela_tipocomprobante' => $tipoComprobante,
                    'factura_nro' => $numeroFactura,
                    'factura_fechahora' => date('Y-m-d H:i:s'),
                    'factura_subtotal' => $datosFactura['subtotal'],
                    'factura_intereses' => $datosFactura['intereses'] ?? 0,
                    'factura_iva' => $datosFactura['iva'] ?? 0,
                    'factura_total' => $datosFactura['total']
                ];

                $facturaId = $this->create($facturaData);
                
                if (!$facturaId) {
                    throw new \Exception("Error al crear la factura");
                }

                // 2. Crear los detalles de la factura
                if (!empty($detalles)) {
                    $this->createFacturaDetalles($facturaId, $detalles);
                }

                return $facturaId;
                
            } catch (\Exception $e) {
                throw $e; // El rollback se maneja automáticamente por el wrapper transaction()
            }
        });
    }

    /**
     * Crear detalles de factura
     */
    private function createFacturaDetalles($facturaId, $detalles)
    {
        $facturaDetalleModel = new FacturaDetalle();
        
        foreach ($detalles as $detalle) {
            $detalleData = [
                'rela_factura' => $facturaId,
                'facturadetalle_descripcion' => $detalle['descripcion'],
                'facturadetalle_preciounitario' => $detalle['precio_unitario'],
                'facturadetalle_cantidad' => $detalle['cantidad'],
                'facturadetalle_total' => $detalle['total']
            ];
            
            $detalleId = $facturaDetalleModel->create($detalleData);
            if (!$detalleId) {
                throw new \Exception("Error creando detalle de factura: " . $detalle['descripcion']);
            }
        }
    }

    /**
     * Obtener factura completa con detalles
     */
    public function getFacturaCompleta($facturaId)
    {
        $sql = "SELECT f.*, tc.tipocomprobante_descripcion,
                       r.id_reserva, r.reserva_fhinicio, r.reserva_fhfin,
                       c.cabania_nombre, c.cabania_codigo,
                       p.persona_nombre, p.persona_apellido,
                       (SELECT ct.contacto_descripcion FROM contacto ct 
                        LEFT JOIN tipocontacto tc ON ct.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' AND ct.rela_persona = p.id_persona 
                        LIMIT 1) as persona_email
                FROM factura f
                LEFT JOIN tipocomprobante tc ON f.rela_tipocomprobante = tc.idtipocomprobante
                LEFT JOIN reserva r ON f.rela_reserva = r.id_reserva
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                WHERE f.id_factura = ?";

        $result = $this->query($sql, [$facturaId]);
        $factura = $result->fetch_assoc();

        if ($factura) {
            // Obtener detalles de la factura
            $detalles = $this->getDetallesFactura($facturaId);
            $factura['detalles'] = $detalles;
        }

        return $factura;
    }

    /**
     * Obtener detalles de una factura
     */
    public function getDetallesFactura($facturaId)
    {
        $sql = "SELECT * FROM facturadetalle WHERE rela_factura = ?";
        $result = $this->query($sql, [$facturaId]);
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }

        return $detalles;
    }

    /**
     * Obtener facturas por reserva
     */
    public function getFacturasByReserva($reservaId)
    {
        $sql = "SELECT f.*, tc.tipocomprobante_descripcion
                FROM factura f
                LEFT JOIN tipocomprobante tc ON f.rela_tipocomprobante = tc.idtipocomprobante
                WHERE f.rela_reserva = ?
                ORDER BY f.factura_fechahora DESC";

        $result = $this->query($sql, [$reservaId]);
        
        $facturas = [];
        while ($row = $result->fetch_assoc()) {
            $facturas[] = $row;
        }

        return $facturas;
    }

    /**
     * Generar número de factura único para un tipo de comprobante
     * Versión simplificada que consulta directamente la tabla factura
     */
    public function generateNumeroFactura($tipoComprobante = 1)
    {
        return $this->db->transaction(function() use ($tipoComprobante) {
            // Obtener prefijo según tipo de comprobante
            $prefijo = $this->getPrefijoPorTipo($tipoComprobante);
            
            // Buscar el último número usado para este tipo y prefijo
            $sql = "SELECT factura_nro 
                    FROM factura 
                    WHERE rela_tipocomprobante = ? 
                    AND factura_nro LIKE ?
                    ORDER BY id_factura DESC 
                    LIMIT 1";
            
            $result = $this->query($sql, [$tipoComprobante, $prefijo . '-%']);
            $ultimaFactura = $result->fetch_assoc();
            
            $siguienteNumero = 1;
            
            if ($ultimaFactura && $ultimaFactura['factura_nro']) {
                // Extraer el número de la última factura
                $partes = explode('-', $ultimaFactura['factura_nro']);
                if (count($partes) >= 2) {
                    $ultimoNumero = intval(end($partes));
                    $siguienteNumero = $ultimoNumero + 1;
                }
            }
            
            // Generar número formateado
            return sprintf("%s-%08d", $prefijo, $siguienteNumero);
        });
    }
    
    /**
     * Obtener prefijo según tipo de comprobante
     */
    private function getPrefijoPorTipo($tipoComprobante)
    {
        $prefijos = [
            1 => 'FACA', // FACTURA A
            2 => 'FACB', // FACTURA B
            3 => 'FACC', // FACTURA C
            4 => 'TICK', // TICKET USUARIO FINAL
        ];
        
        return $prefijos[$tipoComprobante] ?? 'FAC';
    }
    
    /**
     * Generar número de factura legible (método legacy)
     */
    public function generateFacturaNumber($facturaId)
    {
        $year = date('Y');
        $month = date('m');
        return sprintf("FAC-%s%s-%04d", $year, $month, $facturaId);
    }
}