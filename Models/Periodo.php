<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Periodo
 */
class Periodo extends Model
{
    protected $table = 'periodo';
    protected $primaryKey = 'id_periodo';

    /**
     * Obtener periodos activos
     */
    public function getActive()
    {
        return $this->findAll("periodo_estado = 1", "periodo_descripcion");
    }

    /**
     * Buscar periodos
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["periodo_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(periodo_descripcion LIKE ? OR periodo_fechainicio LIKE ? OR periodo_fechafin LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['fecha_inicio'])) {
            $where[] = "periodo_fechainicio >= ?";
            $params[] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where[] = "periodo_fechafin <= ?";
            $params[] = $filters['fecha_fin'];
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "periodo_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "periodo_fechainicio DESC", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(periodo_descripcion LIKE ? OR periodo_fechainicio LIKE ? OR periodo_fechafin LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['fecha_inicio'])) {
            $where[] = "periodo_fechainicio >= ?";
            $params[] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where[] = "periodo_fechafin <= ?";
            $params[] = $filters['fecha_fin'];
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "periodo_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        
        $result = $this->query($sql, $params);
        $total = $result->fetch_assoc()['total'];
        
        return ceil($total / $perPage);
    }

    /**
     * Buscar por ID
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * Verificar si el periodo está en uso
     */
    public function isInUse($id)
    {
        // Verificar en tabla reserva
        $sql = "SELECT COUNT(*) as count FROM reserva WHERE rela_periodo = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) return true;

        // Verificar en tabla precio (si existe)
        $sql = "SELECT COUNT(*) as count FROM precio WHERE rela_periodo = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $periodo = $this->find($id);
        if (!$periodo) {
            return false;
        }

        $newStatus = $periodo['periodo_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['periodo_estado' => $newStatus]);
    }

    /**
     * Obtener periodos con conteo de reservas
     */
    public function getWithReservasCount()
    {
        $sql = "SELECT p.*, 
                       COUNT(r.id_reserva) as reservas_count,
                       MIN(r.reserva_fechainicio) as primera_reserva,
                       MAX(r.reserva_fechafin) as ultima_reserva
                FROM {$this->table} p
                LEFT JOIN reserva r ON p.{$this->primaryKey} = r.rela_periodo
                GROUP BY p.{$this->primaryKey}
                ORDER BY p.periodo_fechainicio DESC";
        
        $result = $this->db->query($sql);
        
        $periodos = [];
        while ($row = $result->fetch_assoc()) {
            $periodos[] = $row;
        }
        
        return $periodos;
    }

    /**
     * Obtener periodos activos en un rango de fechas
     */
    public function getPeriodosActivos($fechaInicio = null, $fechaFin = null)
    {
        $where = ["periodo_estado = 1"];
        $params = [];

        if ($fechaInicio) {
            $where[] = "periodo_fechafin >= ?";
            $params[] = $fechaInicio;
        }

        if ($fechaFin) {
            $where[] = "periodo_fechainicio <= ?";
            $params[] = $fechaFin;
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY periodo_fechainicio";
        
        $result = $this->query($sql, $params);
        
        $periodos = [];
        while ($row = $result->fetch_assoc()) {
            $periodos[] = $row;
        }
        
        return $periodos;
    }

    /**
     * Verificar solapamiento de fechas
     */
    public function checkDateOverlap($fechaInicio, $fechaFin, $excludeId = null)
    {
        $where = ["periodo_estado = 1"];
        $params = [];

        // Condición para verificar solapamiento
        $where[] = "(? BETWEEN periodo_fechainicio AND periodo_fechafin OR 
                    ? BETWEEN periodo_fechainicio AND periodo_fechafin OR
                    periodo_fechainicio BETWEEN ? AND ? OR
                    periodo_fechafin BETWEEN ? AND ?)";
        $params = array_merge($params, [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);

        if ($excludeId) {
            $where[] = "{$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}";
        
        $result = $this->query($sql, $params);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Obtener estadísticas mensuales
     */
    public function getMonthlyStats($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $sql = "SELECT 
                    MONTH(r.reserva_fechainicio) as mes,
                    COUNT(r.id_reserva) as total_reservas,
                    COUNT(DISTINCT p.{$this->primaryKey}) as periodos_utilizados
                FROM {$this->table} p
                LEFT JOIN reserva r ON p.{$this->primaryKey} = r.rela_periodo 
                    AND YEAR(r.reserva_fechainicio) = ?
                WHERE p.periodo_estado = 1
                GROUP BY MONTH(r.reserva_fechainicio)
                ORDER BY mes";
        
        $result = $this->query($sql, [$year]);
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }

    /**
     * Obtener duración promedio de periodos
     */
    public function getAverageDuration()
    {
        $sql = "SELECT AVG(DATEDIFF(periodo_fechafin, periodo_fechainicio)) as duracion_promedio
                FROM {$this->table} 
                WHERE periodo_estado = 1";
        
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        return round($row['duracion_promedio'], 2);
    }

    /**
     * Obtener periodo actual (que incluya la fecha actual)
     */
    public function getPeriodoActual()
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->table} 
                WHERE periodo_estado = 1 
                AND ? BETWEEN periodo_fechainicio AND periodo_fechafin
                LIMIT 1";
        
        $result = $this->query($sql, [$today]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}