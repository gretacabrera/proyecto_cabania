<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Reserva
 */
class Reserva extends Model
{
    protected $table = 'reserva';
    protected $primaryKey = 'id_reserva';

    /**
     * Obtener reservas con información completa
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "r.reserva_estado = 1";
        
        if (!empty($filters['estado'])) {
            $where .= " AND r.rela_estadoreserva = " . (int)$filters['estado'];
        }
        
        if (!empty($filters['cabania'])) {
            $where .= " AND r.rela_cabania = " . (int)$filters['cabania'];
        }
        
        if (!empty($filters['fecha_inicio'])) {
            $where .= " AND r.reserva_fechainicio >= '" . $this->db->escape($filters['fecha_inicio']) . "'";
        }
        
        if (!empty($filters['fecha_fin'])) {
            $where .= " AND r.reserva_fechafin <= '" . $this->db->escape($filters['fecha_fin']) . "'";
        }
        
        if (!empty($filters['persona'])) {
            $persona = $this->db->escape($filters['persona']);
            $where .= " AND (p.persona_nombre LIKE '%$persona%' OR p.persona_apellido LIKE '%$persona%')";
        }
        
        if (!empty($filters['huesped_dni'])) {
            $dni = $this->db->escape($filters['huesped_dni']);
            $where .= " AND pe.persona_dni LIKE '%$dni%'";
        }
        
        if (!empty($filters['huesped_nombre'])) {
            $nombre = $this->db->escape($filters['huesped_nombre']);
            $where .= " AND CONCAT(pe.persona_nombre, ' ', pe.persona_apellido) LIKE '%$nombre%'";
        }
        
        $sql = "SELECT r.*, 
                       c.cabania_nombre, c.cabania_codigo, c.cabania_precio, c.cabania_capacidad,
                       p.persona_nombre, p.persona_apellido, p.persona_email, p.persona_telefono,
                       er.estadoreserva_descripcion, er.estadoreserva_color,
                       mp.metodopago_descripcion,
                       pr.periodo_descripcion, pr.periodo_fechainicio, pr.periodo_fechafin,
                       GROUP_CONCAT(DISTINCT CONCAT(pe.persona_nombre, ' ', pe.persona_apellido) SEPARATOR ', ') as huespedes,
                       GROUP_CONCAT(DISTINCT pe.persona_dni SEPARATOR ', ') as huespedes_dni
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN persona p ON r.rela_persona = p.id_persona
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                LEFT JOIN metodopago mp ON r.rela_metodopago = mp.id_metodopago
                LEFT JOIN periodo pr ON r.rela_periodo = pr.id_periodo
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona pe ON h.rela_persona = pe.id_persona
                WHERE $where
                GROUP BY r.id_reserva
                ORDER BY r.reserva_fechainicio DESC";
        
        $countSql = "SELECT COUNT(DISTINCT r.id_reserva) as total 
                     FROM reserva r
                     LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                     LEFT JOIN persona p ON r.rela_persona = p.id_persona
                     LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                     LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                     LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                     LEFT JOIN persona pe ON h.rela_persona = pe.id_persona
                     WHERE $where";
        
        return $this->paginateCustom($sql, $countSql, $page, $perPage);
    }

    /**
     * Crear nueva reserva
     */
    public function createReservation($data)
    {
        // Validar disponibilidad
        if (!$this->checkAvailability($data['rela_cabania'], $data['reserva_fechainicio'], $data['reserva_fechafin'])) {
            throw new \Exception("La cabaña no está disponible para las fechas seleccionadas");
        }
        
        // Calcular total basado en días y precio de cabaña
        $cabania = (new Cabania())->find($data['rela_cabania']);
        $fechaInicio = new \DateTime($data['reserva_fechainicio']);
        $fechaFin = new \DateTime($data['reserva_fechafin']);
        $dias = $fechaInicio->diff($fechaFin)->days;
        
        $data['reserva_total'] = $dias * $cabania['cabania_precio'];
        $data['reserva_estado'] = 1;
        
        return $this->create($data);
    }

    /**
     * Verificar disponibilidad de cabaña
     */
    public function checkAvailability($cabaniaId, $fechaInicio, $fechaFin, $excludeReservaId = null)
    {
        $sql = "SELECT COUNT(*) as conflictos 
                FROM reserva r 
                WHERE r.rela_cabania = ? 
                AND r.reserva_estado = 1
                AND r.rela_estadoreserva IN (1, 2, 3) 
                AND (
                    (r.reserva_fechainicio <= ? AND r.reserva_fechafin >= ?) OR
                    (r.reserva_fechainicio <= ? AND r.reserva_fechafin >= ?) OR
                    (r.reserva_fechainicio >= ? AND r.reserva_fechafin <= ?)
                )";
        
        $params = [$cabaniaId, $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaInicio, $fechaFin];
        
        if ($excludeReservaId) {
            $sql .= " AND r.id_reserva != ?";
            $params[] = $excludeReservaId;
        }
        
        $result = $this->query($sql, $params);
        $row = $result->fetch_assoc();
        
        return (int)$row['conflictos'] === 0;
    }

    /**
     * Obtener reservas por estado
     */
    public function getByStatus($statusId)
    {
        $sql = "SELECT r.*, 
                       c.cabania_nombre, c.cabania_codigo,
                       p.persona_nombre, p.persona_apellido, p.persona_email
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN persona p ON r.rela_persona = p.id_persona
                WHERE r.rela_estadoreserva = ?
                AND r.reserva_estado = 1
                ORDER BY r.reserva_fechainicio";
        
        $result = $this->query($sql, [$statusId]);
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Obtener consumos de una reserva
     */
    public function getConsumptions($reservaId)
    {
        $sql = "SELECT c.*, 
                       COALESCE(p.producto_nombre, s.servicio_descripcion) as item_nombre,
                       COALESCE(p.producto_precio, s.servicio_precio) as item_precio
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                WHERE c.rela_reserva = ?
                AND c.consumo_estado = 1
                ORDER BY c.id_consumo";
        
        $result = $this->query($sql, [$reservaId]);
        
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Cambiar estado de reserva
     */
    public function changeStatus($reservaId, $newStatusId, $observaciones = '')
    {
        $updateData = [
            'rela_estadoreserva' => $newStatusId
        ];
        
        if ($observaciones) {
            $updateData['reserva_observaciones'] = $observaciones;
        }
        
        return $this->update($reservaId, $updateData);
    }

    /**
     * Obtener cabañas disponibles para fechas específicas
     */
    public function getAvailableCabins($startDate, $endDate)
    {
        $sql = "SELECT c.* 
                FROM cabania c
                WHERE c.cabania_baja = 0
                AND c.id_cabania NOT IN (
                    SELECT DISTINCT r.rela_cabania 
                    FROM reserva r
                    INNER JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                    WHERE er.estadoreserva_descripcion NOT IN ('cancelada', 'finalizada')
                    AND r.reserva_estado = 1
                    AND (
                        (? BETWEEN r.reserva_fechainicio AND r.reserva_fechafin) OR
                        (? BETWEEN r.reserva_fechainicio AND r.reserva_fechafin) OR
                        (r.reserva_fechainicio BETWEEN ? AND ?) OR
                        (r.reserva_fechafin BETWEEN ? AND ?)
                    )
                )
                ORDER BY c.cabania_nombre";
        
        $result = $this->query($sql, [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return $records;
    }
    
    /**
     * Obtener total de consumos de una reserva
     */
    public function getConsumptionsTotal($reservaId)
    {
        $sql = "SELECT COALESCE(SUM(c.consumo_cantidad * COALESCE(p.producto_precio, s.servicio_precio, 0)), 0) as total
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                WHERE c.rela_reserva = ? AND c.consumo_estado = 1";
        
        $result = $this->query($sql, [$reservaId]);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Confirmar una reserva
     */
    public function confirm($id)
    {
        $confirmedStateId = $this->getStateIdByDescription('confirmada');
        if (!$confirmedStateId) {
            return ['success' => false, 'message' => 'Estado "confirmada" no encontrado'];
        }
        
        $result = $this->update($id, ['rela_estadoreserva' => $confirmedStateId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Reserva confirmada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al confirmar la reserva'];
    }
    
    /**
     * Cancelar una reserva
     */
    public function cancel($id)
    {
        $canceledStateId = $this->getStateIdByDescription('cancelada');
        if (!$canceledStateId) {
            return ['success' => false, 'message' => 'Estado "cancelada" no encontrado'];
        }
        
        $result = $this->update($id, ['rela_estadoreserva' => $canceledStateId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Reserva cancelada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al cancelar la reserva'];
    }
    
    /**
     * Obtener ID de estado por descripción
     */
    private function getStateIdByDescription($description)
    {
        $result = $this->query("SELECT id_estadoreserva FROM estadoreserva WHERE estadoreserva_descripcion = ?", [$description]);
        $row = $result->fetch_assoc();
        return $row ? $row['id_estadoreserva'] : null;
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
}