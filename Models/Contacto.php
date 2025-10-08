<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Contacto
 */
class Contacto extends Model
{
    protected $table = 'contacto';
    protected $primaryKey = 'id_contacto';

    /**
     * Obtener contactos activos
     */
    public function getActive()
    {
        return $this->findAll("contacto_estado = 1", "contacto_descripcion");
    }

    /**
     * Obtener contactos por persona (sin JOIN, usando modelo TipoContacto)
     */
    public function getByPersona($personaId)
    {
        $tipoContactoModel = new TipoContacto();
        
        // 1. Obtener todos los contactos de la persona
        $sql = "SELECT * FROM {$this->table} 
                WHERE rela_persona = ? AND contacto_estado = 1 
                ORDER BY contacto_descripcion";
        
        $result = $this->query($sql, [$personaId]);
        
        $contactos = [];
        while ($row = $result->fetch_assoc()) {
            // 2. Para cada contacto, obtener la descripción del tipo
            $tipoContacto = $tipoContactoModel->find($row['rela_tipocontacto']);
            $row['tipocontacto_descripcion'] = $tipoContacto ? $tipoContacto['tipocontacto_descripcion'] : 'Desconocido';
            
            $contactos[] = $row;
        }
        
        return $contactos;
    }

    /**
     * Buscar contacto por tipo y persona
     */
    public function findByTipoAndPersona($tipoContactoId, $personaId)
    {
        return $this->findWhere("rela_tipocontacto = ? AND rela_persona = ? AND contacto_estado = 1", 
                               [$tipoContactoId, $personaId]);
    }

    /**
     * Buscar contacto por descripción de tipo y persona
     */
    public function findByTipoDescripcionAndPersona($tipoDescripcion, $personaId)
    {
        $tipoContactoModel = new TipoContacto();
        
        // 1. Obtener el ID del tipo de contacto
        $tipoContacto = $tipoContactoModel->findWhere("tipocontacto_descripcion = ?", [$tipoDescripcion]);
        
        if (!$tipoContacto) {
            return false; // Tipo no encontrado
        }
        
        // 2. Buscar el contacto usando solo la tabla contacto
        return $this->findWhere(
            "rela_tipocontacto = ? AND rela_persona = ? AND contacto_estado = 1", 
            [$tipoContacto['id_tipocontacto'], $personaId]
        );
    }

    /**
     * Verificar si un contacto existe por descripción y tipo
     */
    public function existsByDescripcionAndTipo($descripcion, $tipoDescripcion, $excludePersonaId = null)
    {
        $tipoContactoModel = new TipoContacto();
        
        // 1. Obtener el ID del tipo de contacto
        $tipoContacto = $tipoContactoModel->findWhere("tipocontacto_descripcion = ?", [$tipoDescripcion]);
        
        if (!$tipoContacto) {
            return false; // Tipo no encontrado, por tanto no existe el contacto
        }
        
        // 2. Buscar en la tabla contacto
        $where = "rela_tipocontacto = ? AND contacto_descripcion = ? AND contacto_estado = 1";
        $params = [$tipoContacto['id_tipocontacto'], $descripcion];
        
        if ($excludePersonaId) {
            $where .= " AND rela_persona != ?";
            $params[] = $excludePersonaId;
        }
        
        $contacto = $this->findWhere($where, $params);
        return $contacto !== false && $contacto !== null;
    }

    /**
     * Obtener contacto específico por tipo y persona
     */
    public function getContactoByTipoAndPersona($tipoDescripcion, $personaId)
    {
        $contacto = $this->findByTipoDescripcionAndPersona($tipoDescripcion, $personaId);
        return $contacto ? $contacto['contacto_descripcion'] : null;
    }

    /**
     * Buscar contacto por descripción y tipo
     */
    public function findByDescripcionAndTipo($descripcion, $tipoDescripcion)
    {
        $tipoContactoModel = new TipoContacto();
        
        // 1. Obtener el ID del tipo de contacto
        $tipoContacto = $tipoContactoModel->findWhere("tipocontacto_descripcion = ?", [$tipoDescripcion]);
        
        if (!$tipoContacto) {
            return false; // Tipo no encontrado
        }
        
        // 2. Buscar el contacto usando solo la tabla contacto
        return $this->findWhere(
            "rela_tipocontacto = ? AND contacto_descripcion = ? AND contacto_estado = 1", 
            [$tipoContacto['id_tipocontacto'], $descripcion]
        );
    }

    /**
     * Crear contacto con validaciones
     */
    public function createContacto($data)
    {
        // Validar datos requeridos
        if (empty($data['contacto_descripcion']) || 
            empty($data['rela_persona']) || 
            empty($data['rela_tipocontacto'])) {
            return false;
        }

        // Establecer valores por defecto
        $data['contacto_estado'] = $data['contacto_estado'] ?? 1;

        return $this->create($data);
    }

    /**
     * Actualizar contacto
     */
    public function updateContacto($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Eliminar contacto (baja lógica)
     */
    public function deleteContacto($id)
    {
        return $this->update($id, ['contacto_estado' => 0]);
    }

    /**
     * Restaurar contacto
     */
    public function restoreContacto($id)
    {
        return $this->update($id, ['contacto_estado' => 1]);
    }
}