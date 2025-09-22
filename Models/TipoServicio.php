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
     * Buscar tipos de servicios
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["tiposervicio_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "tiposervicio_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "tiposervicio_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "tiposervicio_descripcion", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "tiposervicio_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "tiposervicio_estado = ?";
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
     * Verificar si el tipo de servicio está en uso
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as count FROM servicio WHERE rela_tiposervicio = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $tipoServicio = $this->find($id);
        if (!$tipoServicio) {
            return false;
        }

        $newStatus = $tipoServicio['tiposervicio_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['tiposervicio_estado' => $newStatus]);
    }

    /**
     * Obtener tipos de servicios con conteo de servicios
     */
    public function getWithServiciosCount()
    {
        $sql = "SELECT ts.*, 
                       COUNT(s.id_servicio) as servicios_count
                FROM {$this->table} ts
                LEFT JOIN servicio s ON ts.{$this->primaryKey} = s.rela_tiposervicio
                GROUP BY ts.{$this->primaryKey}
                ORDER BY ts.tiposervicio_descripcion";
        
        $result = $this->db->query($sql);
        
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }
        
        return $tipos;
    }

    /**
     * Obtener estadísticas por mes
     */
    public function getMonthlyStats($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $sql = "SELECT ts.tiposervicio_descripcion,
                       MONTH(s.servicio_fecha) as mes,
                       COUNT(s.id_servicio) as cantidad
                FROM {$this->table} ts
                LEFT JOIN servicio s ON ts.{$this->primaryKey} = s.rela_tiposervicio 
                    AND YEAR(s.servicio_fecha) = ?
                WHERE ts.tiposervicio_estado = 1
                GROUP BY ts.{$this->primaryKey}, MONTH(s.servicio_fecha)
                ORDER BY ts.tiposervicio_descripcion, mes";
        
        $result = $this->query($sql, [$year]);
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }

    /**
     * Obtener tipos de servicios más utilizados
     */
    public function getMostUsed($limit = 10)
    {
        $sql = "SELECT ts.*, COUNT(s.id_servicio) as total_servicios
                FROM {$this->table} ts
                LEFT JOIN servicio s ON ts.{$this->primaryKey} = s.rela_tiposervicio
                WHERE ts.tiposervicio_estado = 1
                GROUP BY ts.{$this->primaryKey}
                ORDER BY total_servicios DESC, ts.tiposervicio_descripcion
                LIMIT ?";
        
        $result = $this->query($sql, [$limit]);
        
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }
        
        return $tipos;
    }
}