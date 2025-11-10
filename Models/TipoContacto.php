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
     * Obtener tipos de contactos con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['tipocontacto_descripcion'])) {
            $where .= " AND tipocontacto_descripcion LIKE ?";
            $params[] = '%' . $filters['tipocontacto_descripcion'] . '%';
        }
        
        if (isset($filters['tipocontacto_estado']) && $filters['tipocontacto_estado'] !== '') {
            $where .= " AND tipocontacto_estado = ?";
            $params[] = (int) $filters['tipocontacto_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "tipocontacto_descripcion ASC", $params);
    }

    /**
     * Obtener todos los tipos de contactos con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['tipocontacto_descripcion'])) {
            $where .= " AND tipocontacto_descripcion LIKE ?";
            $params[] = '%' . $filters['tipocontacto_descripcion'] . '%';
        }
        
        if (isset($filters['tipocontacto_estado']) && $filters['tipocontacto_estado'] !== '') {
            $where .= " AND tipocontacto_estado = ?";
            $params[] = (int) $filters['tipocontacto_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY tipocontacto_descripcion ASC";
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
     * Obtener tipos de contactos con paginación usando parámetros preparados
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
     * Obtener estadísticas de un tipo de contacto específico
     */
    public function getStatistics($tipoContactoId)
    {
        $stats = [
            'total_contactos' => $this->getTotalContactos($tipoContactoId),
            'total_personas' => $this->getTotalPersonas($tipoContactoId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de contactos registrados para este tipo
     */
    private function getTotalContactos($tipoContactoId)
    {
        $sql = "SELECT COUNT(*) as total FROM contacto WHERE rela_tipocontacto = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $tipoContactoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número de personas que tienen contactos de este tipo
     */
    private function getTotalPersonas($tipoContactoId)
    {
        $sql = "SELECT COUNT(DISTINCT rela_persona) as total 
                FROM contacto 
                WHERE rela_tipocontacto = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $tipoContactoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
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