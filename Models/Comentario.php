<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de comentarios
 */
class Comentario extends Model
{
    protected $table = 'comentarios';
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
        $sql = "SELECT c.*, r.reserva_codigo, p.persona_nombre, p.persona_apellido,
                       cab.cabania_nombre
                FROM {$this->table} c
                LEFT JOIN reservas r ON c.rela_reserva = r.id_reserva
                LEFT JOIN personas p ON c.rela_persona = p.id_persona
                LEFT JOIN cabanias cab ON r.rela_cabania = cab.id_cabania
                WHERE c.{$this->primaryKey} = {$id}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Obtener reservas para selección
     */
    public function getReservas()
    {
        $sql = "SELECT r.id_reserva, r.reserva_codigo, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                FROM reserva r
                INNER JOIN cabania c ON r.rela_cabania = c.id_cabania
                INNER JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                INNER JOIN persona p ON h.rela_persona = p.id_persona
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
        $sql = "SELECT * FROM personas WHERE rela_estadopersona = 1 ORDER BY persona_nombre, persona_apellido";
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
        
        $sql = "SELECT c.*, p.persona_nombre, p.persona_apellido, cab.cabania_nombre
                FROM {$this->table} c
                LEFT JOIN personas p ON c.rela_persona = p.id_persona
                LEFT JOIN reservas r ON c.rela_reserva = r.id_reserva
                LEFT JOIN cabanias cab ON r.rela_cabania = cab.id_cabania
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
        $whereConditions = [
            "c.comentario_estado = 1",
            "u.usuario_nombre = '" . addslashes($nombreUsuario) . "'"
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
                       LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                       LEFT JOIN persona p ON h.rela_persona = p.id_persona
                       LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                       LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                       LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                       $whereClause";

        // Query base para obtener registros
        $queryBase = "SELECT c.*,
                            p.persona_nombre, p.persona_apellido,
                            cab.cabania_nombre,
                            r.reserva_fechainicio as reserva_fhinicio,
                            r.reserva_fechafin as reserva_fhfin
                     FROM comentario c
                     LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                     LEFT JOIN persona p ON h.rela_persona = p.id_persona
                     LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                     LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                     LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                     $whereClause
                     ORDER BY c.comentario_fechahora DESC";

        return $this->paginateCustomQuery($queryBase, $queryCount, $pagina, $registrosPorPagina);
    }

    /**
     * Obtener comentario para edición (migrado desde Views)
     */
    public function getComentarioParaEdicion($idComentario, $nombreUsuario)
    {
        $sql = "SELECT c.*, 
                       p.persona_nombre, p.persona_apellido,
                       cab.cabania_nombre,
                       r.reserva_fechainicio, r.reserva_fechafin, r.id_reserva
                FROM comentario c
                LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                LEFT JOIN persona p ON h.rela_persona = p.id_persona
                LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                WHERE c.id_comentario = " . intval($idComentario) . "
                AND c.comentario_estado = 1
                AND u.usuario_nombre = '" . addslashes($nombreUsuario) . "'
                LIMIT 1";

        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Verificar si comentario pertenece a usuario (migrado desde Views)
     */
    public function verificarComentarioUsuario($idComentario, $nombreUsuario)
    {
        $comentario = $this->getComentarioParaEdicion($idComentario, $nombreUsuario);
        return $comentario !== null;
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
}