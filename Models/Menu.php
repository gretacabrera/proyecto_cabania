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
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
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
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    /**
     * Obtener menús activos ordenados
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE menu_estado = 1 ORDER BY menu_orden ASC, menu_nombre ASC";
        $result = $this->query($sql);
        
        $menus = [];
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
        
        return $menus;
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
        $result = $this->query($sql, $params);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $totalResult = $this->query($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = $totalRow['total'];

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
        
        $result = $this->query($sql, ["%{$term}%", $limit]);
        
        $menus = [];
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
        
        return $menus;
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
        $row = $result->fetch_assoc();
        $stats['total_menus'] = $row['total'];

        // Menús activos
        $sql = "SELECT COUNT(*) as activos FROM {$this->table} WHERE menu_estado = 1";
        $result = $this->query($sql);
        $row = $result->fetch_assoc();
        $stats['menus_activos'] = $row['activos'];

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
        $result = $this->query($sql);
        
        $menus = [];
        while ($row = $result->fetch_assoc()) {
            $menus[] = $row;
        }
        $stats['menus_mas_utilizados'] = $menus;

        return $stats;
    }

    /**
     * Verificar si está en uso en módulos
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as total FROM modulo WHERE rela_menu = ?";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        return $row['total'] > 0;
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
        
        $result = $this->query($sql, [$id, $limit]);
        
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row;
        }
        
        return $modulos;
    }

    /**
     * Obtener siguiente orden disponible
     */
    public function getNextOrder()
    {
        $sql = "SELECT MAX(menu_orden) as max_orden FROM {$this->table}";
        $result = $this->query($sql);
        $row = $result->fetch_assoc();
        
        return ($row['max_orden'] ?? 0) + 1;
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


    /**
     * Obtener men�s con paginaci�n y filtros para listado
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['menu_nombre'])) {
            $where .= " AND menu_nombre LIKE ?";
            $params[] = '%' . $filters['menu_nombre'] . '%';
        }
        
        if (isset($filters['menu_estado']) && $filters['menu_estado'] !== '') {
            $where .= " AND menu_estado = ?";
            $params[] = (int) $filters['menu_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "menu_orden ASC", $params);
    }

    /**
     * Método auxiliar para paginación con parámetros
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->query($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT * FROM {$this->table} WHERE $where $orderClause LIMIT $limit OFFSET $offset";
        $dataResult = $this->query($dataSql, $params);
        
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
     * Obtener todos los menús con detalles para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['menu_nombre'])) {
            $where .= " AND menu_nombre LIKE ?";
            $params[] = '%' . $filters['menu_nombre'] . '%';
        }
        
        if (isset($filters['menu_estado']) && $filters['menu_estado'] !== '') {
            $where .= " AND menu_estado = ?";
            $params[] = (int) $filters['menu_estado'];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY menu_orden ASC";
        $result = $this->query($sql, $params);
        
        // Convertir mysqli_result a array
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
     * Obtener estadísticas de un menú específico
     */
    public function getStatistics($id)
    {
        $stats = [];
        
        // Total de módulos asociados
        $sql = "SELECT COUNT(*) as total FROM modulo WHERE rela_menu = ?";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        $stats['total_modulos'] = $row['total'] ?? 0;
        
        // Módulos activos
        $sql = "SELECT COUNT(*) as activos FROM modulo WHERE rela_menu = ? AND modulo_estado = 1";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        $stats['modulos_activos'] = $row['activos'] ?? 0;
        
        // Perfiles que usan este menú (a través de sus módulos)
        $sql = "SELECT COUNT(DISTINCT pm.rela_perfil) as total 
                FROM perfil_modulo pm
                INNER JOIN modulo m ON pm.rela_modulo = m.id_modulo
                WHERE m.rela_menu = ?";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        $stats['perfiles_usando'] = $row['total'] ?? 0;
        
        // Porcentaje de módulos activos
        if ($stats['total_modulos'] > 0) {
            $stats['porcentaje_activos'] = round(($stats['modulos_activos'] / $stats['total_modulos']) * 100);
        } else {
            $stats['porcentaje_activos'] = 0;
        }
        
        return $stats;
    }
}
