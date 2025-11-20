<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad PersonaFisica
 * Gestiona personas físicas (individuos con DNI)
 */
class PersonaFisica extends Model
{
    protected $table = 'personafisica';
    protected $primaryKey = 'id_personafisica';

    /**
     * Obtener personas físicas con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['personafisica_nombre'])) {
            $where .= " AND pf.personafisica_nombre LIKE ?";
            $params[] = '%' . $filters['personafisica_nombre'] . '%';
        }
        
        if (!empty($filters['personafisica_apellido'])) {
            $where .= " AND pf.personafisica_apellido LIKE ?";
            $params[] = '%' . $filters['personafisica_apellido'] . '%';
        }
        
        if (!empty($filters['personafisica_dni'])) {
            $where .= " AND pf.personafisica_dni LIKE ?";
            $params[] = '%' . $filters['personafisica_dni'] . '%';
        }
        
        if (!empty($filters['edad_min'])) {
            $where .= " AND TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) >= ?";
            $params[] = (int) $filters['edad_min'];
        }
        
        if (!empty($filters['edad_max'])) {
            $where .= " AND TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) <= ?";
            $params[] = (int) $filters['edad_max'];
        }
        
        $sql = "SELECT pf.*,
                       p.persona_email,
                       p.persona_telefono,
                       TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) as edad
                FROM personafisica pf
                INNER JOIN persona p ON pf.rela_persona = p.id_persona
                WHERE $where
                ORDER BY pf.personafisica_apellido, pf.personafisica_nombre ASC";
        
        return $this->paginateCustomQuery($sql, $where, $params, $page, $perPage);
    }

    /**
     * Paginación personalizada
     */
    protected function paginateCustomQuery($sql, $where, $params, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM personafisica pf
                     INNER JOIN persona p ON pf.rela_persona = p.id_persona
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros paginados
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($paginatedSql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Buscar persona física con datos completos
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT pf.*,
                       p.persona_email,
                       p.persona_telefono,
                       p.persona_estado,
                       TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) as edad
                FROM personafisica pf
                INNER JOIN persona p ON pf.rela_persona = p.id_persona
                WHERE pf.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Buscar por DNI
     */
    public function findByDNI($dni)
    {
        $sql = "SELECT pf.*,
                       p.persona_email,
                       p.persona_telefono,
                       TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) as edad
                FROM personafisica pf
                INNER JOIN persona p ON pf.rela_persona = p.id_persona
                WHERE pf.personafisica_dni = ?
                LIMIT 1";
        
        $result = $this->query($sql, [$dni]);
        return $result->fetch_assoc();
    }

    /**
     * Buscar por persona ID
     */
    public function findByPersona($personaId)
    {
        $sql = "SELECT pf.*,
                       p.persona_email,
                       p.persona_telefono,
                       TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) as edad
                FROM personafisica pf
                INNER JOIN persona p ON pf.rela_persona = p.id_persona
                WHERE pf.rela_persona = ?
                LIMIT 1";
        
        $result = $this->query($sql, [$personaId]);
        return $result->fetch_assoc();
    }

    /**
     * Validar DNI argentino
     */
    public function validarDNI($dni)
    {
        // DNI debe ser numérico y tener entre 7 y 8 dígitos
        if (!preg_match('/^\d{7,8}$/', $dni)) {
            return [
                'valido' => false,
                'mensaje' => 'DNI debe contener 7 u 8 dígitos numéricos'
            ];
        }
        
        return [
            'valido' => true,
            'mensaje' => 'DNI válido'
        ];
    }

    /**
     * Verificar si DNI existe (sin contar el ID actual)
     */
    public function dniExiste($dni, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE personafisica_dni = ?";
        $params = [$dni];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Calcular edad
     */
    public function calcularEdad($fechaNacimiento)
    {
        $fechaNac = new \DateTime($fechaNacimiento);
        $hoy = new \DateTime();
        $edad = $hoy->diff($fechaNac);
        
        return $edad->y;
    }

    /**
     * Obtener nombre completo
     */
    public function getNombreCompleto($personaFisicaId)
    {
        $persona = $this->find($personaFisicaId);
        
        if (!$persona) {
            return null;
        }
        
        return trim($persona['personafisica_nombre'] . ' ' . $persona['personafisica_apellido']);
    }

    /**
     * Exportar todos sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['personafisica_nombre'])) {
            $where .= " AND pf.personafisica_nombre LIKE ?";
            $params[] = '%' . $filters['personafisica_nombre'] . '%';
        }
        
        if (!empty($filters['personafisica_apellido'])) {
            $where .= " AND pf.personafisica_apellido LIKE ?";
            $params[] = '%' . $filters['personafisica_apellido'] . '%';
        }
        
        if (!empty($filters['personafisica_dni'])) {
            $where .= " AND pf.personafisica_dni LIKE ?";
            $params[] = '%' . $filters['personafisica_dni'] . '%';
        }
        
        $sql = "SELECT pf.*,
                       p.persona_email,
                       p.persona_telefono,
                       TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) as edad
                FROM personafisica pf
                INNER JOIN persona p ON pf.rela_persona = p.id_persona
                WHERE $where
                ORDER BY pf.personafisica_apellido, pf.personafisica_nombre ASC";
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM personafisica pf
                     INNER JOIN persona p ON pf.rela_persona = p.id_persona
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $personas = [];
        while ($row = $result->fetch_assoc()) {
            $personas[] = $row;
        }
        
        return [
            'data' => $personas,
            'total' => $total
        ];
    }

    /**
     * Obtener estadísticas demográficas
     */
    public function getEstadisticasDemograficas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_personas,
                    AVG(TIMESTAMPDIFF(YEAR, personafisica_fechanac, CURDATE())) as edad_promedio,
                    MIN(TIMESTAMPDIFF(YEAR, personafisica_fechanac, CURDATE())) as edad_minima,
                    MAX(TIMESTAMPDIFF(YEAR, personafisica_fechanac, CURDATE())) as edad_maxima,
                    COUNT(CASE WHEN personafisica_dni IS NOT NULL THEN 1 END) as con_dni,
                    COUNT(CASE WHEN personafisica_dni IS NULL THEN 1 END) as sin_dni
                FROM personafisica";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Buscar personas por rango de edad
     */
    public function getByRangoEdad($edadMin, $edadMax)
    {
        $sql = "SELECT pf.*,
                       p.persona_email,
                       p.persona_telefono,
                       TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) as edad
                FROM personafisica pf
                INNER JOIN persona p ON pf.rela_persona = p.id_persona
                WHERE TIMESTAMPDIFF(YEAR, pf.personafisica_fechanac, CURDATE()) BETWEEN ? AND ?
                ORDER BY pf.personafisica_apellido, pf.personafisica_nombre";
        
        $result = $this->query($sql, [$edadMin, $edadMax]);
        
        $personas = [];
        while ($row = $result->fetch_assoc()) {
            $personas[] = $row;
        }
        
        return $personas;
    }
}
