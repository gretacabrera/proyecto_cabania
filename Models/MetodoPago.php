<?php

namespace App\Models;

use App\Core\Model;

class MetodoPago extends Model
{
    protected $table = 'metododepago';
    protected $primaryKey = 'id_metododepago';
    
    protected $fields = [
        'metododepago_descripcion',
        'metododepago_estado'
    ];

    protected $requiredFields = [
        'metododepago_descripcion'
    ];

    /**
     * Validar datos de método de pago
     */
    public function validate($data, $isUpdate = false, $id = null)
    {
        $errors = [];

        // Validar descripción
        if (empty($data['metododepago_descripcion'])) {
            $errors[] = 'La descripción del método de pago es obligatoria.';
        } else {
            $descripcion = trim($data['metododepago_descripcion']);
            
            if (strlen($descripcion) < 2) {
                $errors[] = 'La descripción debe tener al menos 2 caracteres.';
            }
            
            if (strlen($descripcion) > 45) {
                $errors[] = 'La descripción no puede superar los 45 caracteres.';
            }

            // Verificar que no exista otro método con la misma descripción
            if ($this->existsOtherWithDescription($descripcion, $id)) {
                $errors[] = 'Ya existe un método de pago con esta descripción.';
            }
        }

        // Validar estado (si se proporciona)
        if (isset($data['metododepago_estado'])) {
            if (!in_array($data['metododepago_estado'], [0, 1, '0', '1'])) {
                $errors[] = 'El estado debe ser 0 (inactivo) o 1 (activo).';
            }
        }

        return $errors;
    }

    /**
     * Verificar si existe otro método con la misma descripción
     */
    private function existsOtherWithDescription($descripcion, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE metododepago_descripcion = ?";
        $params = [$descripcion];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Obtener métodos de pago activos
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE metododepago_estado = 1 ORDER BY metododepago_descripcion ASC";
        return $this->query($sql);
    }

    /**
     * Obtener métodos con filtros y paginación
     */
    public function getWithFilters($filters = [], $page = 1, $limit = 10, $orderBy = 'metododepago_descripcion', $orderDir = 'ASC')
    {
        $conditions = ['1 = 1'];
        $params = [];

        // Filtro por descripción
        if (!empty($filters['metododepago_descripcion'])) {
            $conditions[] = "metododepago_descripcion LIKE ?";
            $params[] = "%" . $filters['metododepago_descripcion'] . "%";
        }

        // Filtro por estado
        if (isset($filters['metododepago_estado']) && $filters['metododepago_estado'] !== '') {
            $conditions[] = "metododepago_estado = ?";
            $params[] = $filters['metododepago_estado'];
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $limit;

        // Validar orden
        $validOrderBy = ['metododepago_descripcion', 'metododepago_estado', 'id_metododepago'];
        if (!in_array($orderBy, $validOrderBy)) {
            $orderBy = 'metododepago_descripcion';
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
     * Buscar métodos de pago por término
     */
    public function searchByTerm($term, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE metododepago_descripcion LIKE ? 
                AND metododepago_estado = 1 
                ORDER BY metododepago_descripcion ASC 
                LIMIT ?";
        
        return $this->query($sql, ["%{$term}%", $limit]);
    }

    /**
     * Cambiar estado del método (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current['metododepago_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['metododepago_estado' => $newStatus]);
    }

    /**
     * Obtener estadísticas de métodos de pago
     */
    public function getStats()
    {
        $stats = [];

        // Total de métodos
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->query($sql);
        $stats['total_metodos'] = $result[0]['total'];

        // Métodos activos
        $sql = "SELECT COUNT(*) as activos FROM {$this->table} WHERE metododepago_estado = 1";
        $result = $this->query($sql);
        $stats['metodos_activos'] = $result[0]['activos'];

        // Métodos inactivos
        $stats['metodos_inactivos'] = $stats['total_metodos'] - $stats['metodos_activos'];

        return $stats;
    }

    /**
     * Verificar si está en uso en reservas o pagos
     */
    public function isInUse($id)
    {
        // Verificar en tabla pago si existe
        $sql = "SELECT COUNT(*) as total FROM pago WHERE rela_metododepago = ?";
        $result = $this->query($sql, [$id]);
        
        return $result[0]['total'] > 0;
    }

    /**
     * Sanitizar y preparar datos para inserción/actualización
     */
    public function sanitizeData($data)
    {
        $sanitized = [];

        if (isset($data['metododepago_descripcion'])) {
            $sanitized['metododepago_descripcion'] = trim($data['metododepago_descripcion']);
            // Capitalizar primera letra de cada palabra
            $sanitized['metododepago_descripcion'] = ucwords(strtolower($sanitized['metododepago_descripcion']));
        }

        if (isset($data['metododepago_estado'])) {
            $sanitized['metododepago_estado'] = (int) $data['metododepago_estado'];
        }

        return $sanitized;
    }
}