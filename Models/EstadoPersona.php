<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad EstadoPersona
 */
class EstadoPersona extends Model
{
    protected $table = 'estadopersona';
    protected $primaryKey = 'id_estadopersona';

    /**
     * Obtener estados activos
     */
    public function getActive()
    {
        return $this->findAll("estadopersona_estado = 1", "estadopersona_descripcion ASC");
    }

    /**
     * Obtener estados con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['estadopersona_descripcion'])) {
            $where .= " AND estadopersona_descripcion LIKE ?";
            $params[] = '%' . $filters['estadopersona_descripcion'] . '%';
        }
        
        if (isset($filters['estadopersona_estado']) && $filters['estadopersona_estado'] !== '') {
            $where .= " AND estadopersona_estado = ?";
            $params[] = (int) $filters['estadopersona_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "estadopersona_descripcion ASC", $params);
    }

    /**
     * Obtener todos los estados con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['estadopersona_descripcion'])) {
            $where .= " AND estadopersona_descripcion LIKE ?";
            $params[] = '%' . $filters['estadopersona_descripcion'] . '%';
        }
        
        if (isset($filters['estadopersona_estado']) && $filters['estadopersona_estado'] !== '') {
            $where .= " AND estadopersona_estado = ?";
            $params[] = (int) $filters['estadopersona_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY estadopersona_descripcion ASC";
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
     * Obtener estados con paginación usando parámetros preparados
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
     * Obtener estadísticas de un estado específico
     */
    public function getStatistics($estadoId)
    {
        $stats = [
            'personas_asociadas' => $this->getPersonasAsociadas($estadoId),
            'total_personas_sistema' => $this->getTotalPersonasSistema()
        ];
        
        return $stats;
    }

    /**
     * Obtener número de personas asociadas a este estado
     */
    private function getPersonasAsociadas($estadoId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM persona 
                WHERE rela_estadopersona = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $estadoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número total de personas en el sistema
     */
    private function getTotalPersonasSistema()
    {
        $sql = "SELECT COUNT(*) as total FROM persona";
        
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }
}