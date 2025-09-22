<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad TipoContacto
 */
class TipoContacto extends Model
{
    protected $table = 'tipocontacto';
    protected $primaryKey = 'id_tipocontacto';

    /**
     * Obtener tipos de contactos activos
     */
    public function getActive()
    {
        return $this->findAll("tipocontacto_estado = 1", "tipocontacto_descripcion");
    }

    /**
     * Buscar tipos de contactos
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["tipocontacto_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "tipocontacto_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "tipocontacto_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "tipocontacto_descripcion", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "tipocontacto_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "tipocontacto_estado = ?";
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
     * Verificar si el tipo de contacto está en uso
     */
    public function isInUse($id)
    {
        // Verificar en tabla persona (si existe relación)
        $sql = "SELECT COUNT(*) as count FROM persona WHERE rela_tipocontacto = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) return true;

        // Verificar en tabla contacto (si existe)
        $sql = "SELECT COUNT(*) as count FROM contacto WHERE rela_tipocontacto = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $tipoContacto = $this->find($id);
        if (!$tipoContacto) {
            return false;
        }

        $newStatus = $tipoContacto['tipocontacto_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['tipocontacto_estado' => $newStatus]);
    }

    /**
     * Obtener tipos de contactos con conteo de personas/contactos
     */
    public function getWithUsageCount()
    {
        $sql = "SELECT tc.*, 
                       COALESCE(p.persona_count, 0) + COALESCE(c.contacto_count, 0) as total_usage
                FROM {$this->table} tc
                LEFT JOIN (
                    SELECT rela_tipocontacto, COUNT(*) as persona_count 
                    FROM persona 
                    WHERE rela_tipocontacto IS NOT NULL 
                    GROUP BY rela_tipocontacto
                ) p ON tc.{$this->primaryKey} = p.rela_tipocontacto
                LEFT JOIN (
                    SELECT rela_tipocontacto, COUNT(*) as contacto_count 
                    FROM contacto 
                    WHERE rela_tipocontacto IS NOT NULL 
                    GROUP BY rela_tipocontacto
                ) c ON tc.{$this->primaryKey} = c.rela_tipocontacto
                ORDER BY tc.tipocontacto_descripcion";
        
        $result = $this->db->query($sql);
        
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }
        
        return $tipos;
    }

    /**
     * Obtener estadísticas por mes de creación de contactos
     */
    public function getMonthlyStats($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $sql = "SELECT tc.tipocontacto_descripcion,
                       MONTH(p.persona_fechacreacion) as mes,
                       COUNT(p.id_persona) as cantidad
                FROM {$this->table} tc
                LEFT JOIN persona p ON tc.{$this->primaryKey} = p.rela_tipocontacto 
                    AND YEAR(p.persona_fechacreacion) = ?
                WHERE tc.tipocontacto_estado = 1
                GROUP BY tc.{$this->primaryKey}, MONTH(p.persona_fechacreacion)
                UNION ALL
                SELECT tc.tipocontacto_descripcion,
                       MONTH(c.contacto_fecha) as mes,
                       COUNT(c.id_contacto) as cantidad
                FROM {$this->table} tc
                LEFT JOIN contacto c ON tc.{$this->primaryKey} = c.rela_tipocontacto 
                    AND YEAR(c.contacto_fecha) = ?
                WHERE tc.tipocontacto_estado = 1
                GROUP BY tc.{$this->primaryKey}, MONTH(c.contacto_fecha)
                ORDER BY tipocontacto_descripcion, mes";
        
        $result = $this->query($sql, [$year, $year]);
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }

    /**
     * Obtener tipos de contactos más utilizados
     */
    public function getMostUsed($limit = 10)
    {
        $sql = "SELECT tc.*, 
                       COALESCE(p.persona_count, 0) + COALESCE(c.contacto_count, 0) as total_usage
                FROM {$this->table} tc
                LEFT JOIN (
                    SELECT rela_tipocontacto, COUNT(*) as persona_count 
                    FROM persona 
                    WHERE rela_tipocontacto IS NOT NULL 
                    GROUP BY rela_tipocontacto
                ) p ON tc.{$this->primaryKey} = p.rela_tipocontacto
                LEFT JOIN (
                    SELECT rela_tipocontacto, COUNT(*) as contacto_count 
                    FROM contacto 
                    WHERE rela_tipocontacto IS NOT NULL 
                    GROUP BY rela_tipocontacto
                ) c ON tc.{$this->primaryKey} = c.rela_tipocontacto
                WHERE tc.tipocontacto_estado = 1
                ORDER BY total_usage DESC, tc.tipocontacto_descripcion
                LIMIT ?";
        
        $result = $this->query($sql, [$limit]);
        
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }
        
        return $tipos;
    }

    /**
     * Obtener resumen de uso por tipo
     */
    public function getUsageSummary()
    {
        $sql = "SELECT tc.tipocontacto_descripcion,
                       COALESCE(p.persona_count, 0) as personas,
                       COALESCE(c.contacto_count, 0) as contactos,
                       COALESCE(p.persona_count, 0) + COALESCE(c.contacto_count, 0) as total
                FROM {$this->table} tc
                LEFT JOIN (
                    SELECT rela_tipocontacto, COUNT(*) as persona_count 
                    FROM persona 
                    WHERE rela_tipocontacto IS NOT NULL 
                    GROUP BY rela_tipocontacto
                ) p ON tc.{$this->primaryKey} = p.rela_tipocontacto
                LEFT JOIN (
                    SELECT rela_tipocontacto, COUNT(*) as contacto_count 
                    FROM contacto 
                    WHERE rela_tipocontacto IS NOT NULL 
                    GROUP BY rela_tipocontacto
                ) c ON tc.{$this->primaryKey} = c.rela_tipocontacto
                WHERE tc.tipocontacto_estado = 1
                ORDER BY total DESC, tc.tipocontacto_descripcion";
        
        $result = $this->db->query($sql);
        
        $summary = [];
        while ($row = $result->fetch_assoc()) {
            $summary[] = $row;
        }
        
        return $summary;
    }
}