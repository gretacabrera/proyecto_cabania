<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de perfiles de usuario
 */
class Perfil extends Model
{
    protected $table = 'perfiles';
    protected $primaryKey = 'id_perfil';

    /**
     * Buscar perfiles
     */
    public function search($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->findAll(
            "perfil_estado = 1 AND (perfil_nombre LIKE '%{$query}%' OR perfil_descripcion LIKE '%{$query}%')",
            "perfil_nombre ASC",
            "{$perPage} OFFSET {$offset}"
        );
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($query = null, $perPage = 10)
    {
        if ($query) {
            $total = $this->count("perfil_estado = 1 AND (perfil_nombre LIKE '%{$query}%' OR perfil_descripcion LIKE '%{$query}%')");
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
            'perfil_nombre' => $newName,
            'perfil_descripcion' => $perfil['perfil_descripcion'] . ' (Copia)',
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