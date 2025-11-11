<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Modulo extends Model
{
    protected $table = 'modulo';
    protected $primaryKey = 'id_modulo';

    /**
     * Obtiene módulos con detalles paginados y filtrados
     *
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @param array $filters Filtros a aplicar
     * @return array Datos paginados
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Filtro por descripción
        if (!empty($filters['modulo_descripcion'])) {
            $where .= " AND m.modulo_descripcion LIKE ?";
            $params[] = '%' . $filters['modulo_descripcion'] . '%';
        }
        
        // Filtro por ruta
        if (!empty($filters['modulo_ruta'])) {
            $where .= " AND m.modulo_ruta LIKE ?";
            $params[] = '%' . $filters['modulo_ruta'] . '%';
        }
        
        // Filtro por menú
        if (!empty($filters['rela_menu'])) {
            $where .= " AND m.rela_menu = ?";
            $params[] = (int) $filters['rela_menu'];
        }
        
        // Filtro por estado
        if (isset($filters['modulo_estado']) && $filters['modulo_estado'] !== '') {
            $where .= " AND m.modulo_estado = ?";
            $params[] = (int) $filters['modulo_estado'];
        }
        
        return $this->paginateWithJoin($page, $perPage, $where, "m.modulo_descripcion ASC", $params);
    }

    /**
     * Método privado para paginación con JOIN
     */
    private function paginateWithJoin($page, $perPage, $where, $orderBy, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT m.*, 
                       me.menu_nombre
                FROM {$this->table} m
                LEFT JOIN menu me ON m.rela_menu = me.id_menu
                WHERE {$where}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";
        
        $records = $this->queryWithParams($sql, $params);
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} m
                     LEFT JOIN menu me ON m.rela_menu = me.id_menu
                     WHERE {$where}";
        
        $countResult = $this->queryWithParams($countSql, $params);
        $total = $countResult[0]['total'] ?? 0;
        
        return [
            'data' => $records,
            'total' => (int) $total,
            'current_page' => (int) $page,
            'total_pages' => ceil($total / $perPage),
            'per_page' => (int) $perPage,
            'offset' => (int) $offset,
            'limit' => (int) $perPage
        ];
    }

    /**
     * Ejecuta una consulta con parámetros preparados
     */
    private function queryWithParams($sql, $params = [])
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare($sql);
        
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
        
        $stmt->close();
        return $records;
    }

    /**
     * Obtiene todos los módulos con detalles para exportación (sin paginación)
     *
     * @param array $filters Filtros a aplicar
     * @return array Todos los registros que cumplen los filtros
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar mismos filtros que getWithDetails
        if (!empty($filters['modulo_descripcion'])) {
            $where .= " AND m.modulo_descripcion LIKE ?";
            $params[] = '%' . $filters['modulo_descripcion'] . '%';
        }
        
        if (!empty($filters['modulo_ruta'])) {
            $where .= " AND m.modulo_ruta LIKE ?";
            $params[] = '%' . $filters['modulo_ruta'] . '%';
        }
        
        if (!empty($filters['rela_menu'])) {
            $where .= " AND m.rela_menu = ?";
            $params[] = (int) $filters['rela_menu'];
        }
        
        if (isset($filters['modulo_estado']) && $filters['modulo_estado'] !== '') {
            $where .= " AND m.modulo_estado = ?";
            $params[] = (int) $filters['modulo_estado'];
        }
        
        $sql = "SELECT m.*, 
                       me.menu_nombre
                FROM {$this->table} m
                LEFT JOIN menu me ON m.rela_menu = me.id_menu
                WHERE {$where}
                ORDER BY m.modulo_descripcion ASC";
        
        $records = $this->queryWithParams($sql, $params);
        
        return [
            'data' => $records,
            'total' => count($records)
        ];
    }

    /**
     * Obtiene estadísticas de un módulo específico
     *
     * @param int $moduloId ID del módulo
     * @return array Estadísticas del módulo
     */
    public function getStatistics($moduloId)
    {
        $stats = [];
        
        // Total de perfiles asignados a este módulo
        $stats['perfiles_asignados'] = $this->getPerfilesAsignados($moduloId);
        
        // Total de usuarios con permiso (a través de perfiles asignados)
        $stats['usuarios_con_permiso'] = $this->getUsuariosConPermiso($moduloId);
        
        // Total de perfiles en el sistema
        $stats['total_perfiles'] = $this->getTotalPerfiles();
        
        // Porcentaje de uso
        $stats['porcentaje_uso'] = $stats['total_perfiles'] > 0 
            ? round(($stats['perfiles_asignados'] / $stats['total_perfiles']) * 100, 2) 
            : 0;
        
        // Fecha de creación (si existe campo created_at, sino NULL)
        $stats['fecha_creacion'] = $this->getFechaCreacion($moduloId);
        
        return $stats;
    }

    /**
     * Cuenta perfiles asignados a un módulo
     */
    private function getPerfilesAsignados($moduloId)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $sql = "SELECT COUNT(DISTINCT rela_perfil) as total 
                FROM perfil_modulo 
                WHERE rela_modulo = ? 
                AND perfilmodulo_estado = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $moduloId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Cuenta usuarios con permiso al módulo (a través de perfiles asignados)
     */
    private function getUsuariosConPermiso($moduloId)
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $sql = "SELECT COUNT(DISTINCT u.id_usuario) as total 
                FROM usuario u
                INNER JOIN perfil_modulo pm ON u.rela_perfil = pm.rela_perfil
                WHERE pm.rela_modulo = ? 
                AND pm.perfilmodulo_estado = 1
                AND u.usuario_estado = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $moduloId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Cuenta total de perfiles en el sistema
     */
    private function getTotalPerfiles()
    {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $sql = "SELECT COUNT(*) as total FROM perfil WHERE perfil_estado = 1";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtiene fecha de creación del módulo (si existe)
     */
    private function getFechaCreacion($moduloId)
    {
        // Como la tabla no tiene campo created_at, retornamos NULL
        // En futuras versiones se podría agregar auditoría
        return null;
    }
}
