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
        $offset = ($page - 1) * $perPage;
        $baseWhere = "rela_estadopersona = 1";
        $params = [];
        
        if ($search) {
            $searchPattern = '%' . $search . '%';
            $baseWhere .= " AND (persona_nombre LIKE ? OR persona_apellido LIKE ?)";
            $params = [$searchPattern, $searchPattern];
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $baseWhere";
        if (!empty($params)) {
            $countResult = $this->query($countSql, $params);
        } else {
            $countResult = $this->db->query($countSql);
        }
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $sql = "SELECT * FROM {$this->table} WHERE $baseWhere ORDER BY persona_apellido, persona_nombre LIMIT ? OFFSET ?";
        $allParams = array_merge($params, [$perPage, $offset]);
        
        $result = $this->query($sql, $allParams);
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
     * Buscar persona por email (lógica simple: buscar contacto y obtener rela_persona)
     */
    public function findByEmail($email)
    {
        $contactoModel = new Contacto();
        
        // 1. Buscar el contacto con ese email
        $contacto = $contactoModel->findByDescripcionAndTipo($email, 'email');
        
        if (!$contacto) {
            return false; // No existe contacto con ese email
        }
        
        // 2. Obtener la persona usando el rela_persona del contacto
        return $this->find($contacto['rela_persona']);
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
     * Obtener persona con sus contactos (email y teléfono) usando modelo Contacto
     */
    public function getWithContacts($id)
    {
        $sql = "SELECT p.*, ep.estadopersona_descripcion
                FROM persona p
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                WHERE p.id_persona = ?";
        
        $result = $this->query($sql, [$id]);
        $persona = $result->fetch_assoc();
        
        if ($persona) {
            // Usar el modelo Contacto para obtener los contactos específicos
            $contactoModel = new Contacto();
            $persona['contacto_email'] = $contactoModel->getContactoByTipoAndPersona('email', $id);
            $persona['contacto_telefono'] = $contactoModel->getContactoByTipoAndPersona('telefono', $id);
        }
        
        return $persona;
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $contactoModel = new Contacto();
        return $contactoModel->existsByDescripcionAndTipo($email, 'email', $excludeId);
    }

    /**
     * Crear persona con transacción completa
     */
    public function createPersonaCompleta($data)
    {
        $db = \App\Core\Database::getInstance();
        
        return $db->transaction(function($db) use ($data) {
            // 1. Crear persona usando el método create del modelo
            $personaData = [
                'persona_nombre' => $data['persona_nombres'],
                'persona_apellido' => $data['persona_apellidos'],
                'persona_fechanac' => $data['persona_fecha_nacimiento'],
                'persona_direccion' => $data['persona_direccion'],
                'rela_estadopersona' => 1
            ];
            
            $personaId = $this->create($personaData);
            if (!$personaId) {
                throw new \Exception('Error al crear la persona');
            }
            
            // 2. Crear contactos usando el modelo Contacto
            $this->createContactosForPersona($personaId, $data);
            
            // 3. Crear huésped si es necesario
            if (isset($data['crear_huesped']) && $data['crear_huesped']) {
                $this->createHuespedForPersona($personaId);
            }
            
            return $personaId;
        });
    }

    /**
     * Crear contactos para una persona
     */
    private function createContactosForPersona($personaId, $data)
    {
        $contactoModel = new Contacto();
        $tipoContactoModel = new TipoContacto();
        
        $contactos = [
            'email' => $data['persona_email'] ?? '',
            'telefono' => $data['persona_telefono'] ?? '',
            'instagram' => $data['persona_instagram'] ?? '',
            'facebook' => $data['persona_facebook'] ?? ''
        ];

        foreach ($contactos as $tipoDescripcion => $valor) {
            if (!empty($valor)) {
                // Obtener ID del tipo de contacto usando findWhere
                $tipoContacto = $tipoContactoModel->findWhere("tipocontacto_descripcion = ?", [$tipoDescripcion]);
                
                if (!$tipoContacto) {
                    throw new \Exception("Tipo de contacto '$tipoDescripcion' no encontrado");
                }
                
                // Crear contacto usando el método create del modelo
                $contactoData = [
                    'contacto_descripcion' => $valor,
                    'rela_persona' => $personaId,
                    'rela_tipocontacto' => $tipoContacto['id_tipocontacto'],
                    'contacto_estado' => 1
                ];
                
                $contactoId = $contactoModel->create($contactoData);
                if (!$contactoId) {
                    throw new \Exception("Error al crear contacto $tipoDescripcion");
                }
            }
        }
    }

    /**
     * Crear huésped para una persona
     */
    private function createHuespedForPersona($personaId)
    {
        $huespedModel = new Huesped();
        
        // Verificar si ya es huésped
        if ($huespedModel->personaIsHuesped($personaId)) {
            return; // Ya es huésped, no hacer nada
        }
        
        // Crear huésped usando el método create del modelo
        $huespedData = [
            'rela_persona' => $personaId,
            'huesped_estado' => 1
        ];
        
        $huespedId = $huespedModel->create($huespedData);
        if (!$huespedId) {
            throw new \Exception('Error al crear huésped');
        }
        
        return $huespedId;
    }

    /**
     * Método público para crear huésped (usado desde Usuario model)
     */
    public function insertHuespedTransaction($db, $personaId)
    {
        return $this->createHuespedForPersona($personaId);
    }
    
    /**
     * Buscar persona por nombre de usuario
     */
    public function findByUsuario($nombreUsuario)
    {
        $sql = "SELECT p.* 
                FROM persona p
                LEFT JOIN usuario u ON p.id_persona = u.rela_persona
                WHERE u.usuario_nombre = ? AND u.usuario_estado = 1";
        
        $result = $this->query($sql, [$nombreUsuario]);
        return $result->fetch_assoc();
    }
}