<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de comentarios
 */
class Comentario extends Model
{
    protected $table = 'comentario';
    protected $primaryKey = 'id_comentario';

    /**
     * Buscar comentarios con filtros
     */
    public function search($filters, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $where = "1=1";
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $where .= " AND (comentario_titulo LIKE '%{$search}%' OR comentario_contenido LIKE '%{$search}%')";
        }
        
        if (!empty($filters['estado'])) {
            $where .= " AND comentario_estado = " . intval($filters['estado']);
        }
        
        return $this->findAll(
            $where,
            "comentario_fecha DESC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = "1=1";
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $where .= " AND (comentario_titulo LIKE '%{$search}%' OR comentario_contenido LIKE '%{$search}%')";
        }
        
        if (!empty($filters['estado'])) {
            $where .= " AND comentario_estado = " . intval($filters['estado']);
        }
        
        $total = $this->count($where);
        return ceil($total / $perPage);
    }

    /**
     * Obtener comentario con relaciones
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT c.*, r.reserva_codigo, pf.personafisica_nombre, pf.personafisica_apellido,
                       cab.cabania_nombre
                FROM {$this->table} c
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE c.{$this->primaryKey} = {$id}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener reservas para selección
     */
    public function getReservas()
    {
        $sql = "SELECT r.id_reserva, r.reserva_codigo, c.cabania_nombre, pf.personafisica_nombre, pf.personafisica_apellido
                FROM reserva r
                INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                INNER JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                INNER JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                WHERE er.estadoreserva_estado = 1
                ORDER BY r.reserva_fhinicio DESC";
        
        $result = $this->db->query($sql);
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Obtener personas para selección
     */
    public function getPersonas()
    {
        $sql = "SELECT p.*, pf.personafisica_nombre, pf.personafisica_apellido
                FROM persona p
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                WHERE p.rela_estadopersona = 1 
                ORDER BY pf.personafisica_nombre, pf.personafisica_apellido";
        $result = $this->db->query($sql);
        
        $personas = [];
        while ($row = $result->fetch_assoc()) {
            $personas[] = $row;
        }
        
        return $personas;
    }

    /**
     * Obtener comentarios aprobados (para vista pública)
     */
    public function getApproved($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, pf.personafisica_nombre, pf.personafisica_apellido, cab.cabania_nombre
                FROM {$this->table} c
                LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE c.comentario_estado = 2
                ORDER BY c.comentario_fecha DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $result = $this->db->query($sql);
        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            $comentarios[] = $row;
        }
        
        return $comentarios;
    }

    /**
     * Marcar como moderado
     */
    public function moderate($id, $estado, $observaciones = '')
    {
        $data = [
            'comentario_estado' => $estado,
            'comentario_observaciones' => $observaciones,
            'comentario_fecha_moderacion' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $data);
    }

    /**
     * Obtener comentarios de usuario con filtros (migrado desde Views)
     */
    public function getComentariosUsuarioConFiltros($nombreUsuario, $filtros = [], $pagina = 1, $registrosPorPagina = 10)
    {
        // Primero obtener el id_persona del usuario
        $sqlUsuario = "SELECT rela_persona FROM usuario WHERE usuario_nombre = '" . addslashes($nombreUsuario) . "' LIMIT 1";
        $resultUsuario = $this->db->query($sqlUsuario);
        
        if (!$resultUsuario) {
            return $this->paginateCustomQuery("SELECT c.* FROM comentario c WHERE 1=0", "SELECT COUNT(*) as total FROM comentario c WHERE 1=0", $pagina, $registrosPorPagina);
        }
        
        $usuario = $resultUsuario->fetch_assoc();
        if (!$usuario) {
            return $this->paginateCustomQuery("SELECT c.* FROM comentario c WHERE 1=0", "SELECT COUNT(*) as total FROM comentario c WHERE 1=0", $pagina, $registrosPorPagina);
        }
        
        $idPersonaUsuario = $usuario['rela_persona'];
        
        $whereConditions = [
            "c.comentario_estado = 1",
            "p.id_persona = " . intval($idPersonaUsuario)
        ];

        // Aplicar filtros
        if (!empty($filtros['fecha_desde'])) {
            $whereConditions[] = "DATE(c.comentario_fechahora) >= '" . addslashes($filtros['fecha_desde']) . "'";
        }
        if (!empty($filtros['fecha_hasta'])) {
            $whereConditions[] = "DATE(c.comentario_fechahora) <= '" . addslashes($filtros['fecha_hasta']) . "'";
        }
        if (isset($filtros['puntuacion']) && $filtros['puntuacion'] > 0 && $filtros['puntuacion'] <= 5) {
            $whereConditions[] = "c.comentario_puntuacion = " . intval($filtros['puntuacion']);
        }
        if (isset($filtros['comentario_estado']) && $filtros['comentario_estado'] != "") {
            $whereConditions[0] = "c.comentario_estado = " . intval($filtros['comentario_estado']);
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // Query para contar registros
        $queryCount = "SELECT COUNT(*) as total
                       FROM comentario c
                       INNER JOIN huesped h ON c.rela_huesped = h.id_huesped
                       INNER JOIN persona p ON h.rela_persona = p.id_persona
                       LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                       LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                       LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                       $whereClause";

        // Query base para obtener registros
        $queryBase = "SELECT c.*,
                            pf.personafisica_nombre, pf.personafisica_apellido,
                            cab.cabania_nombre,
                            r.reserva_fhinicio,
                            r.reserva_fhfin
                     FROM comentario c
                     INNER JOIN huesped h ON c.rela_huesped = h.id_huesped
                     INNER JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                     LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                     LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                     $whereClause
                     ORDER BY c.comentario_fechahora DESC";

        return $this->paginateCustomQuery($queryBase, $queryCount, $pagina, $registrosPorPagina);
    }

    /**
     * Obtener comentario para edición (migrado desde Views)
     */
    public function getComentarioParaEdicion($idComentario, $nombreUsuario, $incluirEliminados = false)
    {
        $sql = "SELECT c.*, 
                       pf.personafisica_nombre, pf.personafisica_apellido,
                       cab.cabania_nombre,
                       r.reserva_fhinicio, r.reserva_fhfin, r.id_reserva,
                       p.id_persona as persona_huesped
                FROM comentario c
                INNER JOIN huesped h ON c.rela_huesped = h.id_huesped
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE c.id_comentario = " . intval($idComentario);
        
        // Solo filtrar por estado si no se solicita incluir eliminados
        if (!$incluirEliminados) {
            $sql .= " AND c.comentario_estado = 1";
        }
        
        $sql .= " LIMIT 1";

        error_log("=== DEBUG getComentarioParaEdicion ===");
        error_log("incluirEliminados: " . ($incluirEliminados ? 'true' : 'false'));
        error_log("SQL: " . $sql);
        
        $result = $this->db->query($sql);
        $data = $result ? $result->fetch_assoc() : null;
        
        error_log("Resultado: " . ($data ? "ENCONTRADO (persona_huesped=" . ($data['persona_huesped'] ?? 'NULL') . ", estado=" . ($data['comentario_estado'] ?? 'NULL') . ")" : "NULL"));
        
        return $data;
    }

    /**
     * Verificar si comentario pertenece a usuario (migrado desde Views)
     */
    public function verificarComentarioUsuario($idComentario, $nombreUsuario)
    {
        error_log("=== DEBUG verificarComentarioUsuario ===");
        error_log("idComentario: " . $idComentario);
        error_log("nombreUsuario: " . $nombreUsuario);
        
        // Obtener id_persona del usuario
        $sqlUsuario = "SELECT rela_persona FROM usuario WHERE usuario_nombre = '" . addslashes($nombreUsuario) . "' LIMIT 1";
        error_log("SQL Usuario: " . $sqlUsuario);
        $resultUsuario = $this->db->query($sqlUsuario);
        
        if (!$resultUsuario) {
            error_log("ERROR: Query usuario falló");
            return false;
        }
        
        $usuario = $resultUsuario->fetch_assoc();
        if (!$usuario) {
            error_log("ERROR: Usuario no encontrado");
            return false;
        }
        
        $idPersonaUsuario = $usuario['rela_persona'];
        error_log("ID Persona del usuario: " . $idPersonaUsuario);
        
        // Obtener comentario INCLUYENDO los eliminados (para poder verificar permisos)
        $comentario = $this->getComentarioParaEdicion($idComentario, $nombreUsuario, true);
        
        if (!$comentario) {
            error_log("ERROR: Comentario no encontrado");
            return false;
        }
        
        error_log("ID Persona del huesped: " . ($comentario['persona_huesped'] ?? 'NULL'));
        
        $resultado = $comentario['persona_huesped'] == $idPersonaUsuario;
        error_log("Resultado comparación: " . ($resultado ? "TRUE" : "FALSE"));
        
        return $resultado;
    }

    /**
     * Obtener información de reserva para nuevo comentario (migrado desde Views)
     */
    public function getInformacionReserva($idReserva)
    {
        $sql = "SELECT r.*, cab.cabania_nombre 
                FROM reserva r
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE r.id_reserva = " . intval($idReserva);

        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Obtener comentarios de una reserva específica
     */
    public function getComentariosByReserva($idReserva)
    {
        $sql = "SELECT c.*, pf.personafisica_nombre, pf.personafisica_apellido
                FROM comentario c
                LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN personafisica pf ON p.rela_personafisica = pf.id_personafisica
                WHERE c.rela_reserva = " . intval($idReserva) . "
                AND c.comentario_estado = 1
                ORDER BY c.comentario_fechahora DESC";
        
        $result = $this->db->query($sql);
        $comentarios = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $comentarios[] = $row;
            }
            $result->free(); // Liberar resultado explícitamente
        }
        
        return $comentarios;
    }

    /**
     * Paginación personalizada para queries complejas
     */
    protected function paginateCustomQuery($queryBase, $queryCount, $pagina, $registrosPorPagina)
    {
        $offset = ($pagina - 1) * $registrosPorPagina;
        
        // Contar total
        $countResult = $this->db->query($queryCount);
        $total = $countResult->fetch_assoc()['total'];
        
        // Obtener registros paginados
        $queryPaginada = $queryBase . " LIMIT $registrosPorPagina OFFSET $offset";
        $result = $this->db->query($queryPaginada);
        
        $registros = [];
        while ($row = $result->fetch_assoc()) {
            $registros[] = $row;
        }
        
        return [
            'registros' => $registros,
            'paginacion' => [
                'total_registros' => $total,
                'total_paginas' => ceil($total / $registrosPorPagina),
                'pagina_actual' => $pagina,
                'registros_por_pagina' => $registrosPorPagina,
                'desde' => ($offset + 1),
                'hasta' => min($offset + $registrosPorPagina, $total)
            ]
        ];
    }

    /**
     * Sobrescribir softDelete para usar el campo correcto
     */
    public function softDelete($id, $field = 'comentario_estado')
    {
        return $this->update($id, [$field => 0]);
    }

    /**
     * Sobrescribir restore para usar el campo correcto
     */
    public function restore($id, $field = 'comentario_estado')
    {
        return $this->update($id, [$field => 1]);
    }
}