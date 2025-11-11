<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de perfiles de usuario
 */
class Perfil extends Model
{
    protected $table = 'perfil';
    protected $primaryKey = 'id_perfil';

    /**
     * Obtener perfiles con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['perfil_descripcion'])) {
            $where .= " AND perfil_descripcion LIKE ?";
            $params[] = '%' . $filters['perfil_descripcion'] . '%';
        }
        
        if (isset($filters['perfil_estado']) && $filters['perfil_estado'] !== '') {
            $where .= " AND perfil_estado = ?";
            $params[] = (int) $filters['perfil_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "perfil_descripcion ASC", $params);
    }

    /**
     * Obtener todos los perfiles con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['perfil_descripcion'])) {
            $where .= " AND perfil_descripcion LIKE ?";
            $params[] = '%' . $filters['perfil_descripcion'] . '%';
        }
        
        if (isset($filters['perfil_estado']) && $filters['perfil_estado'] !== '') {
            $where .= " AND perfil_estado = ?";
            $params[] = (int) $filters['perfil_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY perfil_descripcion ASC";
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
     * Obtener perfiles con paginación usando parámetros preparados
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
     * @param string $sql
     * @param array $params
     * @return \mysqli_result
     * @throws \Exception
     */
    private function queryWithParams($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \Exception("Error preparando consulta: " . ($this->db->error ?? 'Desconocido'));
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception("Error ejecutando consulta: " . ($stmt->error ?? 'Desconocido'));
        }
        
        return $stmt->get_result();
    }

    /**
     * Obtener estadísticas del perfil
     */
    public function getStatistics($id)
    {
        $stats = [
            'usuarios_activos' => 0,
            'usuarios_totales' => 0,
            'modulos_asignados' => 0,
            'porcentaje_uso' => 0
        ];

        // Contar usuarios totales del perfil
        $sqlTotal = "SELECT COUNT(*) as total FROM usuario WHERE rela_perfil = ?";
        $stmtTotal = $this->db->prepare($sqlTotal);
        $stmtTotal->bind_param('i', $id);
        $stmtTotal->execute();
        $resultTotal = $stmtTotal->get_result();
        $rowTotal = $resultTotal->fetch_assoc();
        $stats['usuarios_totales'] = (int) $rowTotal['total'];

        // Contar usuarios activos del perfil
        $sqlActivos = "SELECT COUNT(*) as total FROM usuario WHERE rela_perfil = ? AND usuario_estado = 1";
        $stmtActivos = $this->db->prepare($sqlActivos);
        $stmtActivos->bind_param('i', $id);
        $stmtActivos->execute();
        $resultActivos = $stmtActivos->get_result();
        $rowActivos = $resultActivos->fetch_assoc();
        $stats['usuarios_activos'] = (int) $rowActivos['total'];

        // Contar módulos asignados al perfil
        $sqlModulos = "SELECT COUNT(*) as total FROM perfil_modulo WHERE rela_perfil = ?";
        $stmtModulos = $this->db->prepare($sqlModulos);
        $stmtModulos->bind_param('i', $id);
        $stmtModulos->execute();
        $resultModulos = $stmtModulos->get_result();
        $rowModulos = $resultModulos->fetch_assoc();
        $stats['modulos_asignados'] = (int) $rowModulos['total'];

        // Calcular porcentaje de uso (módulos asignados / total módulos disponibles)
        $sqlTotalModulos = "SELECT COUNT(*) as total FROM modulo WHERE modulo_estado = 1";
        $resultTotalModulos = $this->db->query($sqlTotalModulos);
        $rowTotalModulos = $resultTotalModulos->fetch_assoc();
        $totalModulos = (int) $rowTotalModulos['total'];

        if ($totalModulos > 0) {
            $stats['porcentaje_uso'] = round(($stats['modulos_asignados'] / $totalModulos) * 100);
        }

        return $stats;
    }

    /**
     * Buscar perfiles
     */
    public function search($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->findAll(
            "perfil_estado = 1 AND perfil_descripcion LIKE '%{$query}%'",
            "perfil_descripcion ASC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Listar perfiles paginados
     */
    public function listPaginated($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->findAll(
            "perfil_estado = 1",
            "perfil_descripcion ASC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($query = null, $perPage = 10)
    {
        if ($query) {
            $total = $this->count("perfil_estado = 1 AND perfil_descripcion LIKE '%{$query}%'");
        } else {
            $total = $this->count("perfil_estado = 1");
        }
        
        return ceil($total / $perPage);
    }

    /**
     * Encontrar perfil con módulos
     */
    public function findWithModules($id)
    {
        $perfil = $this->find($id);
        if ($perfil) {
            $sql = "SELECT m.* FROM modulos m
                    INNER JOIN perfiles_modulos pm ON m.id_modulo = pm.rela_modulo
                    WHERE pm.rela_perfil = {$id} AND m.modulo_estado = 1";
            
            $result = $this->db->query($sql);
            $modulos = [];
            while ($row = $result->fetch_assoc()) {
                $modulos[] = $row;
            }
            
            $perfil['modulos'] = $modulos;
        }
        
        return $perfil;
    }

    /**
     * Obtener módulos disponibles
     */
    public function getAvailableModules()
    {
        return $this->findAll("1=1", "modulo_nombre ASC"); // Debería ser tabla modulos
    }

    /**
     * Asignar módulos al perfil
     */
    public function assignModules($perfilId, $modulosIds)
    {
        // Primero eliminar asignaciones existentes
        $sql = "DELETE FROM perfiles_modulos WHERE rela_perfil = {$perfilId}";
        $this->db->query($sql);
        
        // Asignar nuevos módulos
        foreach ($modulosIds as $moduloId) {
            $sql = "INSERT INTO perfiles_modulos (rela_perfil, rela_modulo) VALUES ({$perfilId}, {$moduloId})";
            $this->db->query($sql);
        }
        
        return true;
    }

    /**
     * Actualizar módulos del perfil
     */
    public function updateModules($perfilId, $modulosIds)
    {
        return $this->assignModules($perfilId, $modulosIds);
    }

    /**
     * Verificar si tiene usuarios activos
     */
    public function hasActiveUsers($id)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE rela_perfil = {$id} AND usuario_estado = 1";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'] > 0;
    }

    /**
     * Obtener usuarios del perfil
     */
    public function getUsers($id)
    {
        $sql = "SELECT u.*, p.persona_nombre, p.persona_apellido 
                FROM usuarios u
                INNER JOIN personas p ON u.rela_persona = p.id_persona
                WHERE u.rela_perfil = {$id}";
        
        $result = $this->db->query($sql);
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        
        return $usuarios;
    }

    /**
     * Clonar perfil
     */
    public function clonePerfil($id, $newName)
    {
        $perfil = $this->findWithModules($id);
        if (!$perfil) {
            return false;
        }
        
        // Crear nuevo perfil
        $newPerfilData = [
            'perfil_descripcion' => $newName,
            'perfil_estado' => 1
        ];
        
        $newPerfilId = $this->create($newPerfilData);
        
        if ($newPerfilId && isset($perfil['modulos'])) {
            $modulosIds = array_column($perfil['modulos'], 'id_modulo');
            $this->assignModules($newPerfilId, $modulosIds);
        }
        
        return $newPerfilId;
    }
}