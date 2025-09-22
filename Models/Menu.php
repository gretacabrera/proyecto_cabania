<?php

namespace App\Models;

use App\Core\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    
    protected $fields = [
        'menu_nombre',
        'menu_orden',
        'menu_estado'
    ];

    protected $requiredFields = [
        'menu_nombre'
    ];

    /**
     * Validar datos de menú
     */
    public function validate($data, $isUpdate = false, $id = null)
    {
        $errors = [];

        // Validar nombre
        if (empty($data['menu_nombre'])) {
            $errors[] = 'El nombre del menú es obligatorio.';
        } else {
            $nombre = trim($data['menu_nombre']);
            
            if (strlen($nombre) < 2) {
                $errors[] = 'El nombre debe tener al menos 2 caracteres.';
            }
            
            if (strlen($nombre) > 45) {
                $errors[] = 'El nombre no puede superar los 45 caracteres.';
            }

            // Verificar que no exista otro menú con el mismo nombre
            if ($this->existsOtherWithName($nombre, $id)) {
                $errors[] = 'Ya existe un menú con este nombre.';
            }
        }

        // Validar orden
        if (isset($data['menu_orden'])) {
            $orden = $data['menu_orden'];
            if (!is_numeric($orden) || $orden < 1) {
                $errors[] = 'El orden debe ser un número mayor a 0.';
            }
            
            // Verificar que no exista otro menú con el mismo orden
            if ($this->existsOtherWithOrder($orden, $id)) {
                $errors[] = 'Ya existe un menú con este orden. Use un número diferente.';
            }
        }

        // Validar estado (si se proporciona)
        if (isset($data['menu_estado'])) {
            if (!in_array($data['menu_estado'], [0, 1, '0', '1'])) {
                $errors[] = 'El estado debe ser 0 (inactivo) o 1 (activo).';
            }
        }

        return $errors;
    }

    /**
     * Verificar si existe otro menú con el mismo nombre
     */
    private function existsOtherWithName($nombre, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE menu_nombre = ?";
        $params = [$nombre];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Verificar si existe otro menú con el mismo orden
     */
    private function existsOtherWithOrder($orden, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE menu_orden = ?";
        $params = [$orden];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Obtener menús activos ordenados
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE menu_estado = 1 ORDER BY menu_orden ASC, menu_nombre ASC";
        return $this->query($sql);
    }

    /**
     * Obtener menús con filtros y paginación
     */
    public function getWithFilters($filters = [], $page = 1, $limit = 10, $orderBy = 'menu_orden', $orderDir = 'ASC')
    {
        $conditions = ['1 = 1'];
        $params = [];

        // Filtro por nombre
        if (!empty($filters['menu_nombre'])) {
            $conditions[] = "menu_nombre LIKE ?";
            $params[] = "%" . $filters['menu_nombre'] . "%";
        }

        // Filtro por estado
        if (isset($filters['menu_estado']) && $filters['menu_estado'] !== '') {
            $conditions[] = "menu_estado = ?";
            $params[] = $filters['menu_estado'];
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $limit;

        // Validar orden
        $validOrderBy = ['menu_nombre', 'menu_orden', 'menu_estado', 'id_menu'];
        if (!in_array($orderBy, $validOrderBy)) {
            $orderBy = 'menu_orden';
        }

        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        // Consulta principal
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY {$orderBy} {$orderDir} LIMIT {$limit} OFFSET {$offset}";
        $data = $this->query($sql, $params);

        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $totalResult = $this->query($countSql, $params);
        $total = $totalResult[0]['total'];

        return [
            'data' => $data,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
            'per_page' => $limit
        ];
    }

    /**
     * Buscar menús por término
     */
    public function searchByTerm($term, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE menu_nombre LIKE ? 
                AND menu_estado = 1 
                ORDER BY menu_orden ASC, menu_nombre ASC 
                LIMIT ?";
        
        return $this->query($sql, ["%{$term}%", $limit]);
    }

    /**
     * Cambiar estado del menú (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current['menu_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['menu_estado' => $newStatus]);
    }

    /**
     * Obtener estadísticas de menús
     */
    public function getStats()
    {
        $stats = [];

        // Total de menús
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->query($sql);
        $stats['total_menus'] = $result[0]['total'];

        // Menús activos
        $sql = "SELECT COUNT(*) as activos FROM {$this->table} WHERE menu_estado = 1";
        $result = $this->query($sql);
        $stats['menus_activos'] = $result[0]['activos'];

        // Menús inactivos
        $stats['menus_inactivos'] = $stats['total_menus'] - $stats['menus_activos'];

        // Menús con más módulos (si existe relación)
        $sql = "SELECT m.id_menu, m.menu_nombre, 
                       COUNT(mo.id_modulo) as modulos_count
                FROM {$this->table} m
                LEFT JOIN modulo mo ON m.id_menu = mo.rela_menu 
                WHERE m.menu_estado = 1
                GROUP BY m.id_menu, m.menu_nombre
                ORDER BY modulos_count DESC
                LIMIT 10";
        $stats['menus_mas_utilizados'] = $this->query($sql);

        return $stats;
    }

    /**
     * Verificar si está en uso en módulos
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as total FROM modulo WHERE rela_menu = ?";
        $result = $this->query($sql, [$id]);
        
        return $result[0]['total'] > 0;
    }

    /**
     * Obtener módulos asociados a un menú
     */
    public function getModulos($id, $limit = 20)
    {
        $sql = "SELECT mo.*
                FROM modulo mo
                WHERE mo.rela_menu = ?
                AND mo.modulo_estado = 1
                ORDER BY mo.modulo_descripcion ASC
                LIMIT ?";
        
        return $this->query($sql, [$id, $limit]);
    }

    /**
     * Obtener siguiente orden disponible
     */
    public function getNextOrder()
    {
        $sql = "SELECT MAX(menu_orden) as max_orden FROM {$this->table}";
        $result = $this->query($sql);
        
        return ($result[0]['max_orden'] ?? 0) + 1;
    }

    /**
     * Reordenar menús
     */
    public function reorder($ordenMap)
    {
        foreach ($ordenMap as $id => $orden) {
            $this->update($id, ['menu_orden' => $orden]);
        }
        return true;
    }

    /**
     * Sanitizar y preparar datos para inserción/actualización
     */
    public function sanitizeData($data)
    {
        $sanitized = [];

        if (isset($data['menu_nombre'])) {
            $sanitized['menu_nombre'] = trim($data['menu_nombre']);
            // Capitalizar primera letra de cada palabra
            $sanitized['menu_nombre'] = ucwords(strtolower($sanitized['menu_nombre']));
        }

        if (isset($data['menu_orden'])) {
            $sanitized['menu_orden'] = (int) $data['menu_orden'];
        }

        if (isset($data['menu_estado'])) {
            $sanitized['menu_estado'] = (int) $data['menu_estado'];
        }

        return $sanitized;
    }
}