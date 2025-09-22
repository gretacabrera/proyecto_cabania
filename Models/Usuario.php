<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Usuario
 */
class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password)
    {
        $usuario = $this->findWhere("usuario_nombre = ? AND usuario_estado = 1", [$username]);
        
        if ($usuario && password_verify($password, $usuario['usuario_contrasenia'])) {
            return $usuario;
        }
        
        return false;
    }

    /**
     * Obtener usuario con perfil
     */
    public function findWithProfile($id)
    {
        $sql = "SELECT u.*, p.perfil_descripcion, p.perfil_estado
                FROM usuario u
                LEFT JOIN perfil p ON u.rela_perfil = p.id_perfil
                WHERE u.id_usuario = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener usuarios activos con paginación
     */
    public function getActivePaginated($page = 1, $perPage = 10, $search = '')
    {
        $where = "u.usuario_estado = 1";
        
        if ($search) {
            $search = $this->db->escape($search);
            $where .= " AND (u.usuario_nombre LIKE '%$search%' OR p.persona_nombre LIKE '%$search%' OR p.persona_apellido LIKE '%$search%')";
        }
        
        $sql = "SELECT u.*, pe.persona_nombre, pe.persona_apellido, pe.persona_email,
                       pr.perfil_descripcion
                FROM usuario u
                LEFT JOIN persona pe ON u.rela_persona = pe.id_persona
                LEFT JOIN perfil pr ON u.rela_perfil = pr.id_perfil
                WHERE $where
                ORDER BY u.usuario_nombre";
        
        $countSql = "SELECT COUNT(*) as total 
                     FROM usuario u
                     LEFT JOIN persona pe ON u.rela_persona = pe.id_persona
                     LEFT JOIN perfil pr ON u.rela_perfil = pr.id_perfil
                     WHERE $where";
        
        return $this->paginateCustom($sql, $countSql, $page, $perPage);
    }

    /**
     * Crear usuario con hash de contraseña
     */
    public function createUser($data)
    {
        if (isset($data['usuario_clave'])) {
            $data['usuario_contrasenia'] = password_hash($data['usuario_clave'], PASSWORD_DEFAULT);
            unset($data['usuario_clave']); // Remover el campo temporal
        }
        
        return $this->create($data);
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['usuario_contrasenia' => $hashedPassword]);
    }

    /**
     * Verificar si el usuario existe
     */
    public function userExists($username)
    {
        $usuario = $this->findWhere("usuario_nombre = ?", [$username]);
        return $usuario !== false;
    }

    /**
     * Obtener módulos del usuario
     */
    public function getUserModules($userId)
    {
        $sql = "SELECT m.modulo_ruta, m.modulo_descripcion, men.menu_nombre, m.rela_menu
                FROM modulo m
                LEFT JOIN perfil_modulo pm ON pm.rela_modulo = m.id_modulo
                LEFT JOIN usuario u ON u.rela_perfil = pm.rela_perfil
                LEFT JOIN menu men ON m.rela_menu = men.id_menu
                WHERE u.id_usuario = ?
                AND m.modulo_estado = 1
                AND pm.perfilmodulo_estado = 1
                AND u.usuario_estado = 1
                ORDER BY men.menu_nombre, m.modulo_descripcion";
        
        $result = $this->query($sql, [$userId]);
        
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        
        return $modules;
    }

    /**
     * Paginación personalizada
     */
    protected function paginateCustom($sql, $countSql, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countResult = $this->db->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $result = $this->db->query($paginatedSql);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage)
        ];
    }

    /**
     * Buscar usuarios
     */
    public function search($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->findAll(
            "usuario_estado = 1 AND (usuario_nombre LIKE '%{$query}%')",
            "usuario_nombre ASC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($query = null, $perPage = 10)
    {
        if ($query) {
            $total = $this->count("usuario_estado = 1 AND (usuario_nombre LIKE '%{$query}%')");
        } else {
            $total = $this->count("usuario_estado = 1");
        }
        
        return ceil($total / $perPage);
    }

    /**
     * Buscar por nombre de usuario
     */
    public function findByUsername($username)
    {
        return $this->findWhere("usuario_nombre = ?", [$username]);
    }

    /**
     * Obtener perfiles disponibles
     */
    public function getPerfiles()
    {
        $sql = "SELECT * FROM perfiles WHERE perfil_estado = 1 ORDER BY perfil_nombre";
        $result = $this->db->query($sql);
        
        $perfiles = [];
        while ($row = $result->fetch_assoc()) {
            $perfiles[] = $row;
        }
        
        return $perfiles;
    }

    /**
     * Obtener personas disponibles
     */
    public function getPersonas()
    {
        $sql = "SELECT * FROM personas WHERE persona_estado = 1 ORDER BY persona_nombre, persona_apellido";
        $result = $this->db->query($sql);
        
        $personas = [];
        while ($row = $result->fetch_assoc()) {
            $personas[] = $row;
        }
        
        return $personas;
    }

    /**
     * Obtener usuario con relaciones
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT u.*, p.persona_nombre, p.persona_apellido, p.persona_email,
                       pr.perfil_nombre, pr.perfil_descripcion
                FROM {$this->table} u
                LEFT JOIN personas p ON u.rela_persona = p.id_persona
                LEFT JOIN perfiles pr ON u.rela_perfil = pr.id_perfil
                WHERE u.{$this->primaryKey} = {$id}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }
}