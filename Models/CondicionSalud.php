<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad CondicionSalud
 */
class CondicionSalud extends Model
{
    protected $table = 'condicionsalud';
    protected $primaryKey = 'id_condicionsalud';

    /**
     * Obtener condiciones de salud activas
     */
    public function getActive()
    {
        return $this->findAll("condicionsalud_estado = 1", "condicionsalud_descripcion ASC");
    }

    /**
     * Obtener condiciones de salud con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['condicionsalud_descripcion'])) {
            $where .= " AND condicionsalud_descripcion LIKE ?";
            $params[] = '%' . $filters['condicionsalud_descripcion'] . '%';
        }
        
        if (isset($filters['condicionsalud_estado']) && $filters['condicionsalud_estado'] !== '') {
            $where .= " AND condicionsalud_estado = ?";
            $params[] = (int) $filters['condicionsalud_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "condicionsalud_descripcion ASC", $params);
    }

    /**
     * Obtener todas las condiciones para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['condicionsalud_descripcion'])) {
            $where .= " AND condicionsalud_descripcion LIKE ?";
            $params[] = '%' . $filters['condicionsalud_descripcion'] . '%';
        }
        
        if (isset($filters['condicionsalud_estado']) && $filters['condicionsalud_estado'] !== '') {
            $where .= " AND condicionsalud_estado = ?";
            $params[] = (int) $filters['condicionsalud_estado'];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE $where ORDER BY condicionsalud_descripcion ASC";
        $result = $this->queryWithParams($sql, $params);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => count($data)
        ];
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
     * Obtener estadísticas de una condición de salud específica
     */
    public function getStatistics($id)
    {
        $id = (int) $id;
        
        // Estadísticas de huéspedes con esta condición de salud
        $sql = "SELECT 
                    COUNT(hc.id_huespedcondicionsalud) as total_huespedes,
                    COUNT(CASE WHEN hc.huespedcondicionsalud_estado = 1 THEN 1 END) as huespedes_activos,
                    COUNT(DISTINCT hr.rela_reserva) as total_reservas,
                    COUNT(CASE WHEN r.reserva_estado IN (1, 2, 3) THEN 1 END) as reservas_activas
                FROM condicionsalud c
                LEFT JOIN huesped_condicionsalud hc ON hc.rela_condicionsalud = c.id_condicionsalud
                LEFT JOIN huesped_reserva hr ON hr.rela_huesped = hc.rela_huesped
                LEFT JOIN reserva r ON r.id_reserva = hr.rela_reserva
                WHERE c.id_condicionsalud = {$id}";
        
        $result = $this->db->query($sql);
        $stats = $result->fetch_assoc();
        
        // Calcular porcentaje del total de huéspedes con condiciones de salud
        $sqlTotal = "SELECT COUNT(DISTINCT rela_huesped) as total_huespedes_sistema FROM huesped_condicionsalud WHERE huespedcondicionsalud_estado = 1";
        $resultTotal = $this->db->query($sqlTotal);
        $totalSistema = $resultTotal->fetch_assoc();
        
        $porcentaje = 0;
        if ($totalSistema['total_huespedes_sistema'] > 0) {
            $porcentaje = round(($stats['huespedes_activos'] / $totalSistema['total_huespedes_sistema']) * 100, 2);
        }
        
        return [
            'total_huespedes' => (int) $stats['total_huespedes'],
            'huespedes_activos' => (int) $stats['huespedes_activos'],
            'total_reservas' => (int) $stats['total_reservas'],
            'reservas_activas' => (int) $stats['reservas_activas'],
            'porcentaje_huespedes' => $porcentaje
        ];
    }

}