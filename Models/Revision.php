<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Revisión
 */
class Revision extends Model
{
    protected $table = 'revision';
    protected $primaryKey = 'id_revision';

    /**
     * Obtener revisiones con detalles de reserva, cabaña e inventario
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['reserva_id'])) {
            $where .= " AND rev.rela_reserva = ?";
            $params[] = (int) $filters['reserva_id'];
        }
        
        if (!empty($filters['cabania_nombre'])) {
            $where .= " AND c.cabania_nombre LIKE ?";
            $params[] = '%' . $filters['cabania_nombre'] . '%';
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND rev.revision_estado = ?";
            $params[] = (int) $filters['estado'];
        }

        // Calcular offset
        $offset = ($page - 1) * $perPage;
        
        // Contar total de registros
        $countSql = "SELECT COUNT(DISTINCT rev.rela_reserva) as total 
                     FROM revision rev
                     INNER JOIN reserva r ON rev.rela_reserva = r.id_reserva
                     INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                     WHERE $where";
        
        $countResult = $this->queryWithParams($countSql, $params);
        $totalRow = $countResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Obtener datos agrupados por reserva
        $dataSql = "SELECT 
                        rev.rela_reserva,
                        r.reserva_fhinicio,
                        r.reserva_fhfin,
                        r.rela_estadoreserva,
                        c.id_cabania,
                        c.cabania_nombre,
                        c.cabania_codigo,
                        SUM(CASE WHEN rev.revision_estado = 1 THEN 1 ELSE 0 END) as total_items,
                        SUM(CASE WHEN rev.revision_estado = 1 THEN rev.revision_costo ELSE 0 END) as total_costo,
                        MAX(rev.revision_estado) as revision_estado
                    FROM revision rev
                    INNER JOIN reserva r ON rev.rela_reserva = r.id_reserva
                    INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                    WHERE $where
                    GROUP BY rev.rela_reserva, r.reserva_fhinicio, r.reserva_fhfin, r.rela_estadoreserva,
                             c.id_cabania, c.cabania_nombre, c.cabania_codigo
                    ORDER BY rev.rela_reserva DESC
                    LIMIT ? OFFSET ?";
        
        // Agregar limit y offset a los parámetros
        $paramsWithLimit = array_merge($params, [$perPage, $offset]);
        
        $dataResult = $this->queryWithParams($dataSql, $paramsWithLimit);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }

        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Obtener todas las revisiones sin paginación (para exportar)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['reserva_id'])) {
            $where .= " AND rev.rela_reserva = ?";
            $params[] = (int) $filters['reserva_id'];
        }
        
        if (!empty($filters['cabania_nombre'])) {
            $where .= " AND c.cabania_nombre LIKE ?";
            $params[] = '%' . $filters['cabania_nombre'] . '%';
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND rev.revision_estado = ?";
            $params[] = (int) $filters['estado'];
        }

        // Query para contar total
        $countSql = "SELECT COUNT(DISTINCT rev.rela_reserva) as total 
                     FROM revision rev
                     INNER JOIN reserva r ON rev.rela_reserva = r.id_reserva
                     INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                     WHERE $where";
        
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT 
                        rev.rela_reserva,
                        r.reserva_fhinicio,
                        r.reserva_fhfin,
                        r.rela_estadoreserva,
                        c.id_cabania,
                        c.cabania_nombre,
                        c.cabania_codigo,
                        SUM(CASE WHEN rev.revision_estado = 1 THEN 1 ELSE 0 END) as total_items,
                        SUM(CASE WHEN rev.revision_estado = 1 THEN rev.revision_costo ELSE 0 END) as total_costo,
                        MAX(rev.revision_estado) as revision_estado
                    FROM revision rev
                    INNER JOIN reserva r ON rev.rela_reserva = r.id_reserva
                    INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                    WHERE $where
                    GROUP BY rev.rela_reserva, r.reserva_fhinicio, r.reserva_fhfin, r.rela_estadoreserva,
                             c.id_cabania, c.cabania_nombre, c.cabania_codigo
                    ORDER BY rev.rela_reserva DESC";
        
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
     * Obtener revisiones por reserva
     */
    public function getByReserva($idReserva)
    {
        $sql = "SELECT 
                    rev.id_revision,
                    rev.rela_inventariocabania,
                    rev.revision_costo,
                    rev.revision_estado,
                    inv.id_inventario,
                    inv.inventario_descripcion,
                    ic.id_inventariocabania,
                    cd.rela_niveldanio as nivel_danio_id,
                    nd.niveldanio_descripcion
                FROM revision rev
                INNER JOIN inventario_cabania ic ON rev.rela_inventariocabania = ic.id_inventariocabania
                INNER JOIN inventario inv ON ic.rela_inventario = inv.id_inventario
                LEFT JOIN costodanio cd ON cd.rela_inventario = inv.id_inventario 
                    AND cd.costodanio_importe = rev.revision_costo 
                    AND cd.costodanio_estado = 1
                LEFT JOIN niveldanio nd ON cd.rela_niveldanio = nd.id_niveldanio
                WHERE rev.rela_reserva = ? AND rev.revision_estado = 1
                ORDER BY inv.inventario_descripcion ASC";

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Calcular el total de costos de revisión para una reserva
     */
    public function getTotalCostoByReserva($idReserva)
    {
        $sql = "SELECT COALESCE(SUM(revision_costo), 0) as total
                FROM revision
                WHERE rela_reserva = ? AND revision_estado = 1";

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    /**
     * Insertar múltiples revisiones en una transacción
     */
    public function insertMultiple($revisiones, $idReserva)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO revision (rela_reserva, rela_inventariocabania, revision_costo, revision_estado) 
                    VALUES (?, ?, ?, 1)";
            
            $stmt = $this->db->prepare($sql);

            foreach ($revisiones as $revision) {
                $stmt->bind_param('iid', 
                    $idReserva,
                    $revision['inventariocabania_id'],
                    $revision['costo']
                );
                $stmt->execute();
            }

            // Actualizar el estado de la reserva DENTRO de la misma transacción
            $this->actualizarEstadoReservaDentroTransaccion($idReserva);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("ERROR en insertMultiple: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar el estado de la reserva según pagos y costos (versión para usar dentro de transacción)
     */
    private function actualizarEstadoReservaDentroTransaccion($idReserva)
    {
        $db = \App\Core\Database::getInstance();
        
        // Obtener total de la reserva (cabaña + servicios + productos)
        $sqlTotal = "SELECT 
                        (SELECT cabania_precio * DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio)
                         FROM cabania c WHERE c.id_cabania = r.rela_cabania) +
                        COALESCE((SELECT SUM(consumo_total) FROM consumo WHERE rela_reserva = ?), 0) as total_reserva
                     FROM reserva r WHERE r.id_reserva = ?";

        $stmt = $db->prepare($sqlTotal);
        $stmt->bind_param('ii', $idReserva, $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalReserva = $result->fetch_assoc()['total_reserva'];

        // Obtener total de costos de revisión
        $totalRevision = $this->getTotalCostoByReserva($idReserva);

        // Obtener total pagado
        $sqlPagado = "SELECT COALESCE(SUM(pago_total), 0) as total_pagado
                      FROM pago WHERE rela_reserva = ?";
        $stmt = $db->prepare($sqlPagado);
        $stmt->bind_param('i', $idReserva);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalPagado = $result->fetch_assoc()['total_pagado'];

        // Calcular total final
        $totalFinal = $totalReserva + $totalRevision;

        // Determinar nuevo estado
        $nuevoEstado = ($totalPagado >= $totalFinal) ? 5 : 4; // 5 = Finalizada, 4 = Pendiente de pago

        // LOG TEMPORAL - REMOVER DESPUÉS
        error_log("=== ACTUALIZAR ESTADO RESERVA (EN TRANSACCIÓN) ===");
        error_log("ID Reserva: " . $idReserva);
        error_log("Total Reserva: " . $totalReserva);
        error_log("Total Revisión: " . $totalRevision);
        error_log("Total Pagado: " . $totalPagado);
        error_log("Total Final: " . $totalFinal);
        error_log("Nuevo Estado: " . $nuevoEstado);
        error_log("===============================================");

        // Actualizar estado de la reserva
        $sqlUpdate = "UPDATE reserva SET rela_estadoreserva = ? WHERE id_reserva = ?";
        $stmt = $db->prepare($sqlUpdate);
        $stmt->bind_param('ii', $nuevoEstado, $idReserva);
        $resultado = $stmt->execute();
        
        error_log("Filas afectadas: " . $stmt->affected_rows);
        error_log("Resultado update: " . ($resultado ? 'true' : 'false'));

        return true;
    }

    /**
     * Actualizar el estado de la reserva según pagos y costos
     */
    public function actualizarEstadoReserva($idReserva)
    {
        try {
            $db = \App\Core\Database::getInstance();
            
            // Obtener total de la reserva (cabaña + servicios + productos)
            $sqlTotal = "SELECT 
                            (SELECT cabania_precio * DATEDIFF(r.reserva_fhfin, r.reserva_fhinicio)
                             FROM cabania c WHERE c.id_cabania = r.rela_cabania) +
                            COALESCE((SELECT SUM(consumo_total) FROM consumo WHERE rela_reserva = ?), 0) as total_reserva
                         FROM reserva r WHERE r.id_reserva = ?";

            $stmt = $db->prepare($sqlTotal);
            $stmt->bind_param('ii', $idReserva, $idReserva);
            $stmt->execute();
            $result = $stmt->get_result();
            $totalReserva = $result->fetch_assoc()['total_reserva'];

            // Obtener total de costos de revisión
            $totalRevision = $this->getTotalCostoByReserva($idReserva);

            // Obtener total pagado
            $sqlPagado = "SELECT COALESCE(SUM(pago_total), 0) as total_pagado
                          FROM pago WHERE rela_reserva = ?";
            $stmt = $db->prepare($sqlPagado);
            $stmt->bind_param('i', $idReserva);
            $stmt->execute();
            $result = $stmt->get_result();
            $totalPagado = $result->fetch_assoc()['total_pagado'];

            // Calcular total final
            $totalFinal = $totalReserva + $totalRevision;

            // Determinar nuevo estado
            $nuevoEstado = ($totalPagado >= $totalFinal) ? 5 : 4; // 5 = Finalizada, 4 = Pendiente de pago

            // LOG TEMPORAL - REMOVER DESPUÉS
            error_log("=== ACTUALIZAR ESTADO RESERVA ===");
            error_log("ID Reserva: " . $idReserva);
            error_log("Total Reserva: " . $totalReserva);
            error_log("Total Revisión: " . $totalRevision);
            error_log("Total Pagado: " . $totalPagado);
            error_log("Total Final: " . $totalFinal);
            error_log("Nuevo Estado: " . $nuevoEstado);
            error_log("================================");

            // Actualizar estado de la reserva
            $sqlUpdate = "UPDATE reserva SET rela_estadoreserva = ? WHERE id_reserva = ?";
            $stmt = $db->prepare($sqlUpdate);
            $stmt->bind_param('ii', $nuevoEstado, $idReserva);
            $resultado = $stmt->execute();
            
            error_log("Filas afectadas: " . $stmt->affected_rows);
            error_log("Resultado update: " . ($resultado ? 'true' : 'false'));

            return true;
        } catch (\Exception $e) {
            error_log("ERROR en actualizarEstadoReserva: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Anular todas las revisiones de una reserva
     */
    public function anularByReserva($idReserva)
    {
        $sql = "UPDATE revision SET revision_estado = 0 WHERE rela_reserva = ?";
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $idReserva);
        return $stmt->execute();
    }

    /**
     * Paginación con parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT * FROM {$this->table} WHERE $where $orderClause LIMIT $limit OFFSET $offset";
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
}
