<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de módulos del sistema
 */
class Modulo extends Model
{
    protected $table = 'modulo';
    protected $primaryKey = 'id_modulo';

    /**
     * Buscar módulos
     */
    public function search($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->findAll(
            "modulo_estado = 1 AND (modulo_nombre LIKE '%{$query}%' OR modulo_descripcion LIKE '%{$query}%')",
            "modulo_orden ASC, modulo_nombre ASC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($query = null, $perPage = 10)
    {
        if ($query) {
            $total = $this->count("modulo_estado = 1 AND (modulo_nombre LIKE '%{$query}%' OR modulo_descripcion LIKE '%{$query}%')");
        } else {
            $total = $this->count("modulo_estado = 1");
        }
        
        return ceil($total / $perPage);
    }

    /**
     * Obtener módulos padre
     */
    public function getModulosPadre($excludeId = null)
    {
        $where = "modulo_padre IS NULL AND modulo_estado = 1";
        if ($excludeId) {
            $where .= " AND id_modulo != {$excludeId}";
        }
        
        return $this->findAll($where, "modulo_orden ASC, modulo_nombre ASC");
    }

    /**
     * Verificar si tiene submódulos activos
     */
    public function hasActiveChildren($id)
    {
        $total = $this->count("modulo_padre = {$id} AND modulo_estado = 1");
        return $total > 0;
    }

    /**
     * Verificar si está asignado a perfiles
     */
    public function hasProfileAssignments($id)
    {
        $sql = "SELECT COUNT(*) as total FROM perfiles_modulos WHERE rela_modulo = {$id}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'] > 0;
    }

    /**
     * Obtener estructura jerárquica
     */
    public function getModulosTree()
    {
        $sql = "SELECT * FROM {$this->table} WHERE modulo_estado = 1 ORDER BY modulo_orden ASC, modulo_nombre ASC";
        $result = $this->db->query($sql);
        
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row;
        }
        
        return $this->buildTree($modulos);
    }

    /**
     * Obtener módulo con permisos
     */
    public function findWithPermissions($id)
    {
        $sql = "SELECT m.*, GROUP_CONCAT(p.perfil_nombre) as perfiles_asignados
                FROM {$this->table} m
                LEFT JOIN perfiles_modulos pm ON m.id_modulo = pm.rela_modulo
                LEFT JOIN perfiles p ON pm.rela_perfil = p.id_perfil
                WHERE m.id_modulo = {$id}
                GROUP BY m.id_modulo";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Construir árbol jerárquico
     */
    private function buildTree($elements, $parentId = null)
    {
        $branch = [];
        
        foreach ($elements as $element) {
            if ($element['modulo_padre'] == $parentId) {
                $children = $this->buildTree($elements, $element['id_modulo']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        
        return $branch;
    }

    /**
     * Obtener módulos por ID de menú
     */
    public function getByMenuId($menuId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rela_menu = ? 
                ORDER BY modulo_descripcion ASC";
        
        $result = $this->query($sql, [$menuId]);
        
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row;
        }
        
        return $modulos;
    }
}