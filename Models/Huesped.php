<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Huesped
 */
class Huesped extends Model
{
    protected $table = 'huesped';
    protected $primaryKey = 'id_huesped';

    /**
     * Obtener huéspedes activos
     */
    public function getActive()
    {
        return $this->findAll("huesped_estado = 1");
    }

    /**
     * Obtener huésped por persona
     */
    public function findByPersona($personaId)
    {
        return $this->findWhere("rela_persona = ?", [$personaId]);
    }

    /**
     * Obtener huéspedes de una reserva específica con detalles
     */
    public function getByReserva($reservaId)
    {
        $sql = "SELECT h.*, 
                       pf.personafisica_dni as persona_dni,
                       pf.personafisica_nombre as persona_nombre, 
                       pf.personafisica_apellido as persona_apellido, 
                       pf.personafisica_fechanac as persona_fechanac, 
                       p.persona_direccion,
                       u.ubicacion_descripcion,
                       (SELECT COUNT(*) FROM usuario WHERE rela_persona = h.rela_persona AND usuario_estado = 1) as tiene_usuario
                FROM huesped_reserva hr
                INNER JOIN {$this->table} h ON hr.rela_huesped = h.{$this->primaryKey}
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                LEFT JOIN ubicacion u ON h.rela_ubicacion = u.id_ubicacion
                WHERE hr.rela_reserva = ?
                AND hr.huespedreserva_estado = 1
                ORDER BY pf.personafisica_apellido ASC, pf.personafisica_nombre ASC";
        
        $result = $this->query($sql, [$reservaId]);
        
        // Verificar si hubo error en la consulta
        if ($result === false) {
            error_log("Error en getByReserva: " . $this->db->error);
            return [];
        }
        
        $huespedes = [];
        while ($row = $result->fetch_assoc()) {
            // Obtener condiciones de salud
            $condiciones = $this->getCondicionesSaludDescripciones($row['id_huesped']);
            $row['condiciones'] = $condiciones;
            $huespedes[] = $row;
        }
        
        return $huespedes;
    }

    /**
     * Obtener descripciones de condiciones de salud del huésped
     */
    private function getCondicionesSaludDescripciones($huespedId)
    {
        $sql = "SELECT cs.condicionsalud_descripcion 
                FROM huesped_condicionsalud hc
                INNER JOIN condicionsalud cs ON hc.rela_condicionsalud = cs.id_condicionsalud
                WHERE hc.rela_huesped = ? AND hc.huespedcondicionsalud_estado = 1
                ORDER BY cs.condicionsalud_descripcion ASC";
        
        $result = $this->query($sql, [$huespedId]);
        
        $condiciones = [];
        while ($row = $result->fetch_assoc()) {
            $condiciones[] = $row['condicionsalud_descripcion'];
        }
        
        return $condiciones;
    }

    /**
     * Obtener huésped con información de persona
     */
    public function findWithPersona($id)
    {
        $sql = "SELECT h.*, 
                       pf.personafisica_dni as persona_dni,
                       pf.personafisica_nombre as persona_nombre, 
                       pf.personafisica_apellido as persona_apellido, 
                       pf.personafisica_fechanac as persona_fechanac, 
                       p.persona_direccion,
                       h.rela_ubicacion
                FROM {$this->table} h
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                WHERE h.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener huéspedes con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['persona_nombre'])) {
            $where .= " AND (pf.personafisica_nombre LIKE ? OR pf.personafisica_apellido LIKE ?)";
            $params[] = '%' . $filters['persona_nombre'] . '%';
            $params[] = '%' . $filters['persona_nombre'] . '%';
        }
        
        if (!empty($filters['persona_dni'])) {
            $where .= " AND pf.personafisica_dni LIKE ?";
            $params[] = '%' . $filters['persona_dni'] . '%';
        }
        
        if (!empty($filters['rela_ubicacion'])) {
            $where .= " AND h.rela_ubicacion = ?";
            $params[] = (int) $filters['rela_ubicacion'];
        }
        
        if (isset($filters['huesped_estado']) && $filters['huesped_estado'] !== '') {
            $where .= " AND h.huesped_estado = ?";
            $params[] = (int) $filters['huesped_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "pf.personafisica_apellido ASC, pf.personafisica_nombre ASC", $params);
    }

    /**
     * Obtener todos los huéspedes con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['persona_nombre'])) {
            $where .= " AND (pf.personafisica_nombre LIKE ? OR pf.personafisica_apellido LIKE ?)";
            $params[] = '%' . $filters['persona_nombre'] . '%';
            $params[] = '%' . $filters['persona_nombre'] . '%';
        }
        
        if (!empty($filters['persona_apellido'])) {
            $where .= " AND pf.personafisica_apellido LIKE ?";
            $params[] = '%' . $filters['persona_apellido'] . '%';
        }
        
        if (!empty($filters['huesped_ubicacion'])) {
            $where .= " AND h.huesped_ubicacion LIKE ?";
            $params[] = '%' . $filters['huesped_ubicacion'] . '%';
        }
        
        if (isset($filters['huesped_estado']) && $filters['huesped_estado'] !== '') {
            $where .= " AND h.huesped_estado = ?";
            $params[] = (int) $filters['huesped_estado'];
        }
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} h
                     INNER JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT h.*, pf.personafisica_nombre as persona_nombre, pf.personafisica_apellido as persona_apellido, 
                           pf.personafisica_fechanac as persona_fechanac, p.persona_direccion, u.ubicacion_descripcion
                    FROM {$this->table} h
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                    LEFT JOIN ubicacion u ON h.rela_ubicacion = u.id_ubicacion
                    WHERE $where
                    ORDER BY pf.personafisica_apellido ASC, pf.personafisica_nombre ASC";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Obtener huéspedes con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} h
                     INNER JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT h.*, pf.personafisica_nombre as persona_nombre, pf.personafisica_apellido as persona_apellido, 
                           pf.personafisica_fechanac as persona_fechanac, p.persona_direccion, u.ubicacion_descripcion
                    FROM {$this->table} h
                    INNER JOIN persona p ON h.rela_persona = p.id_persona
                    LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                    LEFT JOIN ubicacion u ON h.rela_ubicacion = u.id_ubicacion
                    WHERE $where $orderClause LIMIT $limit OFFSET $offset";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Ejecutar query con parámetros preparados
     */
    private function queryWithParams($sql, $params = [])
    {
        return $this->query($sql, $params);
    }

    /**
     * Obtener estadísticas de un huésped específico
     */
    public function getStatistics($huespedId)
    {
        $stats = [
            'reservas_activas' => $this->getReservasActivas($huespedId),
            'reservas_totales' => $this->getReservasTotales($huespedId),
            'gasto_total' => $this->getGastoTotal($huespedId),
            'ultima_reserva' => $this->getUltimaReserva($huespedId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número de reservas activas (confirmadas y futuras)
     */
    private function getReservasActivas($huespedId)
    {
        $fechaActual = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total 
                FROM huesped_reserva hr
                INNER JOIN reserva r ON hr.rela_reserva = r.id_reserva
                WHERE hr.rela_huesped = ? 
                AND hr.huespedreserva_estado = 1
                AND DATE(r.reserva_fhfin) >= ? 
                AND r.rela_estadoreserva IN (1, 2)";
        
        $result = $this->query($sql, [$huespedId, $fechaActual]);
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener número total de reservas (históricas)
     */
    private function getReservasTotales($huespedId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM huesped_reserva hr
                WHERE hr.rela_huesped = ?
                AND hr.huespedreserva_estado = 1";
        
        $result = $this->query($sql, [$huespedId]);
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * Calcular gasto total del huésped
     */
    private function getGastoTotal($huespedId)
    {
        $sql = "SELECT SUM(c.cabania_precio) as total_gasto
                FROM huesped_reserva hr
                INNER JOIN reserva r ON hr.rela_reserva = r.id_reserva
                INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                WHERE hr.rela_huesped = ? 
                AND hr.huespedreserva_estado = 1
                AND r.rela_estadoreserva IN (2, 3)";
        
        $result = $this->query($sql, [$huespedId]);
        $row = $result->fetch_assoc();
        
        return (float)($row['total_gasto'] ?? 0);
    }

    /**
     * Obtener fecha de la última reserva
     */
    private function getUltimaReserva($huespedId)
    {
        $sql = "SELECT MAX(r.reserva_fhinicio) as ultima_reserva
                FROM huesped_reserva hr
                INNER JOIN reserva r ON hr.rela_reserva = r.id_reserva
                WHERE hr.rela_huesped = ?
                AND hr.huespedreserva_estado = 1";
        
        $result = $this->query($sql, [$huespedId]);
        $row = $result->fetch_assoc();
        
        return $row['ultima_reserva'] ?? null;
    }

    /**
     * Obtener condiciones de salud del huésped
     */
    public function getCondicionesSalud($huespedId)
    {
        $sql = "SELECT rela_condicionsalud, huespedcondicionsalud_estado 
                FROM huesped_condicionsalud 
                WHERE rela_huesped = ?";
        
        $result = $this->query($sql, [$huespedId]);
        
        $condiciones = [];
        while ($row = $result->fetch_assoc()) {
            $condiciones[$row['rela_condicionsalud']] = $row['huespedcondicionsalud_estado'];
        }
        
        return $condiciones;
    }

    /**
     * Obtener IDs de condiciones de salud activas del huésped
     */
    public function getCondicionesSaludIds($huespedId)
    {
        $sql = "SELECT rela_condicionsalud 
                FROM huesped_condicionsalud 
                WHERE rela_huesped = ? AND huespedcondicionsalud_estado = 1";
        
        $result = $this->query($sql, [$huespedId]);
        
        $condicionesIds = [];
        while ($row = $result->fetch_assoc()) {
            $condicionesIds[] = (int)$row['rela_condicionsalud'];
        }
        
        return $condicionesIds;
    }

    /**
     * Verificar si el huésped tiene usuario asociado
     */
    public function tieneUsuarioAsociado($huespedId)
    {
        $huesped = $this->find($huespedId);
        if (!$huesped) {
            return false;
        }

        $sql = "SELECT COUNT(*) as total FROM usuario WHERE rela_persona = ? AND usuario_estado = 1";
        $result = $this->query($sql, [$huesped['rela_persona']]);
        $row = $result->fetch_assoc();
        
        return ($row['total'] > 0);
    }

    /**
     * Guardar todas las condiciones de salud del huésped (con estados)
     */
    public function saveCondicionesSalud($huespedId, $todasCondiciones, $condicionesSeleccionadas)
    {
        $sql = "INSERT INTO huesped_condicionsalud (rela_huesped, rela_condicionsalud, huespedcondicionsalud_estado) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($todasCondiciones as $condicion) {
            $idCondicion = $condicion['id_condicionsalud'];
            $estado = in_array($idCondicion, $condicionesSeleccionadas) ? 1 : 0;
            
            $stmt->bind_param('iii', $huespedId, $idCondicion, $estado);
            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }
        }
        
        $stmt->close();
        return true;
    }

    /**
     * Actualizar condiciones de salud del huésped
     */
    public function updateCondicionesSalud($huespedId, $todasCondiciones, $condicionesSeleccionadas)
    {
        // Obtener condiciones existentes
        $sqlExistentes = "SELECT rela_condicionsalud FROM huesped_condicionsalud WHERE rela_huesped = ?";
        $result = $this->query($sqlExistentes, [$huespedId]);
        
        $condicionesExistentes = [];
        while ($row = $result->fetch_assoc()) {
            $condicionesExistentes[] = $row['rela_condicionsalud'];
        }
        
        // Actualizar o insertar condiciones
        foreach ($todasCondiciones as $condicion) {
            $idCondicion = $condicion['id_condicionsalud'];
            $estado = in_array($idCondicion, $condicionesSeleccionadas) ? 1 : 0;
            
            if (in_array($idCondicion, $condicionesExistentes)) {
                // Actualizar existente
                $sqlUpdate = "UPDATE huesped_condicionsalud SET huespedcondicionsalud_estado = ? WHERE rela_huesped = ? AND rela_condicionsalud = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->bind_param('iii', $estado, $huespedId, $idCondicion);
                if (!$stmtUpdate->execute()) {
                    $stmtUpdate->close();
                    return false;
                }
                $stmtUpdate->close();
            } else {
                // Insertar nueva
                $sqlInsert = "INSERT INTO huesped_condicionsalud (rela_huesped, rela_condicionsalud, huespedcondicionsalud_estado) VALUES (?, ?, ?)";
                $stmtInsert = $this->db->prepare($sqlInsert);
                $stmtInsert->bind_param('iii', $huespedId, $idCondicion, $estado);
                if (!$stmtInsert->execute()) {
                    $stmtInsert->close();
                    return false;
                }
                $stmtInsert->close();
            }
        }
        
        return true;
    }

    /**
     * Asociar huésped con reserva
     */
    public function asociarReserva($huespedId, $reservaId)
    {
        $sql = "INSERT INTO huesped_reserva (rela_reserva, rela_huesped, huespedreserva_estado) VALUES (?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $reservaId, $huespedId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Obtener ID de reserva asociada al huésped
     */
    public function getReservaAsociada($huespedId)
    {
        $sql = "SELECT rela_reserva FROM huesped_reserva WHERE rela_huesped = ? ORDER BY id_huespedreserva DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $huespedId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row ? $row['rela_reserva'] : null;
    }

    /**
     * Eliminar reserva asociada al huésped
     */
    public function eliminarReservaAsociada($huespedId)
    {
        $sql = "DELETE FROM huesped_reserva WHERE rela_huesped = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $huespedId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit()
    {
        $this->db->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback()
    {
        $this->db->rollback();
    }

    /**
     * Verificar si una persona ya es huésped
     */
    public function personaIsHuesped($personaId)
    {
        $huesped = $this->findWhere("rela_persona = ?", [$personaId]);
        return $huesped !== false && $huesped !== null;
    }

    /**
     * Buscar huésped por ID de persona física
     */
    public function findByPersonaFisicaId($personaFisicaId)
    {
        $sql = "SELECT h.* FROM huesped h 
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                WHERE p.rela_personafisica = ?
                LIMIT 1";
        $result = $this->query($sql, [$personaFisicaId]);
        return $result->fetch_assoc();
    }

    /**
     * Verificar si huésped ya está asociado a una reserva (activo)
     */
    public function estaAsociadoAReserva($huespedId, $reservaId)
    {
        $sql = "SELECT * FROM huesped_reserva 
                WHERE rela_reserva = ? 
                AND rela_huesped = ?
                AND huespedreserva_estado = 1";
        $result = $this->query($sql, [$reservaId, $huespedId]);
        $asociacion = $result->fetch_assoc();
        return $asociacion !== null && $asociacion !== false;
    }

    /**
     * Actualizar el estado de un huésped en una reserva específica
     * Solo modifica el registro en huesped_reserva, sin afectar huesped ni persona
     * 
     * @param int $huespedId ID del huésped
     * @param int $reservaId ID de la reserva
     * @param int $estado Nuevo estado (0=Eliminado, 1=Activo)
     * @return bool True si se actualizó correctamente
     */
    public function updateEstadoEnReserva($huespedId, $reservaId, $estado)
    {
        $sql = "UPDATE huesped_reserva 
                SET huespedreserva_estado = ? 
                WHERE rela_huesped = ? AND rela_reserva = ?";
        
        error_log("updateEstadoEnReserva - SQL: $sql");
        error_log("updateEstadoEnReserva - Params: estado=$estado, huespedId=$huespedId, reservaId=$reservaId");
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $estado, $huespedId, $reservaId);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("updateEstadoEnReserva - Error SQL: " . $stmt->error);
            return false;
        }
        
        $affectedRows = $stmt->affected_rows;
        error_log("updateEstadoEnReserva - Filas afectadas: $affectedRows");
        
        return $affectedRows > 0;
    }
}