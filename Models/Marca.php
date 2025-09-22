<?php

namespace App\Models;

use App\Core\Model;

class Marca extends Model
{
    protected $table = 'marca';
    protected $primaryKey = 'id_marca';
    
    protected $fields = [
        'marca_descripcion',
        'marca_estado'
    ];

    protected $requiredFields = [
        'marca_descripcion'
    ];

    /**
     * Validar datos de marca
     */
    public function validate($data, $isUpdate = false, $id = null)
    {
        $errors = [];

        // Validar descripción
        if (empty($data['marca_descripcion'])) {
            $errors[] = 'La descripción de la marca es obligatoria.';
        } else {
            $descripcion = trim($data['marca_descripcion']);
            
            if (strlen($descripcion) < 2) {
                $errors[] = 'La descripción debe tener al menos 2 caracteres.';
            }
            
            if (strlen($descripcion) > 45) {
                $errors[] = 'La descripción no puede superar los 45 caracteres.';
            }

            // Verificar que no exista otra marca con la misma descripción
            if ($this->existsOtherWithDescription($descripcion, $id)) {
                $errors[] = 'Ya existe una marca con esta descripción.';
            }
        }

        // Validar estado (si se proporciona)
        if (isset($data['marca_estado'])) {
            if (!in_array($data['marca_estado'], [0, 1, '0', '1'])) {
                $errors[] = 'El estado debe ser 0 (inactivo) o 1 (activo).';
            }
        }

        return $errors;
    }

    /**
     * Verificar si existe otra marca con la misma descripción
     */
    private function existsOtherWithDescription($descripcion, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE marca_descripcion = ?";
        $params = [$descripcion];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Obtener marcas activas
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE marca_estado = 1 ORDER BY marca_descripcion ASC";
        return $this->query($sql);
    }

    /**
     * Obtener marcas con filtros y paginación
     */
    public function getWithFilters($filters = [], $page = 1, $limit = 10, $orderBy = 'marca_descripcion', $orderDir = 'ASC')
    {
        $conditions = ['1 = 1'];
        $params = [];

        // Filtro por descripción
        if (!empty($filters['marca_descripcion'])) {
            $conditions[] = "marca_descripcion LIKE ?";
            $params[] = "%" . $filters['marca_descripcion'] . "%";
        }

        // Filtro por estado
        if (isset($filters['marca_estado']) && $filters['marca_estado'] !== '') {
            $conditions[] = "marca_estado = ?";
            $params[] = $filters['marca_estado'];
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $limit;

        // Validar orden
        $validOrderBy = ['marca_descripcion', 'marca_estado', 'id_marca'];
        if (!in_array($orderBy, $validOrderBy)) {
            $orderBy = 'marca_descripcion';
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
     * Buscar marcas por término
     */
    public function searchByTerm($term, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE marca_descripcion LIKE ? 
                AND marca_estado = 1 
                ORDER BY marca_descripcion ASC 
                LIMIT ?";
        
        return $this->query($sql, ["%{$term}%", $limit]);
    }

    /**
     * Cambiar estado de la marca (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current['marca_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['marca_estado' => $newStatus]);
    }

    /**
     * Obtener estadísticas de marcas
     */
    public function getStats()
    {
        $stats = [];

        // Total de marcas
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->query($sql);
        $stats['total_marcas'] = $result[0]['total'];

        // Marcas activas
        $sql = "SELECT COUNT(*) as activos FROM {$this->table} WHERE marca_estado = 1";
        $result = $this->query($sql);
        $stats['marcas_activas'] = $result[0]['activos'];

        // Marcas inactivas
        $stats['marcas_inactivas'] = $stats['total_marcas'] - $stats['marcas_activas'];

        // Marcas más utilizadas (según relación con productos)
        $sql = "SELECT m.id_marca, m.marca_descripcion, 
                       COUNT(p.id_producto) as productos_count
                FROM {$this->table} m
                LEFT JOIN producto p ON m.id_marca = p.rela_marca 
                WHERE m.marca_estado = 1
                GROUP BY m.id_marca, m.marca_descripcion
                ORDER BY productos_count DESC
                LIMIT 10";
        $stats['marcas_mas_utilizadas'] = $this->query($sql);

        return $stats;
    }

    /**
     * Verificar si está en uso en productos
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as total FROM producto WHERE rela_marca = ?";
        $result = $this->query($sql, [$id]);
        
        return $result[0]['total'] > 0;
    }

    /**
     * Obtener productos asociados a una marca
     */
    public function getProductos($id, $limit = 10)
    {
        $sql = "SELECT p.*, c.categoria_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                WHERE p.rela_marca = ?
                ORDER BY p.producto_descripcion ASC
                LIMIT ?";
        
        return $this->query($sql, [$id, $limit]);
    }

    /**
     * Sanitizar y preparar datos para inserción/actualización
     */
    public function sanitizeData($data)
    {
        $sanitized = [];

        if (isset($data['marca_descripcion'])) {
            $sanitized['marca_descripcion'] = trim($data['marca_descripcion']);
            // Capitalizar primera letra de cada palabra
            $sanitized['marca_descripcion'] = ucwords(strtolower($sanitized['marca_descripcion']));
        }

        if (isset($data['marca_estado'])) {
            $sanitized['marca_estado'] = (int) $data['marca_estado'];
        }

        return $sanitized;
    }
}