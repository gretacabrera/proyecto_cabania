<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad TipoServicio
 */
class TipoServicio extends Model
{
    protected $table = 'tiposervicio';
    protected $primaryKey = 'id_tiposervicio';

    /**
     * Obtener tipos de servicios activos
     */
    public function getActive()
    {
        return $this->findAll("tiposervicio_estado = 1", "tiposervicio_descripcion");
    }

    /**
     * Obtener tipos de servicios con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['tiposervicio_descripcion'])) {
            $where .= " AND tiposervicio_descripcion LIKE ?";
            $params[] = '%' . $filters['tiposervicio_descripcion'] . '%';
        }
        
        if (isset($filters['tiposervicio_estado']) && $filters['tiposervicio_estado'] !== '') {
            $where .= " AND tiposervicio_estado = ?";
            $params[] = (int) $filters['tiposervicio_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "tiposervicio_descripcion ASC", $params);
    }

    /**
     * Obtener todos los tipos de servicios con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['tiposervicio_descripcion'])) {
            $where .= " AND tiposervicio_descripcion LIKE ?";
            $params[] = '%' . $filters['tiposervicio_descripcion'] . '%';
        }
        
        if (isset($filters['tiposervicio_estado']) && $filters['tiposervicio_estado'] !== '') {
            $where .= " AND tiposervicio_estado = ?";
            $params[] = (int) $filters['tiposervicio_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY tiposervicio_descripcion ASC";
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
     * Obtener tipos de servicios con paginación usando parámetros preparados
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
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \Exception("Error preparando consulta: " . $this->db->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        return $stmt->get_result();
    }

    /**
     * Obtener estadísticas de un tipo de servicio específico
     */
    public function getStatistics($id)
    {
        $stats = [
            'servicios_totales' => $this->getServiciosTotales($id),
            'uso_mes_actual' => $this->getUsoMesActual($id)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de servicios de este tipo
     */
    private function getServiciosTotales($id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM servicio 
                WHERE rela_tiposervicio = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener cantidad de consumos en el mes actual de servicios de este tipo
     */
    private function getUsoMesActual($id)
    {
        $mesActual = date('Y-m');
        $inicioMes = $mesActual . '-01 00:00:00';
        $finMes = date('Y-m-t') . ' 23:59:59';
        
        $sql = "SELECT COUNT(*) as total 
                FROM consumo c
                INNER JOIN servicio s ON c.rela_servicio = s.id_servicio
                INNER JOIN reserva r ON c.rela_reserva = r.id_reserva
                WHERE s.rela_tiposervicio = ?
                AND r.reserva_fhinicio >= ?
                AND r.reserva_fhinicio <= ?
                AND c.consumo_estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $id, $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }
}