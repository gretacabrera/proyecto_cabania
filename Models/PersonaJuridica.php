<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad PersonaJuridica
 * Gestiona personas jurídicas (empresas con CUIT)
 */
class PersonaJuridica extends Model
{
    protected $table = 'personajuridica';
    protected $primaryKey = 'id_personajuridica';

    /**
     * Obtener personas jurídicas con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['personajuridica_cuit'])) {
            $where .= " AND pj.personajuridica_cuit LIKE ?";
            $params[] = '%' . $filters['personajuridica_cuit'] . '%';
        }
        
        if (!empty($filters['persona_email'])) {
            $where .= " AND p.persona_email LIKE ?";
            $params[] = '%' . $filters['persona_email'] . '%';
        }
        
        $sql = "SELECT pj.*,
                       p.persona_email,
                       p.persona_telefono,
                       p.persona_estado
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE $where
                ORDER BY pj.personajuridica_cuit ASC";
        
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
                     FROM personajuridica pj
                     INNER JOIN persona p ON pj.rela_persona = p.id_persona
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
     * Buscar persona jurídica con datos completos
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT pj.*,
                       p.persona_email,
                       p.persona_telefono,
                       p.persona_estado
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE pj.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Buscar por CUIT
     */
    public function findByCUIT($cuit)
    {
        $sql = "SELECT pj.*,
                       p.persona_email,
                       p.persona_telefono
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE pj.personajuridica_cuit = ?
                LIMIT 1";
        
        $result = $this->query($sql, [$cuit]);
        return $result->fetch_assoc();
    }

    /**
     * Buscar por persona ID
     */
    public function findByPersona($personaId)
    {
        $sql = "SELECT pj.*,
                       p.persona_email,
                       p.persona_telefono
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE pj.rela_persona = ?
                LIMIT 1";
        
        $result = $this->query($sql, [$personaId]);
        return $result->fetch_assoc();
    }

    /**
     * Validar CUIT argentino
     * Formato: 11 dígitos (XX-XXXXXXXX-X)
     */
    public function validarCUIT($cuit)
    {
        // Remover guiones si existen
        $cuitLimpio = str_replace('-', '', $cuit);
        
        // CUIT debe tener exactamente 10 dígitos numéricos
        if (!preg_match('/^\d{10,11}$/', $cuitLimpio)) {
            return [
                'valido' => false,
                'mensaje' => 'CUIT debe contener 10 u 11 dígitos numéricos'
            ];
        }
        
        // Validación básica de formato
        if (strlen($cuitLimpio) == 11) {
            // Validar dígito verificador (algoritmo simplificado)
            $digitoVerificador = (int) substr($cuitLimpio, -1);
            $base = substr($cuitLimpio, 0, 10);
            
            $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
            $suma = 0;
            
            for ($i = 0; $i < 10; $i++) {
                $suma += (int) $base[$i] * $multiplicadores[$i];
            }
            
            $resto = $suma % 11;
            $verificador = 11 - $resto;
            
            if ($verificador == 11) $verificador = 0;
            if ($verificador == 10) $verificador = 9;
            
            if ($verificador != $digitoVerificador) {
                return [
                    'valido' => false,
                    'mensaje' => 'CUIT inválido: dígito verificador incorrecto'
                ];
            }
        }
        
        return [
            'valido' => true,
            'mensaje' => 'CUIT válido'
        ];
    }

    /**
     * Verificar si CUIT existe (sin contar el ID actual)
     */
    public function cuitExiste($cuit, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE personajuridica_cuit = ?";
        $params = [$cuit];
        
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
     * Formatear CUIT para visualización
     */
    public function formatearCUIT($cuit)
    {
        // Remover caracteres no numéricos
        $cuitLimpio = preg_replace('/[^0-9]/', '', $cuit);
        
        if (strlen($cuitLimpio) == 11) {
            return substr($cuitLimpio, 0, 2) . '-' . 
                   substr($cuitLimpio, 2, 8) . '-' . 
                   substr($cuitLimpio, 10, 1);
        }
        
        return $cuit;
    }

    /**
     * Exportar todos sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['personajuridica_cuit'])) {
            $where .= " AND pj.personajuridica_cuit LIKE ?";
            $params[] = '%' . $filters['personajuridica_cuit'] . '%';
        }
        
        $sql = "SELECT pj.*,
                       p.persona_email,
                       p.persona_telefono,
                       p.persona_estado
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE $where
                ORDER BY pj.personajuridica_cuit ASC";
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM personajuridica pj
                     INNER JOIN persona p ON pj.rela_persona = p.id_persona
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
        
        $empresas = [];
        while ($row = $result->fetch_assoc()) {
            $empresas[] = $row;
        }
        
        return [
            'data' => $empresas,
            'total' => $total
        ];
    }

    /**
     * Obtener estadísticas de empresas
     */
    public function getEstadisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_empresas,
                    COUNT(DISTINCT p.persona_email) as emails_unicos,
                    COUNT(DISTINCT p.persona_telefono) as telefonos_unicos
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE p.persona_estado = 1";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener empresas activas
     */
    public function getEmpresasActivas()
    {
        $sql = "SELECT pj.*,
                       p.persona_email,
                       p.persona_telefono
                FROM personajuridica pj
                INNER JOIN persona p ON pj.rela_persona = p.id_persona
                WHERE p.persona_estado = 1
                ORDER BY pj.personajuridica_cuit";
        
        $result = $this->db->query($sql);
        
        $empresas = [];
        while ($row = $result->fetch_assoc()) {
            $empresas[] = $row;
        }
        
        return $empresas;
    }
}
