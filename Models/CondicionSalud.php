<?php

namespace App\Models;

use App\Core\Model;

class CondicionSalud extends Model
{
    protected $table = 'condicionsalud';
    protected $primaryKey = 'id_condicionsalud';
    
    protected $fields = [
        'condicionsalud_descripcion',
        'condicionsalud_estado'
    ];

    protected $requiredFields = [
        'condicionsalud_descripcion'
    ];

    /**
     * Validar datos de condición de salud
     */
    public function validate($data, $isUpdate = false, $id = null)
    {
        $errors = [];

        // Validar descripción
        if (empty($data['condicionsalud_descripcion'])) {
            $errors[] = 'La descripción de la condición de salud es obligatoria.';
        } else {
            $descripcion = trim($data['condicionsalud_descripcion']);
            
            if (strlen($descripcion) < 3) {
                $errors[] = 'La descripción debe tener al menos 3 caracteres.';
            }
            
            if (strlen($descripcion) > 250) {
                $errors[] = 'La descripción no puede superar los 250 caracteres.';
            }

            // Verificar que no exista otra condición con la misma descripción
            if ($this->existsOtherWithDescription($descripcion, $id)) {
                $errors[] = 'Ya existe una condición de salud con esta descripción.';
            }
        }

        // Validar estado (si se proporciona)
        if (isset($data['condicionsalud_estado'])) {
            if (!in_array($data['condicionsalud_estado'], [0, 1, '0', '1'])) {
                $errors[] = 'El estado debe ser 0 (inactivo) o 1 (activo).';
            }
        }

        return $errors;
    }

    /**
     * Verificar si existe otra condición con la misma descripción
     */
    private function existsOtherWithDescription($descripcion, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE condicionsalud_descripcion = ?";
        $params = [$descripcion];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->query($sql, $params);

        return $result[0]['count'] > 0;
    }

    /**
     * Obtener condiciones de salud activas
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE condicionsalud_estado = 1 ORDER BY condicionsalud_descripcion ASC";
        return $this->query($sql);
    }

    /**
     * Obtener condiciones con filtros y paginación
     */
    public function getWithFilters($filters = [], $page = 1, $limit = 10, $orderBy = 'condicionsalud_descripcion', $orderDir = 'ASC')
    {
        $conditions = ['1 = 1'];
        $params = [];

        // Filtro por descripción
        if (!empty($filters['condicionsalud_descripcion'])) {
            $conditions[] = "condicionsalud_descripcion LIKE ?";
            $params[] = "%" . $filters['condicionsalud_descripcion'] . "%";
        }

        // Filtro por estado
        if (isset($filters['condicionsalud_estado']) && $filters['condicionsalud_estado'] !== '') {
            $conditions[] = "condicionsalud_estado = ?";
            $params[] = $filters['condicionsalud_estado'];
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $limit;

        // Validar orden
        $validOrderBy = ['condicionsalud_descripcion', 'condicionsalud_estado', 'id_condicionsalud'];
        if (!in_array($orderBy, $validOrderBy)) {
            $orderBy = 'condicionsalud_descripcion';
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
     * Cambiar estado de la condición (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $current = $this->find($id);
        if (!$current) {
            return false;
        }

        $newStatus = $current['condicionsalud_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['condicionsalud_estado' => $newStatus]);
    }

    /**
     * Obtener estadísticas de condiciones de salud
     */
    public function getStats()
    {
        $stats = [];

        // Total de condiciones
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->query($sql);
        $stats['total_condiciones'] = $result[0]['total'];

        // Condiciones activas
        $sql = "SELECT COUNT(*) as activas FROM {$this->table} WHERE condicionsalud_estado = 1";
        $result = $this->query($sql);
        $stats['condiciones_activas'] = $result[0]['activas'];

        // Condiciones inactivas
        $stats['condiciones_inactivas'] = $stats['total_condiciones'] - $stats['condiciones_activas'];

        // Condiciones más utilizadas (según relación con huéspedes)
        $sql = "SELECT c.id_condicionsalud, c.condicionsalud_descripcion, 
                       COUNT(hc.id_huespedcondicionsalud) as uso_count
                FROM {$this->table} c
                LEFT JOIN huesped_condicionsalud hc ON c.id_condicionsalud = hc.rela_condicionsalud 
                WHERE c.condicionsalud_estado = 1
                GROUP BY c.id_condicionsalud, c.condicionsalud_descripcion
                ORDER BY uso_count DESC
                LIMIT 10";
        $stats['condiciones_mas_utilizadas'] = $this->query($sql);

        // Distribución de uso mensual
        $sql = "SELECT MONTH(NOW()) as mes, COUNT(hc.id_huespedcondicionsalud) as total
                FROM huesped_condicionsalud hc
                INNER JOIN {$this->table} c ON hc.rela_condicionsalud = c.id_condicionsalud
                WHERE c.condicionsalud_estado = 1
                GROUP BY MONTH(NOW())
                ORDER BY mes";
        $stats['uso_mensual'] = $this->query($sql);

        return $stats;
    }

    /**
     * Buscar condiciones de salud por término
     */
    public function search($term, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE condicionsalud_descripcion LIKE ? 
                AND condicionsalud_estado = 1 
                ORDER BY condicionsalud_descripcion ASC 
                LIMIT ?";
        
        return $this->query($sql, ["%{$term}%", $limit]);
    }

    /**
     * Verificar si la condición está siendo utilizada por huéspedes
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as count FROM huesped_condicionsalud 
                WHERE rela_condicionsalud = ? AND huespedcondicionsalud_estado = 1";
        $result = $this->query($sql, [$id]);
        
        return $result[0]['count'] > 0;
    }

    /**
     * Obtener huéspedes asociados a una condición de salud
     */
    public function getHuespedes($condicionId)
    {
        $sql = "SELECT h.id_huesped, h.huesped_nombre, h.huesped_apellido,
                       hc.huespedcondicionsalud_estado
                FROM huesped_condicionsalud hc
                INNER JOIN huesped h ON hc.rela_huesped = h.id_huesped
                WHERE hc.rela_condicionsalud = ?
                ORDER BY h.huesped_apellido, h.huesped_nombre";
        
        return $this->query($sql, [$condicionId]);
    }

    /**
     * Obtener condiciones críticas (que requieren atención especial)
     * Basado en palabras clave que indican condiciones serias
     */
    public function getCondicionesCriticas()
    {
        $palabrasCriticas = [
            'alergia', 'alergico', 'diabetes', 'diabetico', 'cardiaco', 'corazon',
            'epilepsia', 'epileptico', 'asma', 'asmatico', 'hipertension', 'presion',
            'renal', 'riñon', 'hepatico', 'higado', 'cancer', 'tumor', 'quimioterapia'
        ];
        
        $conditions = [];
        $params = [];
        
        foreach ($palabrasCriticas as $palabra) {
            $conditions[] = "condicionsalud_descripcion LIKE ?";
            $params[] = "%{$palabra}%";
        }
        
        $whereClause = implode(' OR ', $conditions);
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE ({$whereClause}) AND condicionsalud_estado = 1
                ORDER BY condicionsalud_descripcion ASC";
        
        return $this->query($sql, $params);
    }

    /**
     * Sanitizar y preparar datos para inserción/actualización
     */
    public function sanitizeData($data)
    {
        $sanitized = [];

        if (isset($data['condicionsalud_descripcion'])) {
            $sanitized['condicionsalud_descripcion'] = trim($data['condicionsalud_descripcion']);
            // Capitalizar primera letra de cada palabra
            $sanitized['condicionsalud_descripcion'] = ucwords(strtolower($sanitized['condicionsalud_descripcion']));
        }

        if (isset($data['condicionsalud_estado'])) {
            $sanitized['condicionsalud_estado'] = (int) $data['condicionsalud_estado'];
        }

        return $sanitized;
    }

    /**
     * Eliminar registros inactivos antiguos (soft delete cleanup)
     */
    public function cleanupInactive($daysOld = 365)
    {
        // Esta función podría ser útil para limpieza de mantenimiento
        // Por ahora solo retorna información sobre registros inactivos antiguos
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE condicionsalud_estado = 0";
        
        $result = $this->query($sql);
        
        return $result[0]['count'];
    }

    /**
     * Obtener condiciones agrupadas por primera letra
     */
    public function getGroupedByLetter()
    {
        $sql = "SELECT UPPER(LEFT(condicionsalud_descripcion, 1)) as letra,
                       COUNT(*) as cantidad
                FROM {$this->table}
                WHERE condicionsalud_estado = 1
                GROUP BY letra
                ORDER BY letra";
        
        return $this->query($sql);
    }
}