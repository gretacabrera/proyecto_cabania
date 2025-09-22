<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Persona
 */
class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    /**
     * Obtener personas activas
     */
    public function getActive($page = 1, $perPage = 10, $search = '')
    {
        $where = "persona_estado = 1";
        
        if ($search) {
            $search = $this->db->escape($search);
            $where .= " AND (persona_nombre LIKE '%$search%' OR persona_apellido LIKE '%$search%' OR persona_email LIKE '%$search%')";
        }
        
        return $this->paginate($page, $perPage, $where, "persona_apellido, persona_nombre");
    }

    /**
     * Buscar persona por email
     */
    public function findByEmail($email)
    {
        return $this->findWhere("persona_email = ? AND persona_estado = 1", [$email]);
    }

    /**
     * Obtener persona con estado
     */
    public function getWithState($id)
    {
        $sql = "SELECT p.*, ep.estadopersona_descripcion
                FROM persona p
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                WHERE p.id_persona = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener persona con sus contactos (email y teléfono)
     */
    public function getWithContacts($id)
    {
        $sql = "SELECT p.*, 
                       ep.estadopersona_descripcion,
                       (SELECT contacto_descripcion FROM contacto c 
                        JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' 
                        AND c.rela_persona = p.id_persona 
                        AND c.contacto_estado = 1 
                        LIMIT 1) AS contacto_email,
                       (SELECT contacto_descripcion FROM contacto c 
                        JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'telefono' 
                        AND c.rela_persona = p.id_persona 
                        AND c.contacto_estado = 1 
                        LIMIT 1) AS contacto_telefono
                FROM persona p
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                WHERE p.id_persona = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $where = "persona_email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $where .= " AND id_persona != ?";
            $params[] = $excludeId;
        }
        
        $persona = $this->findWhere($where, $params);
        return $persona !== false;
    }
}