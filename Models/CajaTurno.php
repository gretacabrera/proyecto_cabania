<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad CajaTurno
 */
class CajaTurno extends Model
{
    protected $table = 'cajaturno';
    protected $primaryKey = 'id_cajaturno';

    /**
     * Obtener la caja asignada a un usuario
     */
    public function getCajaByUsuario($usuarioId)
    {
        $sql = "SELECT c.*, u.usuario_nombre, p.persona_denominacion
                FROM caja c
                INNER JOIN usuario u ON c.rela_usuario = u.id_usuario
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                WHERE c.rela_usuario = ? AND c.caja_estado = 1";
        
        $result = $this->query($sql, [$usuarioId]);
        return $result->fetch_assoc();
    }

    /**
     * Verificar si hay un turno abierto para una caja
     */
    public function getTurnoAbierto($cajaId)
    {
        $sql = "SELECT ct.*, 
                ua.usuario_nombre as apertura_nombre, 
                pa.persona_denominacion as apertura_denominacion
                FROM cajaturno ct
                LEFT JOIN usuario ua ON ct.rela_usuarioapertura = ua.id_usuario
                LEFT JOIN persona pa ON ua.rela_persona = pa.id_persona
                WHERE ct.rela_caja = ? 
                AND ct.cajaturno_fhcierre IS NULL
                ORDER BY ct.cajaturno_fhapertura DESC
                LIMIT 1";
        
        $result = $this->query($sql, [$cajaId]);
        return $result->fetch_assoc();
    }

    /**
     * Crear apertura de caja
     */
    public function abrirCaja($cajaId, $usuarioId, $contadoInicial, $denominaciones)
    {
        try {
            $this->db->beginTransaction();

            // Insertar el turno de caja - campos de cierre quedan en NULL
            $sql = "INSERT INTO cajaturno 
                    (rela_caja, cajaturno_fhapertura, rela_usuarioapertura, cajaturno_contadoinicial) 
                    VALUES (?, NOW(), ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iid', $cajaId, $usuarioId, $contadoInicial);
            
            if (!$stmt->execute()) {
                throw new \Exception('Error al crear el turno de caja');
            }

            $turnoId = $stmt->insert_id;
            $stmt->close();

            // Guardar el detalle de denominaciones en una tabla auxiliar (si existe)
            // Por ahora solo guardamos el total en cajaturno_contadoinicial

            $this->db->commit();
            return $turnoId;

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error en abrirCaja: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas del turno actual
     */
    public function getEstadisticasTurno($turnoId)
    {
        $stats = [
            'total_movimientos' => $this->getTotalMovimientos($turnoId),
            'total_ingresos' => $this->getTotalIngresos($turnoId),
            'total_salidas' => $this->getTotalSalidas($turnoId),
            'saldo_teorico' => 0
        ];

        // Calcular saldo teórico
        $turno = $this->find($turnoId);
        if ($turno) {
            $stats['saldo_teorico'] = $turno['cajaturno_contadoinicial'] + 
                                      $stats['total_ingresos'] - 
                                      $stats['total_salidas'];
        }

        return $stats;
    }

    /**
     * Obtener total de movimientos del turno
     */
    private function getTotalMovimientos($turnoId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM cajamovimiento 
                WHERE rela_cajaturno = ? AND cajamovimiento_estado = 1";
        
        $result = $this->query($sql, [$turnoId]);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Obtener total de ingresos del turno
     */
    private function getTotalIngresos($turnoId)
    {
        $sql = "SELECT COALESCE(SUM(cajamovimiento_monto), 0) as total 
                FROM cajamovimiento 
                WHERE rela_cajaturno = ? 
                AND cajamovimiento_tipo = 'I' 
                AND cajamovimiento_estado = 1";
        
        $result = $this->query($sql, [$turnoId]);
        $row = $result->fetch_assoc();
        return (float)$row['total'];
    }

    /**
     * Obtener total de salidas del turno
     */
    private function getTotalSalidas($turnoId)
    {
        $sql = "SELECT COALESCE(SUM(cajamovimiento_monto), 0) as total 
                FROM cajamovimiento 
                WHERE rela_cajaturno = ? 
                AND cajamovimiento_tipo = 'E' 
                AND cajamovimiento_estado = 1";
        
        $result = $this->query($sql, [$turnoId]);
        $row = $result->fetch_assoc();
        return (float)$row['total'];
    }

    /**
     * Obtener últimos movimientos del turno
     */
    public function getUltimosMovimientos($turnoId, $limit = 10)
    {
        $sql = "SELECT * FROM cajamovimiento 
                WHERE rela_cajaturno = ? AND cajamovimiento_estado = 1
                ORDER BY cajamovimiento_fechahora DESC
                LIMIT ?";
        
        $result = $this->query($sql, [$turnoId, $limit]);
        
        $movimientos = [];
        while ($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
        }
        
        return $movimientos;
    }

    /**
     * Obtener historial de turnos de una caja
     */
    public function getHistorialTurnos($cajaId, $limit = 20)
    {
        $sql = "SELECT ct.*, 
                ua.usuario_nombre as apertura_nombre, 
                pa.persona_denominacion as apertura_denominacion,
                uc.usuario_nombre as cierre_nombre,
                pc.persona_denominacion as cierre_denominacion
                FROM cajaturno ct
                LEFT JOIN usuario ua ON ct.rela_usuarioapertura = ua.id_usuario
                LEFT JOIN persona pa ON ua.rela_persona = pa.id_persona
                LEFT JOIN usuario uc ON ct.rela_usuariocierre = uc.id_usuario
                LEFT JOIN persona pc ON uc.rela_persona = pc.id_persona
                WHERE ct.rela_caja = ?
                ORDER BY ct.cajaturno_fhapertura DESC
                LIMIT ?";
        
        $result = $this->query($sql, [$cajaId, $limit]);
        
        $turnos = [];
        while ($row = $result->fetch_assoc()) {
            $turnos[] = $row;
        }
        
        return $turnos;
    }
}
