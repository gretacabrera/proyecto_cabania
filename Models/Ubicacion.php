<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Ubicacion
 */
class Ubicacion extends Model
{
    protected $table = 'ubicacion';
    protected $primaryKey = 'id_ubicacion';

    /**
     * Obtener ubicaciones activas
     */
    public function getAllActive()
    {
        return $this->findAll("ubicacion_estado = 1", "ubicacion_descripcion ASC");
    }

    /**
     * Obtener todas las ubicaciones
     */
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY ubicacion_descripcion ASC";
        $result = $this->query($sql);
        
        $ubicaciones = [];
        while ($row = $result->fetch_assoc()) {
            $ubicaciones[] = $row;
        }
        
        return $ubicaciones;
    }

    /**
     * Obtener ubicaciones con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['ubicacion_descripcion'])) {
            $where .= " AND u.ubicacion_descripcion LIKE ?";
            $params[] = '%' . $filters['ubicacion_descripcion'] . '%';
        }
        
        if (isset($filters['ubicacion_estado']) && $filters['ubicacion_estado'] !== '') {
            $where .= " AND u.ubicacion_estado = ?";
            $params[] = (int) $filters['ubicacion_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "ubicacion_descripcion ASC", $params);
    }

    /**
     * Obtener todas las ubicaciones con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['ubicacion_descripcion'])) {
            $where .= " AND u.ubicacion_descripcion LIKE ?";
            $params[] = '%' . $filters['ubicacion_descripcion'] . '%';
        }
        
        if (isset($filters['ubicacion_estado']) && $filters['ubicacion_estado'] !== '') {
            $where .= " AND u.ubicacion_estado = ?";
            $params[] = (int) $filters['ubicacion_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} u WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros con conteo de cabañas
        $dataSql = "SELECT u.*, 
                    (SELECT COUNT(*) FROM cabania c WHERE c.rela_ubicacion = u.id_ubicacion) as total_cabanias
                    FROM {$this->table} u
                    WHERE $where ORDER BY ubicacion_descripcion ASC";
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
     * Obtener ubicaciones con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} u WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros con conteo de cabañas
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT u.*, 
                    (SELECT COUNT(*) FROM cabania c WHERE c.rela_ubicacion = u.id_ubicacion) as total_cabanias
                    FROM {$this->table} u
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
     * Obtener estadísticas de una ubicación específica
     */
    public function getStatistics($ubicacionId)
    {
        $stats = [
            'total_cabanias' => $this->getTotalCabanias($ubicacionId),
            'total_huespedes' => $this->getTotalHuespedes($ubicacionId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de cabañas en esta ubicación
     */
    private function getTotalCabanias($ubicacionId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM cabania 
                WHERE rela_ubicacion = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $ubicacionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener cantidad total de huéspedes actualmente alojados en esta ubicación
     */
    private function getTotalHuespedes($ubicacionId)
    {
        $sql = "SELECT COUNT(*) as total
                FROM huesped h
                WHERE h.rela_ubicacion = ?
                AND h.huesped_estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $ubicacionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)($row['total'] ?? 0);
    }
}
