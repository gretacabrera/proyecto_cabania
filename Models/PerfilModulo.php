<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de relaciones entre perfiles y módulos (tabla pivot)
 */
class PerfilModulo extends Model
{
    protected $table = 'perfil_modulo';
    protected $primaryKey = 'id_perfilmodulo';

    /**
     * Obtener todas las asignaciones con información de perfil y módulo
     */
    public function getAllWithDetails($filters = [], $page = 1, $perPage = 10, $showInactive = false)
    {
        $offset = ($page - 1) * $perPage;
        
        $where = "WHERE 1=1";
        
        // Filtro por estado (solo administradores ven inactivos)
        if (!$showInactive) {
            $where .= " AND pm.perfilmodulo_estado = 1";
        }
        
        // Aplicar filtros adicionales
        if (isset($filters['rela_perfil']) && $filters['rela_perfil'] != '') {
            $where .= " AND pm.rela_perfil = " . intval($filters['rela_perfil']);
        }
        
        if (isset($filters['rela_modulo']) && $filters['rela_modulo'] != '') {
            $where .= " AND pm.rela_modulo = " . intval($filters['rela_modulo']);
        }
        
        if (isset($filters['perfilmodulo_estado']) && $filters['perfilmodulo_estado'] != '') {
            $where .= " AND pm.perfilmodulo_estado = " . intval($filters['perfilmodulo_estado']);
        }
        
        $sql = "SELECT pm.*, 
                       p.perfil_descripcion, p.perfil_nombre,
                       m.modulo_descripcion, m.modulo_nombre
                FROM {$this->table} pm
                LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                LEFT JOIN modulo m ON pm.rela_modulo = m.id_modulo
                {$where}
                ORDER BY p.perfil_descripcion ASC, m.modulo_descripcion ASC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $result = $this->db->query($sql);
        $registros = [];
        while ($row = $result->fetch_assoc()) {
            $registros[] = $row;
        }
        
        return $registros;
    }

    /**
     * Contar total de asignaciones
     */
    public function countWithFilters($filters = [], $showInactive = false)
    {
        $where = "WHERE 1=1";
        
        if (!$showInactive) {
            $where .= " AND pm.perfilmodulo_estado = 1";
        }
        
        if (isset($filters['rela_perfil']) && $filters['rela_perfil'] != '') {
            $where .= " AND pm.rela_perfil = " . intval($filters['rela_perfil']);
        }
        
        if (isset($filters['rela_modulo']) && $filters['rela_modulo'] != '') {
            $where .= " AND pm.rela_modulo = " . intval($filters['rela_modulo']);
        }
        
        if (isset($filters['perfilmodulo_estado']) && $filters['perfilmodulo_estado'] != '') {
            $where .= " AND pm.perfilmodulo_estado = " . intval($filters['perfilmodulo_estado']);
        }
        
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} pm
                LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                LEFT JOIN modulo m ON pm.rela_modulo = m.id_modulo
                {$where}";
        
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return intval($row['total']);
    }

    /**
     * Crear nueva asignación perfil-módulo
     */
    public function createAssignment($perfilId, $moduloId)
    {
        // Verificar que no exista ya la asignación
        if ($this->exists($perfilId, $moduloId)) {
            return false;
        }
        
        $data = [
            'rela_perfil' => intval($perfilId),
            'rela_modulo' => intval($moduloId),
            'perfilmodulo_estado' => 1
        ];
        
        return $this->create($data);
    }

    /**
     * Verificar si existe una asignación
     */
    public function exists($perfilId, $moduloId, $includeInactive = true)
    {
        $where = "rela_perfil = " . intval($perfilId) . " AND rela_modulo = " . intval($moduloId);
        
        if (!$includeInactive) {
            $where .= " AND perfilmodulo_estado = 1";
        }
        
        $count = $this->count($where);
        return $count > 0;
    }

    /**
     * Obtener asignación con detalles
     */
    public function findWithDetails($id)
    {
        $sql = "SELECT pm.*, 
                       p.perfil_descripcion, p.perfil_nombre,
                       m.modulo_descripcion, m.modulo_nombre
                FROM {$this->table} pm
                LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                LEFT JOIN modulo m ON pm.rela_modulo = m.id_modulo
                WHERE pm.id_perfilmodulo = " . intval($id);
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Cambiar estado de una asignación (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }
        
        $newStatus = $current['perfilmodulo_estado'] ? 0 : 1;
        
        return $this->update($id, ['perfilmodulo_estado' => $newStatus]);
    }

    /**
     * Dar de baja lógica
     */
    public function softDelete($id, $field = 'perfilmodulo_estado')
    {
        return parent::softDelete($id, $field);
    }

    /**
     * Quitar baja lógica (restaurar)
     */
    public function restore($id, $field = 'perfilmodulo_estado')
    {
        return parent::restore($id, $field);
    }

    /**
     * Verificar si se puede eliminar una asignación
     * (no se puede eliminar permisos del perfil administrador)
     */
    public function canDelete($id)
    {
        $assignment = $this->findWithDetails($id);
        
        if (!$assignment) {
            return false;
        }
        
        // No permitir eliminar permisos del perfil administrador
        if (strtolower($assignment['perfil_descripcion']) === 'administrador') {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener módulos asignados a un perfil
     */
    public function getModulesByProfile($perfilId, $activeOnly = true)
    {
        $where = "pm.rela_perfil = " . intval($perfilId);
        if ($activeOnly) {
            $where .= " AND pm.perfilmodulo_estado = 1 AND m.modulo_estado = 1";
        }
        
        $sql = "SELECT m.*, pm.id_perfilmodulo, pm.perfilmodulo_estado
                FROM {$this->table} pm
                INNER JOIN modulo m ON pm.rela_modulo = m.id_modulo
                WHERE {$where}
                ORDER BY m.modulo_orden ASC, m.modulo_descripcion ASC";
        
        $result = $this->db->query($sql);
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row;
        }
        
        return $modulos;
    }

    /**
     * Obtener perfiles que tienen asignado un módulo
     */
    public function getProfilesByModule($moduloId, $activeOnly = true)
    {
        $where = "pm.rela_modulo = " . intval($moduloId);
        if ($activeOnly) {
            $where .= " AND pm.perfilmodulo_estado = 1 AND p.perfil_estado = 1";
        }
        
        $sql = "SELECT p.*, pm.id_perfilmodulo, pm.perfilmodulo_estado
                FROM {$this->table} pm
                INNER JOIN perfil p ON pm.rela_perfil = p.id_perfil
                WHERE {$where}
                ORDER BY p.perfil_descripcion ASC";
        
        $result = $this->db->query($sql);
        $perfiles = [];
        while ($row = $result->fetch_assoc()) {
            $perfiles[] = $row;
        }
        
        return $perfiles;
    }

    /**
     * Sincronizar módulos de un perfil
     */
    public function syncModulesForProfile($perfilId, $modulosIds)
    {
        // Comenzar transacción
        $this->db->beginTransaction();
        
        try {
            // Marcar todos los módulos actuales como inactivos
            $sql = "UPDATE {$this->table} SET perfilmodulo_estado = 0 WHERE rela_perfil = " . intval($perfilId);
            $this->db->query($sql);
            
            // Crear/reactivar asignaciones para los módulos seleccionados
            foreach ($modulosIds as $moduloId) {
                $moduloId = intval($moduloId);
                
                // Verificar si ya existe la asignación
                $existing = $this->findAll("rela_perfil = {$perfilId} AND rela_modulo = {$moduloId}");
                
                if (!empty($existing)) {
                    // Reactivar asignación existente
                    $this->update($existing[0]['id_perfilmodulo'], ['perfilmodulo_estado' => 1]);
                } else {
                    // Crear nueva asignación
                    $this->createAssignment($perfilId, $moduloId);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Obtener perfiles con más módulos asignados
     */
    public function getProfilesWithMostModules($limit = 10)
    {
        $sql = "SELECT p.perfil_descripcion, COUNT(pm.id_perfilmodulo) as total_modulos
                FROM perfil p
                LEFT JOIN {$this->table} pm ON p.id_perfil = pm.rela_perfil AND pm.perfilmodulo_estado = 1
                WHERE p.perfil_estado = 1
                GROUP BY p.id_perfil
                ORDER BY total_modulos DESC
                LIMIT " . intval($limit);
        
        $result = $this->db->query($sql);
        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
        
        return $profiles;
    }

    /**
     * Obtener módulos más asignados
     */
    public function getMostAssignedModules($limit = 10)
    {
        $sql = "SELECT m.modulo_descripcion, COUNT(pm.id_perfilmodulo) as total_perfiles
                FROM modulo m
                LEFT JOIN {$this->table} pm ON m.id_modulo = pm.rela_modulo AND pm.perfilmodulo_estado = 1
                WHERE m.modulo_estado = 1
                GROUP BY m.id_modulo
                ORDER BY total_perfiles DESC
                LIMIT " . intval($limit);
        
        $result = $this->db->query($sql);
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        
        return $modules;
    }
}