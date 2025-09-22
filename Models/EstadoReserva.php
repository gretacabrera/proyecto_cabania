<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad EstadoReserva
 */
class EstadoReserva extends Model
{
    protected $table = 'estadoreserva';
    protected $primaryKey = 'id_estadoreserva';

    /**
     * Obtener estados activos
     */
    public function getActive()
    {
        return $this->findAll("estadoreserva_estado = 1", "estadoreserva_descripcion");
    }

    /**
     * Buscar estados de reservas
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["estadoreserva_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoreserva_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoreserva_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "estadoreserva_descripcion", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoreserva_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoreserva_estado = ?";
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
     * Verificar si el estado está en uso
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as count FROM reserva WHERE rela_estadoreserva = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $estadoReserva = $this->find($id);
        if (!$estadoReserva) {
            return false;
        }

        $newStatus = $estadoReserva['estadoreserva_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['estadoreserva_estado' => $newStatus]);
    }

    /**
     * Obtener estados con conteo de reservas
     */
    public function getWithReservaCount()
    {
        $sql = "SELECT er.*, 
                       COUNT(r.id_reserva) as reservas_count
                FROM {$this->table} er
                LEFT JOIN reserva r ON er.{$this->primaryKey} = r.rela_estadoreserva
                GROUP BY er.{$this->primaryKey}
                ORDER BY er.estadoreserva_descripcion";
        
        $result = $this->db->query($sql);
        
        $estados = [];
        while ($row = $result->fetch_assoc()) {
            $estados[] = $row;
        }
        
        return $estados;
    }

    /**
     * Obtener estadísticas por mes
     */
    public function getMonthlyStats($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $sql = "SELECT er.estadoreserva_descripcion,
                       MONTH(r.reserva_fechainicio) as mes,
                       COUNT(r.id_reserva) as cantidad
                FROM {$this->table} er
                LEFT JOIN reserva r ON er.{$this->primaryKey} = r.rela_estadoreserva 
                    AND YEAR(r.reserva_fechainicio) = ?
                WHERE er.estadoreserva_estado = 1
                GROUP BY er.{$this->primaryKey}, MONTH(r.reserva_fechainicio)
                ORDER BY er.estadoreserva_descripcion, mes";
        
        $result = $this->query($sql, [$year]);
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
}